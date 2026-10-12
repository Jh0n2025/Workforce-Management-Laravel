<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AccountProfileController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = trim((string) ($user?->employee_id ?? ''));

        if (!$user || $employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee account could not be identified.',
            ], 401);
        }

        if ($request->isMethod('get')) {
            try {
                $record = DB::table('employees as e')
                    ->leftJoin('accounts as a', 'a.employee_id', '=', 'e.employee_id')
                    ->where('e.employee_id', $employeeId)
                    ->where('a.id', $user->id)
                    ->first([
                        'e.employee_id', 'e.first_name', 'e.middle_name',
                        'e.last_name', 'e.avatar_url', 'e.email',
                        'e.position_name', 'e.department_name',
                        'a.email as account_email', 'a.role',
                    ]);

                if (!$record) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Profile record not found.',
                    ], 404);
                }

                $fullName = trim(implode(' ', array_filter([
                    trim((string) ($record->first_name ?? '')),
                    trim((string) ($record->middle_name ?? '')),
                    trim((string) ($record->last_name ?? '')),
                ], fn ($part) => $part !== '')));

                return response()->json([
                    'success' => true,
                    'data' => [
                        'employee_id' => $record->employee_id,
                        'first_name' => $record->first_name,
                        'middle_name' => $record->middle_name,
                        'last_name' => $record->last_name,
                        'avatar_url' => $record->avatar_url,
                        'full_name' => $fullName,
                        'email' => $record->account_email ?: $record->email,
                        'position' => $record->position_name,
                        'department' => $record->department_name,
                        'role' => $record->role ?? $user->role,
                    ],
                ]);
            } catch (Throwable $e) {
                Log::error('Profile GET failed: '.$e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to load profile.',
                ], 500);
            }
        }

        if (!$request->isMethod('post')) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed.',
            ], 405);
        }

        $fullName = trim((string) $request->input('name', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $position = trim((string) $request->input('position', ''));
        $department = trim((string) $request->input('department', ''));

        if ($fullName === '') {
            return response()->json(['success' => false, 'message' => 'Full name is required.'], 400);
        }

        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($nameParts) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least a first name and last name.',
            ], 400);
        }

        $firstName = array_shift($nameParts);
        $lastName = array_pop($nameParts);
        $middleName = $nameParts ? implode(' ', $nameParts) : null;

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Please enter a valid email address.'], 400);
        }

        try {
            $account = DB::table('accounts')
                ->where('id', $user->id)
                ->where('employee_id', $employeeId)
                ->first(['id', 'employee_id', 'email', 'role']);

            if (!$account) {
                return response()->json(['success' => false, 'message' => 'Account record not found.'], 404);
            }

            if ($email !== '' && DB::table('accounts')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->where('id', '<>', $user->id)
                ->exists()) {
                return response()->json(['success' => false, 'message' => 'That email address is already being used.'], 409);
            }

            DB::transaction(function () use ($employeeId, $firstName, $middleName, $lastName, $email, $position, $department, $user, $account) {
                DB::table('employees')->where('employee_id', $employeeId)->update([
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => $email !== '' ? $email : null,
                    'position_name' => $position !== '' ? $position : null,
                    'department_name' => $department !== '' ? $department : null,
                    'updated_at' => now(),
                ]);

                if ($email !== '') {
                    DB::table('accounts')->where('id', $user->id)
                        ->where('employee_id', $employeeId)
                        ->update(['email' => $email, 'updated_at' => now()]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'user' => [
                    'id' => (int) $account->id,
                    'employee_id' => $employeeId,
                    'name' => $fullName,
                    'email' => $email !== '' ? $email : (string) $account->email,
                    'role' => $account->role,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Profile update failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to update profile.'], 500);
        }
    }

    public function photo(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = trim((string) ($user?->employee_id ?? ''));

        if (!$user || $employeeId === '') {
            return response()->json(['success' => false, 'message' => 'Employee account could not be identified.'], 401);
        }

        if (!in_array(strtolower((string) $user->role), ['manager', 'employee'], true)) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $current = DB::table('employees')->where('employee_id', $employeeId)->value('avatar_url');
            if ($current === null && !DB::table('employees')->where('employee_id', $employeeId)->exists()) {
                return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }

            if ($request->input('action') === 'remove') {
                $prefix = 'uploads/profile_photos/';
                if (is_string($current) && str_starts_with($current, $prefix)) {
                    $oldPath = public_path($current);
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                DB::table('employees')->where('employee_id', $employeeId)->update([
                    'avatar_url' => null,
                    'updated_at' => now(),
                ]);
                return response()->json(['success' => true, 'message' => 'Profile photo removed.', 'avatar_url' => null]);
            }

            $file = $request->file('photo');
            if (!$file || !$file->isValid()) {
                return response()->json(['success' => false, 'message' => 'Please select a photo.'], 400);
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'Profile photo must be 5 MB or smaller.'], 413);
            }
            $mime = $file->getMimeType();
            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($extensions[$mime]) || @getimagesize($file->getRealPath()) === false) {
                return response()->json(['success' => false, 'message' => 'Only valid JPG, PNG, and WEBP photos are allowed.'], 400);
            }

            $directory = public_path('uploads/profile_photos');
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                return response()->json(['success' => false, 'message' => 'Unable to create profile photo directory.'], 500);
            }

            $safeId = preg_replace('/[^A-Za-z0-9_-]/', '', $employeeId);
            $filename = 'manager_'.$safeId.'_'.Str::random(24).'.'.$extensions[$mime];
            $file->move($directory, $filename);
            $avatarUrl = 'uploads/profile_photos/'.$filename;

            DB::table('employees')->where('employee_id', $employeeId)->update([
                'avatar_url' => $avatarUrl,
                'updated_at' => now(),
            ]);

            $prefix = 'uploads/profile_photos/';
            if (is_string($current) && str_starts_with($current, $prefix)) {
                $oldPath = public_path($current);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully.',
                'avatar_url' => $avatarUrl,
            ]);
        } catch (Throwable $e) {
            Log::error('Profile photo API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to update profile photo.'], 500);
        }
    }
}
