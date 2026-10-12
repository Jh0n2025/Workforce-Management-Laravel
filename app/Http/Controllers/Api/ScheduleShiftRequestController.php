<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScheduleShiftRequestController extends Controller
{
    public function mySchedule(Request $request): JsonResponse
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
            $employee = DB::table('employees')->where('employee_id', $employeeId)
                ->first(['employee_id', 'first_name', 'middle_name', 'last_name', 'position_name']);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }

            $monday = Carbon::today()->startOfWeek(Carbon::MONDAY);
            $sunday = $monday->copy()->addDays(6);
            $rows = DB::table('schedules')->where('employee_id', $employeeId)
                ->whereBetween('schedule_date', [$monday->toDateString(), $sunday->toDateString()])
                ->where('status', '<>', 'cancelled')
                ->orderBy('schedule_date')->orderByDesc('id')
                ->get(['id', 'schedule_date', 'shift_name', 'start_time', 'end_time', 'status']);

            $byDate = [];
            foreach ($rows as $row) {
                $date = Carbon::parse($row->schedule_date)->toDateString();
                if (!isset($byDate[$date])) $byDate[$date] = $row;
            }

            $days = [];
            for ($date = $monday->copy(); $date->lte($sunday); $date->addDay()) {
                $isoDate = $date->toDateString();
                $dayNum = $date->dayOfWeekIso;
                $schedule = $byDate[$isoDate] ?? null;
                $shiftName = $schedule && trim((string) $schedule->shift_name) !== ''
                    ? (string) $schedule->shift_name : 'Day Off';
                $startTime = $schedule && $schedule->start_time ? substr((string) $schedule->start_time, 0, 5) : null;
                $endTime = $schedule && $schedule->end_time ? substr((string) $schedule->end_time, 0, 5) : null;
                $days[] = [
                    'date' => $isoDate,
                    'day' => $date->format('l'),
                    'shortDay' => $date->format('D'),
                    'dayNumber' => $dayNum,
                    'dateNumber' => (int) $date->format('j'),
                    'shiftName' => $shiftName,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'position' => (string) ($employee->position_name ?? ''),
                    'isOff' => !$schedule || in_array(strtoupper($shiftName), ['OFF', 'DAY OFF'], true),
                ];
            }

            return response()->json([
                'success' => true,
                'employee' => [
                    'employeeId' => (string) $employee->employee_id,
                    'name' => trim(implode(' ', array_filter([
                        trim((string) ($employee->first_name ?? '')),
                        trim((string) ($employee->middle_name ?? '')),
                        trim((string) ($employee->last_name ?? '')),
                    ], fn ($v) => $v !== ''))),
                    'position' => (string) ($employee->position_name ?? ''),
                ],
                'weekStart' => $monday->toDateString(),
                'weekEnd' => $sunday->toDateString(),
                'weekLabel' => $monday->format('F j') . '–' . $sunday->format('j, Y'),
                'days' => $days,
            ]);
        } catch (Throwable $e) {
            Log::error('Employee schedule API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load employee schedule.'], 500);
        }
    }

    public function managerSchedule(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Manager access required.'], 403);
        }
        $managerEmployeeId = trim((string) ($user->employee_id ?? ''));
        if ($managerEmployeeId === '') {
            return response()->json(['success' => false, 'message' => 'Manager session not found.'], 401);
        }

        $manager = DB::table('employees')->where('employee_id', $managerEmployeeId)->first(['id']);
        if (!$manager) {
            return response()->json(['success' => false, 'message' => 'Manager employee record not found.'], 404);
        }

        if ($request->isMethod('get')) {
            $weekStart = trim((string) $request->query('week_start', ''));
            try {
                $start = $weekStart === '' ? Carbon::today()->startOfWeek(Carbon::MONDAY) : Carbon::createFromFormat('!Y-m-d', $weekStart);
                if (!$start || ($weekStart !== '' && $start->toDateString() !== $weekStart)) throw new \InvalidArgumentException();
            } catch (Throwable $e) {
                return response()->json(['success' => false, 'message' => 'Invalid week start date.'], 422);
            }
            $start->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->addDays(4);
            try {
                $rows = DB::table('employees as e')
                    ->leftJoin('schedules as s', function ($join) use ($start, $end) {
                        $join->on('s.employee_id', '=', 'e.employee_id')
                            ->whereBetween('s.schedule_date', [$start->toDateString(), $end->toDateString()])
                            ->whereRaw("s.id = (SELECT MAX(s2.id) FROM schedules s2 WHERE s2.employee_id = e.employee_id AND s2.schedule_date = s.schedule_date)");
                    })
                    ->where('e.manager_id', $manager->id)
                    ->where('e.employment_status', '<>', 'terminated')
                    ->orderBy('e.employee_id')->orderBy('s.schedule_date')
                    ->get([
                        'e.employee_id', 'e.first_name', 'e.middle_name', 'e.last_name', 'e.position_name',
                        's.schedule_date', 's.shift_name',
                    ]);
                $employees = [];
                foreach ($rows as $row) {
                    $eid = (string) $row->employee_id;
                    if (!isset($employees[$eid])) {
                        $name = trim(implode(' ', array_filter([
                            trim((string) ($row->first_name ?? '')),
                            trim((string) ($row->middle_name ?? '')),
                            trim((string) ($row->last_name ?? '')),
                        ], fn ($v) => $v !== '')));
                        $employees[$eid] = [
                            'employeeId' => $eid, 'name' => strtoupper($name),
                            'position' => (string) ($row->position_name ?? ''),
                            'mon' => 'OFF', 'tue' => 'OFF', 'wed' => 'OFF', 'thu' => 'OFF', 'fri' => 'OFF',
                        ];
                    }
                    if (!empty($row->schedule_date)) {
                        $dayKey = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri'][Carbon::parse($row->schedule_date)->dayOfWeekIso] ?? null;
                        if ($dayKey) $employees[$eid][$dayKey] = (string) ($row->shift_name ?? 'OFF');
                    }
                }
                return response()->json([
                    'success' => true, 'week_start' => $start->toDateString(),
                    'week_end' => $end->toDateString(),
                    'week_label' => $start->format('M j').'-'.$end->format('j, Y'),
                    'data' => array_values($employees),
                ]);
            } catch (Throwable $e) {
                Log::error('Manager schedule load failed: '.$e->getMessage());
                return response()->json(['success' => false, 'message' => 'Unable to load team schedule.'], 500);
            }
        }

        $input = json_decode($request->getContent(), true);
        if (!is_array($input)) return response()->json(['success' => false, 'message' => 'Invalid request body.'], 400);
        $employeeId = trim((string) ($input['employee_id'] ?? ''));
        $weekStart = trim((string) ($input['week_start'] ?? ''));
        $day = strtolower(trim((string) ($input['day'] ?? '')));
        $shift = trim((string) ($input['shift'] ?? ''));
        if ($employeeId === '' || $weekStart === '') return response()->json(['success' => false, 'message' => 'Employee and week start are required.'], 422);
        try {
            $start = Carbon::createFromFormat('!Y-m-d', $weekStart);
            if (!$start || $start->toDateString() !== $weekStart) throw new \InvalidArgumentException();
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Invalid week start date.'], 422);
        }
        $employeeExists = DB::table('employees')->where('employee_id', $employeeId)->where('manager_id', $manager->id)->where('employment_status', '<>', 'terminated')->exists();
        if (!$employeeExists) return response()->json(['success' => false, 'message' => 'This employee is not assigned to your team.'], 403);
        $start->startOfWeek(Carbon::MONDAY);
        $days = ['mon' => 0, 'tue' => 1, 'wed' => 2, 'thu' => 3, 'fri' => 4];

        if ($day !== '') {
            if (!isset($days[$day])) return response()->json(['success' => false, 'message' => 'Invalid weekday.'], 422);
            if ($shift === '') return response()->json(['success' => false, 'message' => 'Shift is required.'], 422);
            $date = $start->copy()->addDays($days[$day]);
            $parsed = $this->parseShift($shift);
            DB::transaction(function () use ($employeeId, $date, $parsed) {
                DB::table('schedules')->where('employee_id', $employeeId)->whereDate('schedule_date', $date->toDateString())->delete();
                DB::table('schedules')->insert([
                    'employee_id' => $employeeId, 'schedule_date' => $date->toDateString(),
                    'shift_name' => $parsed['shift_name'], 'start_time' => $parsed['start_time'],
                    'end_time' => $parsed['end_time'], 'status' => 'scheduled',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            });
        } else {
            $scheduleValues = $input['schedules'] ?? null;
            if (!is_array($scheduleValues)) return response()->json(['success' => false, 'message' => 'Schedule values are required.'], 422);
            foreach (array_keys($days) as $required) {
                if (!isset($scheduleValues[$required]) || trim((string) $scheduleValues[$required]) === '') {
                    return response()->json(['success' => false, 'message' => 'A shift is required for '.strtoupper($required).'.'], 422);
                }
            }
            DB::transaction(function () use ($employeeId, $start, $days, $scheduleValues) {
                $end = $start->copy()->addDays(4);
                DB::table('schedules')->where('employee_id', $employeeId)->whereBetween('schedule_date', [$start->toDateString(), $end->toDateString()])->delete();
                foreach ($days as $dayKey => $offset) {
                    $date = $start->copy()->addDays($offset);
                    $parsed = $this->parseShift(trim((string) $scheduleValues[$dayKey]));
                    DB::table('schedules')->insert([
                        'employee_id' => $employeeId, 'schedule_date' => $date->toDateString(),
                        'shift_name' => $parsed['shift_name'], 'start_time' => $parsed['start_time'],
                        'end_time' => $parsed['end_time'], 'status' => 'scheduled',
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });
        }
        return response()->json(['success' => true, 'message' => 'Schedule saved successfully.']);
    }

    public function myShiftRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'employee') return response()->json(['success' => false, 'message' => 'Employee access required.'], 403);
        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') return response()->json(['success' => false, 'message' => 'Employee session not found.'], 401);
        if ($request->isMethod('get')) {
            $rows = DB::table('shift_requests')->where('employee_id', $employeeId)->orderByDesc('created_at')->orderByDesc('id')
                ->get(['id', 'requested_date', 'requested_shift', 'reason', 'status', 'reviewed_at', 'created_at']);
            return response()->json(['success' => true, 'data' => $rows]);
        }
        $input = json_decode($request->getContent(), true);
        if (!is_array($input)) return response()->json(['success' => false, 'message' => 'Invalid request body.'], 400);
        $dateValue = trim((string) ($input['requested_date'] ?? ''));
        $shift = trim((string) ($input['requested_shift'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));
        if ($dateValue === '' || $shift === '') return response()->json(['success' => false, 'message' => 'Requested date and requested shift are required.'], 422);
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $dateValue);
            if (!$date || $date->toDateString() !== $dateValue) throw new \InvalidArgumentException();
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Invalid requested date.'], 422);
        }
        if ($date->dayOfWeekIso >= 6) return response()->json(['success' => false, 'message' => 'Shift requests are only allowed for Monday to Friday.'], 422);
        if (DB::table('shift_requests')->where('employee_id', $employeeId)->whereDate('requested_date', $dateValue)->where('status', 'pending')->exists()) {
            return response()->json(['success' => false, 'message' => 'A pending shift request already exists for this date.'], 409);
        }
        $id = DB::table('shift_requests')->insertGetId([
            'employee_id' => $employeeId, 'requested_date' => $dateValue, 'requested_shift' => $shift,
            'reason' => $reason !== '' ? $reason : null, 'status' => 'pending', 'created_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Shift request submitted successfully.', 'id' => $id]);
    }

    public function managerShiftRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'manager') return response()->json(['success' => false, 'message' => 'Manager access required.'], 403);
        $managerEmployeeId = trim((string) ($user->employee_id ?? ''));
        $managerAccountId = (int) ($user->id ?? 0);
        if ($managerEmployeeId === '' || $managerAccountId <= 0) return response()->json(['success' => false, 'message' => 'Manager session is invalid.'], 401);
        $manager = DB::table('employees')->where('employee_id', $managerEmployeeId)->first(['id']);
        if (!$manager) return response()->json(['success' => false, 'message' => 'Manager employee record not found.'], 404);

        if ($request->isMethod('get')) {
            try {
                $rows = DB::table('shift_requests as sr')
                    ->join('employees as e', 'e.employee_id', '=', 'sr.employee_id')
                    ->where('e.manager_id', $manager->id)
                    ->orderByRaw("CASE WHEN sr.status = 'pending' THEN 0 ELSE 1 END")
                    ->orderByDesc('sr.created_at')->orderByDesc('sr.id')
                    ->select([
                        'sr.id', 'sr.employee_id', 'sr.requested_date', 'sr.requested_shift', 'sr.reason',
                        'sr.status', 'sr.created_at', 'e.position_name',
                    ])
                    ->selectRaw("TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) AS employee_name")
                    ->selectRaw("(SELECT s.shift_name FROM schedules s WHERE s.employee_id = sr.employee_id AND s.schedule_date = sr.requested_date ORDER BY s.id DESC LIMIT 1) AS current_shift")
                    ->get();
                $data = $rows->map(function ($row) {
                    $date = Carbon::parse($row->requested_date);
                    $monday = $date->copy()->startOfWeek(Carbon::MONDAY);
                    $friday = $monday->copy()->addDays(4);
                    $dayLabel = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri'][$date->dayOfWeekIso] ?? '';
                    $status = $row->status === 'rejected' ? 'Declined' : ucfirst((string) $row->status);
                    return [
                        'id' => (int) $row->id, 'employeeId' => (string) $row->employee_id,
                        'name' => strtoupper(trim((string) $row->employee_name)),
                        'position' => (string) ($row->position_name ?? ''), 'date' => $date->toDateString(),
                        'week' => $monday->format('M j').'-'.$friday->format('j, Y'), 'day' => $dayLabel,
                        'currentShift' => (string) ($row->current_shift ?? ''),
                        'requestedShift' => (string) $row->requested_shift, 'reason' => (string) ($row->reason ?? ''),
                        'status' => $status, 'createdAt' => (string) $row->created_at,
                    ];
                })->values();
                return response()->json(['success' => true, 'data' => $data]);
            } catch (Throwable $e) {
                Log::error('Manager shift requests load failed: '.$e->getMessage());
                return response()->json(['success' => false, 'message' => 'Unable to process shift request.'], 500);
            }
        }

        $input = json_decode($request->getContent(), true);
        if (!is_array($input)) return response()->json(['success' => false, 'message' => 'Invalid request body.'], 400);
        $id = (int) ($input['id'] ?? 0);
        $action = strtolower(trim((string) ($input['action'] ?? '')));
        if ($id <= 0) return response()->json(['success' => false, 'message' => 'Invalid shift request ID.'], 422);
        if (!in_array($action, ['approve', 'decline'], true)) return response()->json(['success' => false, 'message' => 'Invalid shift request action.'], 422);

        try {
            $requestRow = DB::table('shift_requests as sr')->join('employees as e', 'e.employee_id', '=', 'sr.employee_id')
                ->where('sr.id', $id)->where('e.manager_id', $manager->id)
                ->first(['sr.id', 'sr.employee_id', 'sr.requested_date', 'sr.requested_shift', 'sr.status']);
            if (!$requestRow) return response()->json(['success' => false, 'message' => 'Shift request was not found for your team.'], 404);
            if ($requestRow->status !== 'pending') return response()->json(['success' => false, 'message' => 'This shift request has already been processed.'], 409);

            DB::transaction(function () use ($requestRow, $action, $managerAccountId) {
                if ($action === 'approve') {
                    $parsed = $this->parseShift((string) $requestRow->requested_shift);
                    DB::table('schedules')->where('employee_id', $requestRow->employee_id)->whereDate('schedule_date', $requestRow->requested_date)->delete();
                    DB::table('schedules')->insert([
                        'employee_id' => $requestRow->employee_id, 'schedule_date' => $requestRow->requested_date,
                        'shift_name' => $parsed['shift_name'], 'start_time' => $parsed['start_time'],
                        'end_time' => $parsed['end_time'], 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                DB::table('shift_requests')->where('id', $requestRow->id)->where('status', 'pending')->update([
                    'status' => $action === 'approve' ? 'approved' : 'rejected',
                    'reviewed_by' => $managerAccountId, 'reviewed_at' => now(),
                ]);
            });
            return response()->json(['success' => true, 'message' => $action === 'approve' ? 'Shift request approved.' : 'Shift request declined.']);
        } catch (Throwable $e) {
            Log::error('Manager shift request API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to process shift request.'], 500);
        }
    }

    private function parseShift(string $shift): array
    {
        $shift = trim($shift);
        if ($shift === '' || in_array(strtoupper($shift), ['OFF', 'LEAVE', 'ABSENT', 'DAY OFF'], true)) {
            return ['shift_name' => $shift !== '' ? strtoupper($shift) : 'OFF', 'start_time' => null, 'end_time' => null];
        }
        if (preg_match('/^(\d{1,2})(?::(\d{2}))?\s*-\s*(\d{1,2})(?::(\d{2}))?$/', $shift, $m)) {
            $sh = (int) $m[1]; $sm = ($m[2] ?? '') !== '' ? (int) $m[2] : 0;
            $eh = (int) $m[3]; $em = ($m[4] ?? '') !== '' ? (int) $m[4] : 0;
            if ($sh >= 1 && $sh <= 7) $sh += 12;
            if ($eh >= 1 && $eh <= 7) $eh += 12;
            if ($sh > 23 || $eh > 23 || $sm > 59 || $em > 59) {
                return ['shift_name' => $shift, 'start_time' => null, 'end_time' => null];
            }
            return ['shift_name' => $shift, 'start_time' => sprintf('%02d:%02d:00', $sh, $sm), 'end_time' => sprintf('%02d:%02d:00', $eh, $em)];
        }
        return ['shift_name' => $shift, 'start_time' => null, 'end_time' => null];
    }
}
