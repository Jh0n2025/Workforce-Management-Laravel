<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $employeeId = trim(
            (string) $request->query('employee_id', '')
        );

        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID is required.',
            ], 400);
        }

        try {
            /*
             * Employee information
             */
            $employee = DB::table('employees as e')
                ->leftJoin(
                    'accounts as a',
                    'a.employee_id',
                    '=',
                    'e.employee_id'
                )
                ->where('e.employee_id', $employeeId)
                ->first([
                    'e.employee_id',
                    'e.first_name',
                    'e.middle_name',
                    'e.last_name',
                    'e.avatar_url',
                    'e.date_of_birth',
                    'e.gender',
                    'e.address',
                    'e.phone',
                    'e.email',
                    'e.department_name',
                    'e.position_name',
                    'e.hire_date',
                    'e.employment_type',
                    'e.employment_status',
                    'e.emergency_name',
                    'e.emergency_relationship',
                    'e.emergency_phone',
                    'a.updated_at as password_updated_at',
                ]);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.',
                ], 404);
            }

            $fullName = trim(implode(' ', array_filter([
                $employee->first_name,
                $employee->middle_name,
                $employee->last_name,
            ])));

            $passwordUpdatedAt = $employee->password_updated_at
                ? Carbon::parse($employee->password_updated_at)->format('m/d/y')
                : null;

            /*
             * Attendance summary for the current month
             */
            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();

            $attendance = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->whereBetween('attendance_date', [
                    $monthStart,
                    $monthEnd,
                ])
                ->selectRaw("
                    COALESCE(
                        SUM(
                            CASE
                                WHEN status IN ('present', 'late')
                                THEN 1 ELSE 0
                            END
                        ), 0
                    ) AS days_present,
                    COALESCE(
                        SUM(
                            CASE
                                WHEN status = 'late'
                                THEN 1 ELSE 0
                            END
                        ), 0
                    ) AS late_arrivals
                ")
                ->first();

            /*
             * Upcoming schedules
             */
            $schedule = DB::table('schedules')
                ->where('employee_id', $employeeId)
                ->whereDate('schedule_date', '>=', now()->toDateString())
                ->where('status', '<>', 'cancelled')
                ->orderBy('schedule_date')
                ->orderBy('id')
                ->limit(10)
                ->get([
                    'schedule_date',
                    'shift_name',
                    'start_time',
                    'end_time',
                    'status',
                ]);

            /*
             * Calculate used leave in weekdays, as in the original system
             */
            $leaveRows = DB::table('leave_requests')
                ->where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->orderByDesc('start_date')
                ->get([
                    'leave_type',
                    'start_date',
                    'end_date',
                ]);

            $vacationUsed = 0;
            $sickUsed = 0;
            $emergencyUsed = 0;

            foreach ($leaveRows as $leave) {
                $start = Carbon::parse($leave->start_date)->startOfDay();
                $end = Carbon::parse($leave->end_date)->startOfDay();
                $days = 0;

                for (
                    $date = $start->copy();
                    $date->lte($end);
                    $date->addDay()
                ) {
                    if ($date->isWeekday()) {
                        $days++;
                    }
                }

                switch ($leave->leave_type) {
                    case 'Vacation Leave':
                        $vacationUsed += $days;
                        break;

                    case 'Sick Leave':
                        $sickUsed += $days;
                        break;

                    case 'Emergency Leave':
                        $emergencyUsed += $days;
                        break;
                }
            }

            /*
             * Annual leave entitlement
             */
            $leaveBalance = DB::table('employee_leave_balances')
                ->where('employee_id', $employeeId)
                ->where('balance_year', now()->year)
                ->first([
                    'vacation_days',
                    'sick_days',
                    'emergency_days',
                ]);

            $vacationEntitled = (float) ($leaveBalance->vacation_days ?? 0);
            $sickEntitled = (float) ($leaveBalance->sick_days ?? 0);
            $emergencyEntitled = (float) ($leaveBalance->emergency_days ?? 0);

            /*
             * Recent attendance/timesheet history
             */
            $timesheetRows = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->orderByDesc('attendance_date')
                ->orderByDesc('id')
                ->limit(10)
                ->get([
                    'attendance_date',
                    'time_in',
                    'time_out',
                    'status',
                ]);

            $timesheetHistory = $timesheetRows->map(function ($row) {
                $workedHours = 0.0;

                if ($row->time_in && $row->time_out) {
                    $timeIn = Carbon::parse($row->time_in)->getTimestamp();
                    $timeOut = Carbon::parse($row->time_out)->getTimestamp();

                    if ($timeOut > $timeIn) {
                        $workedHours = round(
                            ($timeOut - $timeIn) / 3600,
                            2
                        );
                    }
                }

                return [
                    'date' => (string) $row->attendance_date,
                    'time_in' => $row->time_in,
                    'time_out' => $row->time_out,
                    'worked_hours' => $workedHours,
                    'status' => $row->status,
                ];
            })->values();

            /*
             * Preserve the original response structure
             */
            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => [
                        'employee_id' => $employee->employee_id,
                        'first_name' => $employee->first_name,
                        'middle_name' => $employee->middle_name,
                        'last_name' => $employee->last_name,
                        'avatar_url' => $employee->avatar_url,
                        'full_name' => $fullName,
                        'date_of_birth' => $employee->date_of_birth,
                        'gender' => $employee->gender,
                        'address' => $employee->address,
                        'phone' => $employee->phone,
                        'email' => $employee->email,
                        'department' => $employee->department_name,
                        'position' => $employee->position_name,
                        'hire_date' => $employee->hire_date,
                        'employment_type' => $employee->employment_type,
                        'employment_status' => $employee->employment_status,
                        'emergency_name' => $employee->emergency_name,
                        'emergency_relationship' => $employee->emergency_relationship,
                        'emergency_phone' => $employee->emergency_phone,
                        'password_updated_at' => $passwordUpdatedAt,
                    ],

                    'attendance_summary' => [
                        'days_present' => (int) $attendance->days_present,
                        'late_arrivals' => (int) $attendance->late_arrivals,
                        'period_start' => $monthStart,
                        'period_end' => $monthEnd,
                    ],

                    'schedule' => $schedule,

                    'leave_balance' => [
                        'vacation_entitled' => $vacationEntitled,
                        'vacation_used' => $vacationUsed,
                        'vacation_remaining' => max(
                            0,
                            $vacationEntitled - $vacationUsed
                        ),

                        'sick_entitled' => $sickEntitled,
                        'sick_used' => $sickUsed,
                        'sick_remaining' => max(
                            0,
                            $sickEntitled - $sickUsed
                        ),

                        'emergency_entitled' => $emergencyEntitled,
                        'emergency_used' => $emergencyUsed,
                        'emergency_remaining' => max(
                            0,
                            $emergencyEntitled - $emergencyUsed
                        ),
                    ],

                    'timesheet_history' => $timesheetHistory,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Employee profile API failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to load employee profile.',
            ], 500);
        }
    }

    /**
     * Update an employee profile from the existing Admin edit form.
     * This operation requires the short-lived Admin RFID authorization created
     * by RfidAuthController::verifyAdminRfid.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $employeeId = trim((string) $request->input('employee_id', ''));
        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID is required.',
            ], 422);
        }

        $authorization = $request->session()->get('employee_edit_rfid_authorization');
        if (
            !is_array($authorization)
            || (string) ($authorization['employee_id'] ?? '') !== $employeeId
            || (int) ($authorization['expires_at'] ?? 0) < time()
            || (int) ($authorization['admin_account_id'] ?? 0) < 1
        ) {
            $request->session()->forget('employee_edit_rfid_authorization');
            return response()->json([
                'success' => false,
                'message' => 'Admin RFID verification is required or has expired. Please verify again.',
            ], 403);
        }

        $verifiedAdmin = DB::table('accounts')
            ->where('id', (int) $authorization['admin_account_id'])
            ->where('role', 'admin')
            ->where('status', 'active')
            ->exists();

        if (!$verifiedAdmin) {
            $request->session()->forget('employee_edit_rfid_authorization');
            return response()->json([
                'success' => false,
                'message' => 'The verified Admin RFID is no longer authorized. Please verify again.',
            ], 403);
        }

        $input = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d'],
            'gender' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'department_name' => ['nullable', 'string', 'max:150'],
            'position_name' => ['nullable', 'string', 'max:150'],
            'hire_date' => ['nullable', 'date_format:Y-m-d'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['required', 'in:active,inactive,on_leave,terminated'],
            'emergency_name' => ['nullable', 'string', 'max:150'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_phone' => ['nullable', 'string', 'max:50'],
            'new_password' => ['nullable', 'string', 'min:6'],
            'retype_new_password' => ['nullable', 'string'],
        ]);

        $newPassword = (string) ($input['new_password'] ?? '');
        $retypedPassword = (string) ($input['retype_new_password'] ?? '');
        if ($newPassword !== '' || $retypedPassword !== '') {
            if ($newPassword === '' || $retypedPassword === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter and re-type the new password.',
                ], 422);
            }
            if (!hash_equals($newPassword, $retypedPassword)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New passwords do not match.',
                ], 422);
            }
            if (
                !preg_match('/[A-Za-z]/', $newPassword)
                || !preg_match('/[0-9]/', $newPassword)
                || !preg_match('/[!@%]/', $newPassword)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password must include a letter, a number, and a special character (!@%).',
                ], 422);
            }
        }

        $email = strtolower(trim((string) $input['email']));
        $account = DB::table('accounts')
            ->where('employee_id', $employeeId)
            ->orderBy('id')
            ->first(['id', 'email']);

        if (DB::table('accounts')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->when($account, fn ($query) => $query->where('id', '<>', (int) $account->id))
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'That email address is already being used.',
            ], 409);
        }

        if ($newPassword !== '' && !$account) {
            return response()->json([
                'success' => false,
                'message' => 'This employee does not have a login account to update.',
            ], 409);
        }

        $employeeExists = DB::table('employees')->where('employee_id', $employeeId)->exists();
        if (!$employeeExists) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
            ], 404);
        }

        try {
            DB::transaction(function () use ($employeeId, $input, $email, $account, $newPassword): void {
                DB::table('employees')->where('employee_id', $employeeId)->update([
                    'first_name' => trim((string) $input['first_name']),
                    'middle_name' => trim((string) ($input['middle_name'] ?? '')) !== '' ? trim((string) $input['middle_name']) : null,
                    'last_name' => trim((string) $input['last_name']),
                    'date_of_birth' => $input['date_of_birth'] ?? null,
                    'gender' => trim((string) ($input['gender'] ?? '')) !== '' ? trim((string) $input['gender']) : null,
                    'address' => trim((string) ($input['address'] ?? '')) !== '' ? trim((string) $input['address']) : null,
                    'phone' => trim((string) ($input['phone'] ?? '')) !== '' ? trim((string) $input['phone']) : null,
                    'email' => $email,
                    'department_name' => trim((string) ($input['department_name'] ?? '')) !== '' ? trim((string) $input['department_name']) : null,
                    'position_name' => trim((string) ($input['position_name'] ?? '')) !== '' ? trim((string) $input['position_name']) : null,
                    'hire_date' => $input['hire_date'] ?? null,
                    'employment_type' => trim((string) ($input['employment_type'] ?? '')) !== '' ? trim((string) $input['employment_type']) : null,
                    'employment_status' => $input['employment_status'],
                    'emergency_name' => trim((string) ($input['emergency_name'] ?? '')) !== '' ? trim((string) $input['emergency_name']) : null,
                    'emergency_relationship' => trim((string) ($input['emergency_relationship'] ?? '')) !== '' ? trim((string) $input['emergency_relationship']) : null,
                    'emergency_phone' => trim((string) ($input['emergency_phone'] ?? '')) !== '' ? trim((string) $input['emergency_phone']) : null,
                    'updated_at' => now(),
                ]);

                if ($account) {
                    $accountChanges = [
                        'email' => $email,
                        'updated_at' => now(),
                    ];
                    if ($newPassword !== '') {
                        $accountChanges['password_hash'] = Hash::make($newPassword);
                    }
                    DB::table('accounts')->where('id', (int) $account->id)->update($accountChanges);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Employee information saved successfully.',
                'password_updated_at' => $newPassword !== '' ? now()->format('m/d/y') : null,
            ]);
        } catch (Throwable $e) {
            Log::error('Employee profile update API failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to save employee information. Please check the entered information and try again.',
            ], 500);
        }
    }
}
