<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\OtpChallenge;
use App\Models\TrustedDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please enter your email and password.',
            ], 400);
        }

        $account = Account::query()
            ->with('employee')
            ->whereRaw('LOWER(email) = LOWER(?)', [$email])
            ->first();

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }
        /** @var Account $account */

        if ($account->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active.',
            ], 403);
        }

        if (!Hash::check($password, $account->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $name = $this->accountName($account);

        /*
         * Preserve the existing 24-hour trusted-device behavior.
         */
        $trustedToken = $request->cookie('wfm_trusted_device');

        if (is_string($trustedToken) && $trustedToken !== '') {
            $trustedDevice = TrustedDevice::query()
                ->where('account_id', $account->id)
                ->where(
                    'device_token_hash',
                    hash('sha256', $trustedToken)
                )
                ->where('expires_at', '>', now())
                ->orderByDesc('expires_at')
                ->first();

            if ($trustedDevice) {
                $trustedDevice->forceFill([
                    'last_used_at' => now(),
                ])->save();

                Auth::guard('web')->login($account);

                $request->session()->regenerate();
                $request->session()->forget('pending_2fa');

                return $this->authenticatedResponse($account, $name);
            }
        }

        /*
         * This device is not trusted.
         * Generate a six-digit email verification code.
         */
        $otpCode = (string) random_int(100000, 999999);
        $challenge = null;

        try {
            $challenge = DB::transaction(function () use (
                $account,
                $otpCode
            ) {
                OtpChallenge::query()
                    ->where('account_id', $account->id)
                    ->whereNull('consumed_at')
                    ->update([
                        'consumed_at' => now(),
                    ]);

                return OtpChallenge::query()->create([
                    'account_id' => $account->id,
                    'code_hash' => Hash::make($otpCode),
                    'expires_at' => now()->addMinutes(10),
                    'attempts' => 0,
                    'consumed_at' => null,
                    'created_at' => now(),
                ]);
            });

            $request->session()->put('pending_2fa', [
                'account_id' => (int) $account->id,
                'employee_id' => $account->employee_id,
                'email' => $account->email,
                'name' => $name,
                'role' => $account->role,
                'otp_sent_at' => now()->toDateTimeString(),
            ]);

            /*
             * Keep the existing Workforce Management email theme.
             */
            $safeCode = htmlspecialchars(
                $otpCode,
                ENT_QUOTES,
                'UTF-8'
            );

            $html = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background:#F8FAFC; padding:30px; color:#163B6D;">
    <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border:1px solid #E5E7EB; border-radius:16px; padding:32px;">
        <h2 style="margin:0 0 10px;">Workforce Management</h2>
        <p style="color:#6B7280;">Use this six-digit code to complete your sign-in.</p>

        <div style="margin:28px 0; text-align:center;">
            <span style="display:inline-block; padding:16px 24px; border-radius:12px; background:#F8FAFC; border:1px solid #E5E7EB; font-size:32px; font-weight:700; letter-spacing:10px; color:#163B6D;">
                {$safeCode}
            </span>
        </div>

        <p style="color:#6B7280;">
            This code expires in 10 minutes.
        </p>

        <p style="font-size:13px; color:#9CA3AF;">
            If you did not try to sign in, you can ignore this email.
        </p>
    </div>
</body>
</html>
HTML;

            Mail::html($html, function ($message) use ($account) {
                $message
                    ->to($account->email)
                    ->subject(
                        'Your Workforce Management Verification Code'
                    );
            });

            return response()->json([
                'success' => false,
                'requires_2fa' => true,
                'message' => 'A six-digit verification code was sent to your email.',
                'masked_email' => $this->maskEmail($account->email),
            ]);
        } catch (Throwable $e) {
            if ($challenge !== null) {
                $challenge->forceFill([
                    'consumed_at' => now(),
                ])->save();
            }

            $request->session()->forget('pending_2fa');

            Log::error('WFM login OTP error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'requires_2fa' => false,
                'message' => 'We could not send the verification code. Please try again.',
            ], 500);
        }
    }

    public function verifyOtp(Request $request): JsonResponse
{
    $pending = $request->session()->get('pending_2fa');
    $code = trim((string) $request->input('code', ''));

    if (!is_array($pending) || !isset($pending['account_id'])) {
        return response()->json([
            'success' => false,
            'message' => 'Your verification session has expired. Please sign in again.',
        ], 401);
    }

    if (!preg_match('/^\d{6}$/', $code)) {
        return response()->json([
            'success' => false,
            'message' => 'Enter the 6-digit verification code.',
        ], 400);
    }

    try {
        $result = DB::transaction(function () use ($pending, $code): array {
            $challenge = OtpChallenge::query()
                ->where('account_id', (int) $pending['account_id'])
                ->whereNull('consumed_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$challenge) {
                return [
                    'error' => true,
                    'status' => 400,
                    'message' => 'No active verification code was found. Please request a new code.',
                ];
            }

            if ($challenge->expires_at->isPast()) {
                $challenge->consumed_at = now();
                $challenge->save();

                return [
                    'error' => true,
                    'status' => 400,
                    'message' => 'This verification code has expired. Please request a new code.',
                ];
            }

            if ((int) $challenge->attempts >= 5) {
                return [
                    'error' => true,
                    'status' => 429,
                    'message' => 'Too many incorrect attempts. Please request a new code.',
                ];
            }

            if (!Hash::check($code, $challenge->code_hash)) {
                $challenge->attempts = (int) $challenge->attempts + 1;
                $challenge->save();

                $remaining = max(0, 5 - (int) $challenge->attempts);

                return [
                    'error' => true,
                    'status' => 401,
                    'message' => $remaining > 0
                        ? "Incorrect verification code. {$remaining} attempt(s) remaining."
                        : 'Too many incorrect attempts. Please request a new code.',
                ];
            }

            $account = Account::query()
                ->with('employee')
                ->whereKey((int) $pending['account_id'])
                ->lockForUpdate()
                ->first();

            if (!$account || $account->status !== 'active') {
                return [
                    'error' => true,
                    'status' => 403,
                    'message' => 'This account is no longer active.',
                ];
            }

            $challenge->consumed_at = now();
            $challenge->save();

            $deviceToken = rtrim(
                strtr(base64_encode(random_bytes(32)), '+/', '-_'),
                '='
            );

            TrustedDevice::query()->create([
                'account_id' => $account->id,
                'device_token_hash' => hash('sha256', $deviceToken),
                'verified_at' => now(),
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'last_used_at' => now(),
            ]);

            return [
                'error' => false,
                'account' => $account,
                'device_token' => $deviceToken,
            ];
        });

        if ($result['error']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['status']);
        }

        /** @var Account $account */
        $account = $result['account'];

        Auth::guard('web')->login($account);

        $request->session()->regenerate();
        $request->session()->forget('pending_2fa');
        $request->session()->put('two_factor_verified', true);
        $request->session()->put('authenticated_at', now()->toDateTimeString());

        $pages = [
            'admin' => 'admin.html',
            'manager' => 'manager.html',
            'employee' => 'employee.html',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Verification successful.',
            'account_id' => (int) $account->id,
            'employee_id' => $account->employee_id,
            'name' => $this->accountName($account),
            'email' => $account->email,
            'role' => $account->role,
            'redirect' => $pages[$account->role] ?? 'login.html',
        ])->cookie(
            'wfm_trusted_device',
            $result['device_token'],
            1440,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        );

    } catch (Throwable $e) {
        Log::error('WFM OTP verification error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Unable to verify the code. Please try again.',
        ], 500);
    }
}

public function resendOtp(Request $request): JsonResponse
{
    $pending = $request->session()->get('pending_2fa');

    if (!is_array($pending) || !isset($pending['account_id'])) {
        return response()->json([
            'success' => false,
            'message' => 'Your verification session has expired. Please sign in again.',
        ], 401);
    }

    $account = Account::query()->find((int) $pending['account_id']);

    if (!$account || $account->status !== 'active') {
        $request->session()->forget('pending_2fa');

        return response()->json([
            'success' => false,
            'message' => 'This account is no longer active.',
        ], 403);
    }

    $otpCode = (string) random_int(100000, 999999);

    try {
        DB::transaction(function () use ($account, $otpCode): void {
            // Invalidate previous active codes.
            OtpChallenge::query()
                ->where('account_id', $account->id)
                ->whereNull('consumed_at')
                ->update([
                    'consumed_at' => now(),
                ]);

            // Store a hash of the new six-digit code.
            OtpChallenge::query()->create([
                'account_id' => $account->id,
                'code_hash' => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
                'consumed_at' => null,
                'created_at' => now(),
            ]);

            // Keep the existing Workforce Management email design.
            $safeCode = htmlspecialchars(
                $otpCode,
                ENT_QUOTES,
                'UTF-8'
            );

            $html = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background:#F8FAFC; padding:30px; color:#163B6D;">
    <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border:1px solid #E5E7EB; border-radius:16px; padding:32px;">
        <h2 style="margin:0 0 10px;">Workforce Management</h2>
        <p style="color:#6B7280;">Use this six-digit code to complete your sign-in.</p>

        <div style="margin:28px 0; text-align:center;">
            <span style="display:inline-block; padding:16px 24px; border-radius:12px; background:#F8FAFC; border:1px solid #E5E7EB; font-size:32px; font-weight:700; letter-spacing:10px; color:#163B6D;">
                {$safeCode}
            </span>
        </div>

        <p style="color:#6B7280;">This code expires in 10 minutes.</p>

        <p style="font-size:13px; color:#9CA3AF;">
            If you did not try to sign in, you can ignore this email.
        </p>
    </div>
</body>
</html>
HTML;

            Mail::html($html, function ($message) use ($account): void {
                $message
                    ->to($account->email)
                    ->subject(
                        'Your Workforce Management Verification Code'
                    );
            });
        });

        $pending['otp_sent_at'] = now()->toDateTimeString();

        $request->session()->put('pending_2fa', $pending);

        return response()->json([
            'success' => true,
            'message' => 'A new verification code was sent.',
        ]);
    } catch (Throwable $e) {
        Log::error('WFM OTP resend error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'We could not send a new verification code.',
        ], 500);
    }
}

    private function accountName(Account $account): string
    {
        $employee = $account->employee;

        $name = trim(implode(' ', array_filter([
            $employee?->first_name ?? '',
            $employee?->middle_name ?? '',
            $employee?->last_name ?? '',
        ])));

        if ($name !== '') {
            return $name;
        }

        return match ($account->role) {
            'admin' => 'Admin Account',
            'manager' => 'Manager Account',
            'employee' => 'Employee Account',
            default => $account->email,
        };
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);

        if (count($parts) !== 2) {
            return $email;
        }

        [$local, $domain] = $parts;

        if (strlen($local) <= 2) {
            $maskedLocal = str_repeat('*', strlen($local));
        } else {
            $maskedLocal = substr($local, 0, 1)
                . str_repeat('*', max(1, strlen($local) - 2))
                . substr($local, -1);
        }

        return $maskedLocal . '@' . $domain;
    }

    private function authenticatedResponse(
        Account $account,
        string $name
    ): JsonResponse {
        $pages = [
            'admin' => 'admin.html',
            'manager' => 'manager.html',
            'employee' => 'employee.html',
        ];

        return response()->json([
            'success' => true,
            'requires_2fa' => false,
            'message' => 'Login successful.',
            'account_id' => (int) $account->id,
            'employee_id' => $account->employee_id,
            'name' => $name,
            'email' => $account->email,
            'role' => $account->role,
            'redirect' => $pages[$account->role] ?? 'login.html',
        ]);
    }
}