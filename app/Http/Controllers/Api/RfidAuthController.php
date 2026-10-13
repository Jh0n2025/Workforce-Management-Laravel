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

class RfidAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $rfidUid = trim((string) $request->input('rfid_uid', ''));

        if ($rfidUid === '') {
            return response()->json([
                'success' => false,
                'message' => 'RFID UID is required.',
            ], 400);
        }

        try {
            $record = DB::table('rfid_cards as r')
                ->join('employees as e', 'e.employee_id', '=', 'r.employee_id')
                ->join('accounts as a', 'a.employee_id', '=', 'e.employee_id')
                ->where('r.rfid_uid', $rfidUid)
                ->where('r.status', 'active')
                ->select([
                    'a.id as account_id',
                    'a.role',
                    'a.status as account_status',
                    'e.first_name',
                    'e.middle_name',
                    'e.last_name',
                ])
                ->first();

            if (!$record || $record->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'RFID is not registered for Admin login.',
                ], 403);
            }

            if ($record->account_status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is not active.',
                ], 403);
            }

            $account = Account::query()->with('employee')->find((int) $record->account_id);
            if (!$account || $account->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is not active.',
                ], 403);
            }

            $name = trim(implode(' ', array_filter([
                trim((string) ($record->first_name ?? '')),
                trim((string) ($record->middle_name ?? '')),
                trim((string) ($record->last_name ?? '')),
            ], static fn ($part) => $part !== '')));
            if ($name === '') {
                $name = 'Admin Account';
            }

            $trustedToken = $request->cookie('wfm_trusted_device');
            if (is_string($trustedToken) && $trustedToken !== '') {
                $trusted = TrustedDevice::query()
                    ->where('account_id', $account->id)
                    ->where('device_token_hash', hash('sha256', $trustedToken))
                    ->where('expires_at', '>', now())
                    ->orderByDesc('expires_at')
                    ->first();

                if ($trusted) {
                    $trusted->forceFill(['last_used_at' => now()])->save();
                    Auth::guard('web')->login($account);
                    $request->session()->regenerate();
                    $request->session()->forget('pending_2fa');
                    $request->session()->put('two_factor_verified', true);
                    $request->session()->put('authenticated_at', now()->toDateTimeString());

                    return response()->json([
                        'success' => true,
                        'requires_2fa' => false,
                        'message' => 'Admin RFID login successful.',
                        'account_id' => (int) $account->id,
                        'employee_id' => $account->employee_id,
                        'name' => $name,
                        'email' => $account->email,
                        'role' => 'admin',
                        'redirect' => 'admin.html',
                    ]);
                }
            }

            $otpCode = (string) random_int(100000, 999999);

            DB::transaction(function () use ($account, $otpCode, $request): void {
                OtpChallenge::query()
                    ->where('account_id', $account->id)
                    ->whereNull('consumed_at')
                    ->update(['consumed_at' => now()]);

                OtpChallenge::query()->create([
                    'account_id' => $account->id,
                    'code_hash' => Hash::make($otpCode),
                    'expires_at' => now()->addMinutes(10),
                    'attempts' => 0,
                    'consumed_at' => null,
                    'created_at' => now(),
                ]);

                $request->session()->put('pending_2fa', [
                    'account_id' => (int) $account->id,
                    'employee_id' => $account->employee_id,
                    'email' => $account->email,
                    'name' => $thisName = trim(implode(' ', array_filter([
                        trim((string) ($account->employee?->first_name ?? '')),
                        trim((string) ($account->employee?->middle_name ?? '')),
                        trim((string) ($account->employee?->last_name ?? '')),
                    ], static fn ($part) => $part !== ''))) ?: 'Admin Account',
                    'role' => 'admin',
                    'otp_sent_at' => now()->toDateTimeString(),
                    'login_method' => 'rfid',
                ]);
            });

            $safeCode = htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8');
            $html = <<<HTML
<!doctype html><html><head><meta charset="UTF-8"><title>Verification Code</title></head>
<body style="font-family:Arial,sans-serif;background:#F8FAFC;padding:30px;color:#163B6D;">
<div style="max-width:520px;margin:0 auto;background:#fff;border:1px solid #E5E7EB;border-radius:16px;padding:32px;">
<h2>Workforce Management</h2><p>Use this six-digit code to complete your Admin RFID sign-in.</p>
<div style="margin:28px 0;text-align:center;"><span style="display:inline-block;padding:16px 24px;border-radius:12px;background:#F8FAFC;border:1px solid #E5E7EB;font-size:32px;font-weight:700;letter-spacing:10px;color:#163B6D;">{$safeCode}</span></div>
<p>This code expires in 10 minutes.</p></div></body></html>
HTML;

            Mail::html($html, function ($message) use ($account): void {
                $message->to($account->email)->subject('Your Workforce Management Verification Code');
            });

            return response()->json([
                'success' => false,
                'requires_2fa' => true,
                'message' => 'A six-digit verification code was sent to your email.',
                'masked_email' => $this->maskEmail($account->email),
            ]);
        } catch (Throwable $e) {
            $request->session()->forget('pending_2fa');
            Log::error('WFM Admin RFID login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'requires_2fa' => false,
                'message' => 'We could not complete RFID sign-in. Please try again.',
            ], 500);
        }
    }

    public function verifyAdminRfid(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $employeeId = trim((string) $request->input('employee_id', ''));
        $rfidUid = trim((string) $request->input('rfid_uid', ''));

        if ($employeeId === '' || $rfidUid === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID and Admin RFID are required.',
            ], 400);
        }

        try {
            $admin = DB::table('rfid_cards as r')
                ->join('employees as e', 'e.employee_id', '=', 'r.employee_id')
                ->join('accounts as a', 'a.employee_id', '=', 'e.employee_id')
                ->where('r.rfid_uid', $rfidUid)
                ->where('r.status', 'active')
                ->where('a.role', 'admin')
                ->where('a.status', 'active')
                ->select(['a.id as account_id'])
                ->first();

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'The scanned RFID is not registered to an active Admin account.',
                ], 403);
            }

            $request->session()->put('employee_edit_rfid_authorization', [
                'admin_account_id' => (int) $admin->account_id,
                'employee_id' => $employeeId,
                'verified_at' => time(),
                'expires_at' => time() + 300,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin RFID verified successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Admin RFID employee edit verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify Admin RFID.',
            ], 500);
        }
    }

    /**
     * Verify the currently authenticated Admin's login password as a fallback
     * when their Admin RFID card is unavailable during employee editing.
     */
    public function verifyAdminPasswordForEdit(Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || $admin->role !== 'admin' || $admin->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Active administrator access is required.',
            ], 403);
        }

        $employeeId = trim((string) $request->input('employee_id', ''));
        $password = (string) $request->input('password', '');

        if ($employeeId === '' || $password === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID and Admin password are required.',
            ], 422);
        }

        if (!Hash::check($password, (string) $admin->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'The Admin password is incorrect.',
            ], 403);
        }

        $request->session()->put('employee_edit_rfid_authorization', [
            'admin_account_id' => (int) $admin->id,
            'employee_id' => $employeeId,
            'verified_at' => time(),
            'expires_at' => time() + 300,
            'verification_method' => 'password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin password verified successfully.',
        ]);
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return $email;
        }
        [$local, $domain] = $parts;
        $maskedLocal = strlen($local) <= 2
            ? str_repeat('*', strlen($local))
            : substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 2)) . substr($local, -1);
        return $maskedLocal . '@' . $domain;
    }
}
