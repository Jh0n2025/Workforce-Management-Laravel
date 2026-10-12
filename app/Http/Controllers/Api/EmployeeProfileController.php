<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}