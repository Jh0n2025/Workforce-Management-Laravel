<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeTimesheetGrowthController extends Controller
{
    public function myTimesheet(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'employee') {
            return response()->json(['success' => false, 'message' => 'Employee access required.'], 403);
        }

        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json(['success' => false, 'message' => 'Employee session not found.'], 401);
        }

        try {
            $today = Carbon::today();
            $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->addDays(6);
            $startDate = $weekStart->toDateString();
            $endDate = $weekEnd->toDateString();

            $employee = DB::table('employees')
                ->where('employee_id', $employeeId)
                ->first(['employee_id', 'first_name', 'middle_name', 'last_name', 'position_name']);

            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }

            $rows = DB::table('schedules as s')
                ->leftJoin('attendance_records as a', function ($join) {
                    $join->on('a.employee_id', '=', 's.employee_id')
                        ->on('a.attendance_date', '=', 's.schedule_date')
                        ->whereRaw('a.id = (SELECT MAX(a2.id) FROM attendance_records a2 WHERE a2.employee_id = s.employee_id AND a2.attendance_date = s.schedule_date)');
                })
                ->where('s.employee_id', $employeeId)
                ->whereBetween('s.schedule_date', [$startDate, $endDate])
                ->where('s.status', '<>', 'cancelled')
                ->orderBy('s.schedule_date')
                ->orderByDesc('s.id')
                ->get([
                    's.schedule_date', 's.shift_name', 's.start_time', 's.end_time',
                    'a.time_in', 'a.time_out', 'a.status as attendance_status', 'a.notes as attendance_notes',
                ]);

            $byDate = [];
            foreach ($rows as $row) {
                $date = (string) $row->schedule_date;
                if (!isset($byDate[$date])) {
                    $byDate[$date] = $row;
                }
            }

            $review = DB::table('timesheet_reviews')
                ->where('employee_id', $employeeId)
                ->where('period_start', $startDate)
                ->where('period_end', $endDate)
                ->orderByDesc('id')
                ->first(['status', 'remarks', 'reviewed_at']);

            $reviewStatus = (string) ($review->status ?? 'pending');
            $reviewLabels = [
                'pending' => 'Pending Approval',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ];
            $weekdayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
            $days = [];

            for ($offset = 0; $offset <= 4; $offset++) {
                $date = $weekStart->copy()->addDays($offset);
                $dateIso = $date->toDateString();
                $row = $byDate[$dateIso] ?? null;
                $timeIn = $this->formatTime($row->time_in ?? null);
                $timeOut = $this->formatTime($row->time_out ?? null);
                $workedHours = 0.0;
                $inMinutes = $this->timeToMinutes($timeIn);
                $outMinutes = $this->timeToMinutes($timeOut);

                if ($inMinutes !== null && $outMinutes !== null && $outMinutes > $inMinutes) {
                    $workedHours = max(0, ($outMinutes - $inMinutes - 60) / 60);
                }

                $regularHours = min(8, $workedHours);
                $overtimeHours = max(0, $workedHours - 8);
                $status = 'Day Off';
                if ($row) {
                    if (!$timeIn && !$timeOut) {
                        $status = 'Absent';
                    } elseif ($timeIn && !$timeOut) {
                        $status = 'In Progress';
                    } elseif (strtolower((string) ($row->attendance_status ?? '')) === 'late') {
                        $status = 'Late';
                    } else {
                        $status = 'Completed';
                    }
                }

                $days[] = [
                    'date' => $dateIso,
                    'day' => $weekdayNames[$offset + 1],
                    'shiftName' => $row->shift_name ?? null,
                    'timeIn' => $timeIn,
                    'timeOut' => $timeOut,
                    'regularHours' => round($regularHours, 2),
                    'overtimeHours' => round($overtimeHours, 2),
                    'totalHours' => round($workedHours, 2),
                    'status' => $status,
                ];
            }

            $regularTotal = round(array_sum(array_column($days, 'regularHours')), 2);
            $overtimeTotal = round(array_sum(array_column($days, 'overtimeHours')), 2);

            return response()->json([
                'success' => true,
                'employee' => [
                    'employeeId' => (string) $employee->employee_id,
                    'name' => $this->employeeName($employee),
                    'position' => (string) ($employee->position_name ?? ''),
                ],
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'label' => $weekStart->format('F j') . ' – ' . $weekEnd->format('j, Y'),
                ],
                'review' => [
                    'status' => $reviewStatus,
                    'label' => $reviewLabels[$reviewStatus] ?? 'Pending Approval',
                    'remarks' => (string) ($review->remarks ?? ''),
                    'reviewedAt' => $review->reviewed_at ?? null,
                ],
                'days' => $days,
                'summary' => [
                    'regularHours' => $regularTotal,
                    'overtimeHours' => $overtimeTotal,
                    'totalHours' => round($regularTotal + $overtimeTotal, 2),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Employee timesheet API failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load employee timesheet.'], 500);
        }
    }

    public function managerTimesheets(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Manager access required.'], 403);
        }

        $managerEmployeeId = trim((string) ($user->employee_id ?? ''));
        if ($managerEmployeeId === '') {
            return response()->json(['success' => false, 'message' => 'Manager session not found.'], 401);
        }

        try {
            $manager = DB::table('employees')->where('employee_id', $managerEmployeeId)->first(['id']);
            if (!$manager) {
                return response()->json(['success' => false, 'message' => 'Manager employee record not found.'], 404);
            }

            $today = Carbon::today();
            $currentMonday = $today->copy()->startOfWeek(Carbon::MONDAY);
            $lastMonday = $currentMonday->copy()->subWeek();
            $startIso = $lastMonday->toDateString();
            $todayIso = $today->toDateString();

            $rows = DB::table('employees as e')
                ->join('schedules as s', function ($join) use ($startIso, $todayIso) {
                    $join->on('s.employee_id', '=', 'e.employee_id')
                        ->whereBetween('s.schedule_date', [$startIso, $todayIso])
                        ->whereRaw('s.id = (SELECT MAX(s2.id) FROM schedules s2 WHERE s2.employee_id = s.employee_id AND s2.schedule_date = s.schedule_date)');
                })
                ->leftJoin('attendance_records as a', function ($join) {
                    $join->on('a.employee_id', '=', 'e.employee_id')
                        ->on('a.attendance_date', '=', 's.schedule_date')
                        ->whereRaw('a.id = (SELECT MAX(a2.id) FROM attendance_records a2 WHERE a2.employee_id = e.employee_id AND a2.attendance_date = s.schedule_date)');
                })
                ->where('e.manager_id', $manager->id)
                ->where('e.employment_status', '<>', 'terminated')
                ->where('s.status', '<>', 'cancelled')
                ->orderByDesc('s.schedule_date')
                ->orderBy('e.employee_id')
                ->get([
                    'e.employee_id', 'e.first_name', 'e.middle_name', 'e.last_name', 'e.position_name',
                    's.schedule_date', 's.start_time', 's.end_time', 'a.time_in', 'a.time_out', 'a.notes as attendance_notes',
                ]);

            $records = [];
            foreach ($rows as $row) {
                $date = (string) $row->schedule_date;
                $dateObject = Carbon::parse($date);
                if ($dateObject->dayOfWeekIso >= 6 && $date !== $todayIso) {
                    continue;
                }

                $timeIn = $this->formatTime($row->time_in ?? null);
                $timeOut = $this->formatTime($row->time_out ?? null);
                $remarks = trim((string) ($row->attendance_notes ?? ''));
                if ($remarks === '') {
                    if ($timeIn && $timeOut) {
                        $remarks = 'Attendance record loaded from the database.';
                    } elseif ($timeIn && !$timeOut) {
                        $remarks = $date === $todayIso ? 'Currently on shift, no Time Out yet.' : 'Time Out was not recorded.';
                    } else {
                        $remarks = 'No attendance record for this date.';
                    }
                }

                $records[] = [
                    'id' => (string) $row->employee_id . '_' . $date,
                    'employeeId' => (string) $row->employee_id,
                    'employeeName' => strtoupper($this->employeeName($row)),
                    'position' => (string) ($row->position_name ?? ''),
                    'date' => $date,
                    'scheduleStart' => $row->start_time ? substr((string) $row->start_time, 0, 5) : null,
                    'scheduleEnd' => $row->end_time ? substr((string) $row->end_time, 0, 5) : null,
                    'timeIn' => $timeIn,
                    'timeOut' => $timeOut,
                    'breakMin' => 60,
                    'remarks' => $remarks,
                ];
            }

            return response()->json(['success' => true, 'data' => $records]);
        } catch (Throwable $e) {
            Log::error('Manager timesheet API failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load manager timesheets.'], 500);
        }
    }

    public function myGrowth(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'employee') {
            return response()->json(['success' => false, 'message' => 'Employee access required.'], 403);
        }

        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json(['success' => false, 'message' => 'Employee session not found.'], 401);
        }

        try {
            $employee = DB::table('employees')
                ->where('employee_id', $employeeId)
                ->first(['employee_id', 'first_name', 'middle_name', 'last_name', 'position_name', 'department_name', 'hire_date']);

            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }

            $yearStart = Carbon::today()->startOfYear()->toDateString();
            $yearEnd = Carbon::today()->endOfYear()->toDateString();
            $attendanceRows = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->whereBetween('attendance_date', [$yearStart, $yearEnd])
                ->orderBy('attendance_date')->orderBy('id')
                ->get(['attendance_date', 'status', 'time_in', 'time_out']);

            $today = Carbon::today();
            $currentWeekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
            $attendanceTrend = [];
            for ($week = 3; $week >= 0; $week--) {
                $weekStart = $currentWeekStart->copy()->subWeeks($week);
                $weekEnd = $weekStart->copy()->addDays(4);
                $weekTotal = 0;
                $weekAttended = 0;
                foreach ($attendanceRows as $row) {
                    if (!$row->attendance_date) continue;
                    $attendanceDate = Carbon::parse($row->attendance_date);
                    if ($attendanceDate->lt($weekStart) || $attendanceDate->gt($weekEnd)) continue;
                    $weekTotal++;
                    if (in_array(strtolower(trim((string) $row->status)), ['present', 'late'], true)) $weekAttended++;
                }
                $attendanceTrend[] = [
                    'label' => 'W' . (4 - $week),
                    'rate' => $weekTotal > 0 ? round(($weekAttended / $weekTotal) * 100, 1) : 0,
                ];
            }

            $totalRecords = $presentRecords = $lateRecords = $absentRecords = 0;
            $totalWorkHours = $totalOvertimeHours = 0.0;
            foreach ($attendanceRows as $row) {
                $status = strtolower(trim((string) ($row->status ?? '')));
                $totalRecords++;
                if ($status === 'present') $presentRecords++;
                if ($status === 'late') $lateRecords++;
                if ($status === 'absent') $absentRecords++;
                if (empty($row->time_in) || empty($row->time_out)) continue;
                $timeIn = strtotime((string) $row->time_in);
                $timeOut = strtotime((string) $row->time_out);
                if ($timeIn === false || $timeOut === false || $timeOut <= $timeIn) continue;
                $workedHours = max(0, (($timeOut - $timeIn) / 3600) - 1);
                $totalWorkHours += min(8, $workedHours);
                $totalOvertimeHours += max(0, $workedHours - 8);
            }

            $leaveRows = DB::table('leave_requests')
                ->where('employee_id', $employeeId)->where('status', 'approved')
                ->where('start_date', '<=', $yearEnd)->where('end_date', '>=', $yearStart)
                ->orderBy('start_date')->get(['leave_type', 'start_date', 'end_date']);
            $leaveByType = [];
            $totalLeaveDays = 0;
            foreach ($leaveRows as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                if ($start->lt(Carbon::parse($yearStart))) $start = Carbon::parse($yearStart);
                if ($end->gt(Carbon::parse($yearEnd))) $end = Carbon::parse($yearEnd);
                $days = 0;
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    if ($date->dayOfWeekIso <= 5) $days++;
                }
                $type = (string) $leave->leave_type;
                $leaveByType[$type] = ($leaveByType[$type] ?? 0) + $days;
                $totalLeaveDays += $days;
            }

            $timesheetData = DB::table('timesheet_reviews')
                ->where('employee_id', $employeeId)
                ->where('period_start', '>=', $yearStart)
                ->where('period_end', '<=', $yearEnd)
                ->selectRaw("COUNT(*) AS total_reviews, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_reviews")
                ->first();
            $totalReviews = (int) ($timesheetData->total_reviews ?? 0);
            $approvedReviews = (int) ($timesheetData->approved_reviews ?? 0);
            $timesheetCompletionRate = $totalReviews > 0 ? round(($approvedReviews / $totalReviews) * 100, 1) : 0.0;

            $yearsOfService = $employee->hire_date ? Carbon::parse($employee->hire_date)->diffInYears($today) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $this->employeeName($employee),
                    'position' => $employee->position_name ?? '',
                    'department' => $employee->department_name ?? '',
                    'hire_date' => $employee->hire_date,
                    'years_of_service' => $yearsOfService,
                    'attendance_rate' => $totalRecords > 0 ? round((($presentRecords + $lateRecords) / $totalRecords) * 100, 1) : 0.0,
                    'present_records' => $presentRecords,
                    'late_records' => $lateRecords,
                    'absent_records' => $absentRecords,
                    'attendance_records' => $totalRecords,
                    'attendance_trend' => $attendanceTrend,
                    'work_hours' => round($totalWorkHours, 2),
                    'overtime_hours' => round($totalOvertimeHours, 2),
                    'leave_used_days' => $totalLeaveDays,
                    'timesheet_completion_rate' => $timesheetCompletionRate,
                    'leave_by_type' => collect($leaveByType)->map(fn ($used, $type) => ['type' => $type, 'used' => $used])->values(),
                    'period_start' => $yearStart,
                    'period_end' => $yearEnd,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Employee growth API failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load employee growth information.'], 500);
        }
    }

    private function formatTime(?string $value): ?string
    {
        if (!$value) return null;
        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('H:i', $timestamp);
    }

    private function timeToMinutes(?string $value): ?int
    {
        if (!$value) return null;
        $parts = explode(':', $value);
        if (count($parts) < 2) return null;
        return ((int) $parts[0] * 60) + (int) $parts[1];
    }

    private function employeeName(object $employee): string
    {
        return implode(' ', array_filter([
            trim((string) ($employee->first_name ?? '')),
            trim((string) ($employee->middle_name ?? '')),
            trim((string) ($employee->last_name ?? '')),
        ], fn ($part) => $part !== ''));
    }
}
