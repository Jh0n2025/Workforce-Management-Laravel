<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Throwable;

class AnalyticsApiController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        try {
            $today = Carbon::today();
            $periodStart = $today->copy()->subDays(6);

            $startValue = $periodStart->toDateString();
            $todayValue = $today->toDateString();

            // Active employees
            $activeEmployees = DB::table('employees')
                ->where('employment_status', 'active')
                ->count();

            // Attendance during the last seven days
            $attendance = DB::table('attendance_records')
                ->whereBetween('attendance_date', [$startValue, $todayValue])
                ->selectRaw("
                    COUNT(*) AS total_records,
                    SUM(CASE WHEN status IN ('present', 'late')
                        THEN 1 ELSE 0 END) AS attended_records,
                    SUM(CASE WHEN status = 'late'
                        THEN 1 ELSE 0 END) AS late_records,
                    SUM(CASE WHEN status = 'absent'
                        THEN 1 ELSE 0 END) AS absent_records
                ")
                ->first();

            $totalAttendance = (int) ($attendance->total_records ?? 0);
            $attendedRecords = (int) ($attendance->attended_records ?? 0);
            $lateRecords = (int) ($attendance->late_records ?? 0);
            $absentRecords = (int) ($attendance->absent_records ?? 0);

            // Approved leave days, counting weekdays only
            $leaves = DB::table('leave_requests')
                ->where('status', 'approved')
                ->where('start_date', '<=', $todayValue)
                ->where('end_date', '>=', $startValue)
                ->get(['start_date', 'end_date']);

            $approvedLeaveDays = 0;

            foreach ($leaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);

                if ($leaveStart->lt($periodStart)) {
                    $leaveStart = $periodStart->copy();
                }

                if ($leaveEnd->gt($today)) {
                    $leaveEnd = $today->copy();
                }

                for (
                    $date = $leaveStart->copy();
                    $date->lte($leaveEnd);
                    $date->addDay()
                ) {
                    if ($date->dayOfWeekIso <= 5) {
                        $approvedLeaveDays++;
                    }
                }
            }

            // Scheduled workdays
            $scheduledRows = DB::table('schedules as s')
                ->join('employees as e', 'e.employee_id', '=', 's.employee_id')
                ->whereBetween('s.schedule_date', [$startValue, $todayValue])
                ->where('s.status', '<>', 'cancelled')
                ->where('e.employment_status', '<>', 'terminated')
                ->whereRaw(
                    "UPPER(COALESCE(s.shift_name, '')) NOT IN (?, ?, ?)",
                    ['OFF', 'LEAVE', 'ABSENT']
                )
                ->count();

            // Worked hours, subtracting one unpaid hour per completed shift
            $attendanceRows = DB::table('attendance_records')
                ->whereBetween('attendance_date', [$startValue, $todayValue])
                ->whereNotNull('time_in')
                ->whereNotNull('time_out')
                ->get(['time_in', 'time_out']);

            $regularHours = 0.0;
            $overtimeHours = 0.0;

            foreach ($attendanceRows as $row) {
                $timeIn = strtotime((string) $row->time_in);
                $timeOut = strtotime((string) $row->time_out);

                if (!$timeIn || !$timeOut || $timeOut <= $timeIn) {
                    continue;
                }

                $workedMinutes = max(
                    0,
                    ($timeOut - $timeIn) / 60 - 60
                );

                $regularHours += min(480, $workedMinutes) / 60;
                $overtimeHours += max(0, $workedMinutes - 480) / 60;
            }

            // Attendance rate
            $attendanceRate = $totalAttendance > 0
                ? round(($attendedRecords / $totalAttendance) * 100, 1)
                : 0.0;

            // Shift fulfillment
            $shiftFulfillment = $scheduledRows > 0
                ? round(min(100, ($attendedRecords / $scheduledRows) * 100), 1)
                : 0.0;

            // Department summary
            $departmentExpression =
                "COALESCE(NULLIF(department_name, ''), 'Unassigned')";

            $departments = DB::table('employees')
                ->where('employment_status', 'active')
                ->selectRaw(
                    "$departmentExpression AS department_name, COUNT(*) AS employee_count"
                )
                ->groupByRaw($departmentExpression)
                ->orderByDesc('employee_count')
                ->get()
                ->map(fn ($row) => [
                    'department' => (string) $row->department_name,
                    'employees' => (int) $row->employee_count,
                ])
                ->values();

                        return response()->json([
                'success' => true,
                'data' => [
                    'active_employees' => $activeEmployees,
                    'attendance_rate' => $attendanceRate,
                    'late_records' => $lateRecords,
                    'attended_records' => $attendedRecords,
                    'absent_records' => $absentRecords,
                    'approved_leave_days' => $approvedLeaveDays,
                    'regular_hours' => round($regularHours, 2),
                    'overtime_hours' => round($overtimeHours, 2),
                    'shift_fulfillment' => $shiftFulfillment,
                    'departments' => $departments,
                    'period_start' => $startValue,
                    'period_end' => $todayValue,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Admin analytics API failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to load Admin Workforce Analytics.',
            ], 500);
        }
    }
}