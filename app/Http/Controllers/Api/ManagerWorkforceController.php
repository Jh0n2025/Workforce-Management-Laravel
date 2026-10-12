<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ManagerWorkforceController extends Controller
{
    private function managerId(Request $request): array
    {
        if ($request->user()?->role !== 'manager') {
            return [null, response()->json([
                'success' => false,
                'message' => 'Manager access required.',
            ], 403)];
        }

        $employeeId = trim((string) ($request->user()?->employee_id ?? ''));
        if ($employeeId === '') {
            return [null, response()->json([
                'success' => false,
                'message' => 'Manager session not found.',
            ], 401)];
        }

        $manager = DB::table('employees')
            ->where('employee_id', $employeeId)
            ->first(['id']);

        if (!$manager) {
            return [null, response()->json([
                'success' => false,
                'message' => 'Manager employee record not found.',
            ], 404)];
        }

        return [(int) $manager->id, null];
    }

    public function teamToday(Request $request): JsonResponse
    {
        [$managerId, $error] = $this->managerId($request);
        if ($error) return $error;

        try {
            $today = now()->toDateString();
            $rows = DB::table('employees as e')
                ->leftJoin('attendance_records as a', function ($join) use ($today) {
                    $join->on('a.employee_id', '=', 'e.employee_id')
                        ->whereDate('a.attendance_date', '=', $today);
                })
                ->leftJoin('schedules as s', function ($join) use ($today) {
                    $join->on('s.employee_id', '=', 'e.employee_id')
                        ->whereDate('s.schedule_date', '=', $today)
                        ->where('s.status', '<>', 'cancelled');
                })
                ->leftJoin('leave_requests as lr', function ($join) use ($today) {
                    $join->on('lr.employee_id', '=', 'e.employee_id')
                        ->whereDate('lr.start_date', '<=', $today)
                        ->whereDate('lr.end_date', '>=', $today)
                        ->where('lr.status', '=', 'approved');
                })
                ->where('e.manager_id', $managerId)
                ->where('e.employment_status', '<>', 'terminated')
                ->orderBy('e.employee_id')
                ->get([
                    'e.employee_id', 'e.first_name', 'e.middle_name', 'e.last_name',
                    'e.position_name', 'a.time_in', 'a.time_out',
                    'a.status as attendance_status', 's.shift_name',
                    's.start_time', 's.end_time', 'lr.status as leave_status',
                ]);

            $records = $rows->map(function ($row) {
                $name = trim(implode(' ', array_filter([
                    $row->first_name, $row->middle_name, $row->last_name,
                ], fn ($value) => $value !== null && $value !== '')));

                if ($row->leave_status) $status = 'LEAVE';
                elseif ($row->attendance_status) $status = strtoupper((string) $row->attendance_status);
                elseif ($row->time_in) $status = 'PRESENT';
                else $status = 'ABSENT';

                $schedule = '—';
                if ($row->start_time && $row->end_time) {
                    $schedule = Carbon::parse((string) $row->start_time)->format('g:i A')
                        . ' - ' . Carbon::parse((string) $row->end_time)->format('g:i A');
                } elseif ($row->shift_name) {
                    $schedule = (string) $row->shift_name;
                }

                return [
                    'id' => (string) $row->employee_id,
                    'name' => strtoupper($name),
                    'position' => (string) ($row->position_name ?? ''),
                    'schedule' => $schedule,
                    'clockIn' => $row->time_in ? Carbon::parse((string) $row->time_in)->format('g:i A') : '—',
                    'clockOut' => $row->time_out ? Carbon::parse((string) $row->time_out)->format('g:i A') : '—',
                    'status' => $status,
                ];
            })->values();

            return response()->json(['success' => true, 'data' => $records]);
        } catch (Throwable $e) {
            Log::error('Manager team attendance API failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load team attendance.'], 500);
        }
    }

    public function analytics(Request $request): JsonResponse
    {
        [$managerId, $error] = $this->managerId($request);
        if ($error) return $error;

        try {
            $teamCount = DB::table('employees')
                ->where('manager_id', $managerId)
                ->where('employment_status', '<>', 'terminated')
                ->count();

            $today = Carbon::today();
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dateValue = $date->toDateString();

                $teamCountRow = DB::table('employees as e')
                    ->where('e.manager_id', $managerId)
                    ->where('e.employment_status', '<>', 'terminated')
                    ->selectRaw('COUNT(*) AS team_count')
                    ->selectRaw("SUM(CASE WHEN EXISTS (SELECT 1 FROM schedules s WHERE s.employee_id = e.employee_id AND s.schedule_date = ? AND s.status <> 'cancelled' AND s.shift_name IS NOT NULL AND UPPER(s.shift_name) NOT IN ('OFF','LEAVE','ABSENT')) AND NOT EXISTS (SELECT 1 FROM leave_requests lr WHERE lr.employee_id = e.employee_id AND lr.status = 'approved' AND ? BETWEEN lr.start_date AND lr.end_date) THEN 1 ELSE 0 END) AS expected_count", [$dateValue, $dateValue])
                    ->selectRaw("SUM(CASE WHEN EXISTS (SELECT 1 FROM attendance_records a WHERE a.employee_id = e.employee_id AND a.attendance_date = ? AND a.status IN ('present','late')) THEN 1 ELSE 0 END) AS attending_count", [$dateValue])
                    ->selectRaw("SUM(CASE WHEN EXISTS (SELECT 1 FROM attendance_records a2 WHERE a2.employee_id = e.employee_id AND a2.attendance_date = ? AND a2.status = 'late') THEN 1 ELSE 0 END) AS late_count", [$dateValue])
                    ->selectRaw("SUM(CASE WHEN EXISTS (SELECT 1 FROM leave_requests lr2 WHERE lr2.employee_id = e.employee_id AND lr2.status = 'approved' AND ? BETWEEN lr2.start_date AND lr2.end_date) THEN 1 ELSE 0 END) AS leave_count", [$dateValue])
                    ->first();

                $expected = (int) ($teamCountRow->expected_count ?? 0);
                $attending = (int) ($teamCountRow->attending_count ?? 0);
                $days[] = [
                    'date' => $dateValue,
                    'label' => $date->format('D'),
                    'expected' => $expected,
                    'attending' => $attending,
                    'late' => (int) ($teamCountRow->late_count ?? 0),
                    'leave' => (int) ($teamCountRow->leave_count ?? 0),
                    'rate' => $expected > 0 ? (int) round(($attending / $expected) * 100) : 0,
                ];
            }

            return response()->json(['success' => true, 'team_count' => $teamCount, 'days' => $days]);
        } catch (Throwable $e) {
            Log::error('Manager analytics API failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load workforce analytics.'], 500);
        }
    }

    public function summary(Request $request): JsonResponse
    {
        [$managerId, $error] = $this->managerId($request);
        if ($error) return $error;

        try {
            $teamIds = DB::table('employees')
                ->where('manager_id', $managerId)
                ->where('employment_status', '<>', 'terminated')
                ->orderBy('employee_id')
                ->pluck('employee_id')->map(fn ($id) => (string) $id)->all();

            $today = Carbon::today();
            $start = $today->copy()->subDays(6);
            $startValue = $start->toDateString();
            $endValue = $today->toDateString();
            $expectedDays = $attendingDays = $lateArrivals = $approvedLeaveDays = 0;
            $regularHours = $overtimeHours = 0.0;

            if ($teamIds) {
                $schedules = DB::table('schedules')->whereIn('employee_id', $teamIds)
                    ->whereBetween('schedule_date', [$startValue, $endValue])
                    ->where('status', '<>', 'cancelled')->orderBy('id')
                    ->get(['employee_id', 'schedule_date', 'shift_name', 'start_time', 'end_time']);
                $scheduleMap = [];
                foreach ($schedules as $s) $scheduleMap[(string)$s->employee_id.'|'.Carbon::parse($s->schedule_date)->toDateString()] = $s;

                $attendanceRows = DB::table('attendance_records')->whereIn('employee_id', $teamIds)
                    ->whereBetween('attendance_date', [$startValue, $endValue])->orderBy('id')
                    ->get(['employee_id', 'attendance_date', 'time_in', 'time_out', 'status']);
                $attendanceMap = [];
                foreach ($attendanceRows as $a) $attendanceMap[(string)$a->employee_id.'|'.Carbon::parse($a->attendance_date)->toDateString()] = $a;

                $leaves = DB::table('leave_requests')->whereIn('employee_id', $teamIds)->where('status', 'approved')
                    ->where('start_date', '<=', $endValue)->where('end_date', '>=', $startValue)
                    ->get(['employee_id', 'start_date', 'end_date']);
                $leaveMap = [];
                foreach ($leaves as $leave) {
                    $from = Carbon::parse($leave->start_date)->max($start);
                    $to = Carbon::parse($leave->end_date)->min($today);
                    for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                        if ($d->dayOfWeekIso <= 5) $leaveMap[(string)$leave->employee_id.'|'.$d->toDateString()] = true;
                    }
                }

                foreach ($teamIds as $employeeId) {
                    for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
                        if ($date->dayOfWeekIso > 5) continue;
                        $key = $employeeId.'|'.$date->toDateString();
                        $leaveToday = isset($leaveMap[$key]);
                        if ($leaveToday) $approvedLeaveDays++;

                        $schedule = $scheduleMap[$key] ?? null;
                        $attendance = $attendanceMap[$key] ?? null;
                        if ($schedule) {
                            $shift = strtoupper(trim((string)($schedule->shift_name ?? '')));
                            if ($shift !== '' && !in_array($shift, ['OFF','LEAVE','ABSENT'], true) && !$leaveToday) {
                                $expectedDays++;
                                if ($attendance && in_array(strtolower((string)$attendance->status), ['present','late'], true)) $attendingDays++;
                            }
                        }
                        if ($attendance && strtolower((string)$attendance->status) === 'late') $lateArrivals++;
                        if ($attendance && $attendance->time_in && $attendance->time_out) {
                            $timeIn = strtotime((string)$attendance->time_in);
                            $timeOut = strtotime((string)$attendance->time_out);
                            if ($timeIn && $timeOut && $timeOut > $timeIn) {
                                $minutes = max(0, ($timeOut - $timeIn) / 60 - 60);
                                $regularHours += min(480, $minutes) / 60;
                                $overtimeHours += max(0, $minutes - 480) / 60;
                            }
                        }
                    }
                }
            }

            $attendanceRate = $expectedDays > 0 ? round(($attendingDays / $expectedDays) * 100, 1) : 0.0;
            return response()->json(['success' => true, 'data' => [
                'team_members' => count($teamIds),
                'attendance_rate' => $attendanceRate,
                'late_arrivals' => $lateArrivals,
                'approved_leave_days' => $approvedLeaveDays,
                'regular_hours' => round($regularHours, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'period_start' => $startValue,
                'period_end' => $endValue,
            ]]);
        } catch (Throwable $e) {
            Log::error('Manager analytics summary failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load workforce analytics summary.'], 500);
        }
    }
}
