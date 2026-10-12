<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScheduleApiController extends Controller
{
    private const SHIFTS = [
        'Regular' => ['shift_name' => '8-5', 'start_time' => '08:00:00', 'end_time' => '17:00:00'],
        'Morning' => ['shift_name' => '8-12', 'start_time' => '08:00:00', 'end_time' => '12:00:00'],
        'Afternoon' => ['shift_name' => '1-5', 'start_time' => '13:00:00', 'end_time' => '17:00:00'],
    ];

    private const WORK_TYPES = ['Field Duty', 'Office Work', 'Driving', 'Monitoring', 'Meeting'];

    public function admin(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Administrator access required.'], 403);
        }

        try {
            if ($request->isMethod('get')) {
                if ($request->query('action') === 'employees') {
                    return $this->employeeOptions($request);
                }

                $startDate = (string) $request->query('start_date', now()->toDateString());
                $endDate = (string) $request->query('end_date', $startDate);

                if (!$this->validDate($startDate) || !$this->validDate($endDate)) {
                    return response()->json(['success' => false, 'message' => 'Invalid schedule date range.'], 422);
                }
                if ($endDate < $startDate) {
                    return response()->json(['success' => false, 'message' => 'End date cannot be before start date.'], 422);
                }

                $rows = DB::table('schedules as s')
                    ->join('employees as e', 'e.employee_id', '=', 's.employee_id')
                    ->select([
                        's.id', 's.employee_id', 's.schedule_date', 's.shift_name',
                        's.start_time', 's.end_time', 's.work_type', 's.remarks', 's.status',
                        'e.position_name',
                    ])
                    ->selectRaw("TRIM(CONCAT_WS(' ', e.first_name, NULLIF(e.middle_name, ''), e.last_name)) AS employee_name")
                    ->whereBetween('s.schedule_date', [$startDate, $endDate])
                    ->where('s.status', '<>', 'cancelled')
                    ->whereRaw("s.id = (SELECT MAX(s2.id) FROM schedules s2 WHERE s2.employee_id = s.employee_id AND s2.schedule_date = s.schedule_date AND s2.status <> 'cancelled')")
                    ->orderBy('s.schedule_date')
                    ->orderBy('e.employee_id')
                    ->orderBy('s.id')
                    ->get();

                return response()->json([
                    'success' => true,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'data' => $rows,
                ]);
            }

            if (!$request->isMethod('post')) {
                return response()->json(['success' => false, 'message' => 'Only GET and POST requests are allowed.'], 405);
            }

            $input = $request->json()->all();
            if (!is_array($input) || $input === []) {
                return response()->json(['success' => false, 'message' => 'Invalid request body.'], 400);
            }

            return match (trim((string) ($input['action'] ?? ''))) {
                'create' => $this->createSchedule($input),
                'reassign' => $this->reassignSchedule($input),
                default => response()->json(['success' => false, 'message' => 'Invalid schedule action.'], 422),
            };
        } catch (Throwable $e) {
            Log::error('Admin schedule API failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to process schedule request.'], 500);
        }
    }

    private function employeeOptions(Request $request): JsonResponse
    {
        $date = (string) $request->query('date', now()->toDateString());
        if (!$this->validDate($date)) {
            return response()->json(['success' => false, 'message' => 'Invalid schedule date.'], 422);
        }

        $rows = DB::table('employees as e')
            ->join('accounts as a', 'a.employee_id', '=', 'e.employee_id')
            ->select([
                'e.employee_id', 'e.first_name', 'e.middle_name', 'e.last_name',
                'e.position_name', 'e.employment_status', 'a.role as account_role', 'a.status as account_status',
            ])
            ->whereIn('a.role', ['employee', 'manager'])
            ->whereNotIn('e.employment_status', ['terminated', 'on_leave'])
            ->whereNotExists(function ($query) use ($date) {
                $query->selectRaw('1')->from('leave_requests as lr')
                    ->whereColumn('lr.employee_id', 'e.employee_id')
                    ->where('lr.status', 'approved')
                    ->where('lr.start_date', '<=', $date)
                    ->where('lr.end_date', '>=', $date);
            })
            ->orderByRaw("CASE WHEN a.role = 'manager' THEN 0 ELSE 1 END")
            ->orderBy('e.employee_id')
            ->get();

        $data = $rows->map(function ($row) {
            $name = implode(' ', array_filter([
                trim((string) ($row->first_name ?? '')),
                trim((string) ($row->middle_name ?? '')),
                trim((string) ($row->last_name ?? '')),
            ], fn ($part) => $part !== ''));
            $position = trim((string) ($row->position_name ?? ''));
            if ($position === '') {
                $position = strtolower((string) $row->account_role) === 'manager' ? 'Manager' : 'Employee';
            }
            $active = strtolower((string) $row->account_status) === 'active'
                && strtolower((string) $row->employment_status) === 'active';
            return [
                'id' => (string) $row->employee_id,
                'name' => $name,
                'position' => $position,
                'accountRole' => (string) $row->account_role,
                'status' => $active ? 'Active' : 'Inactive',
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function createSchedule(array $input): JsonResponse
    {
        $employeeId = trim((string) ($input['employee_id'] ?? ''));
        $date = trim((string) ($input['schedule_date'] ?? ''));
        $shift = trim((string) ($input['shift'] ?? ''));
        $workType = trim((string) ($input['work_type'] ?? ''));
        $remarks = trim((string) ($input['remarks'] ?? ''));

        if ($employeeId === '' || $date === '' || $shift === '' || $workType === '') {
            return response()->json(['success' => false, 'message' => 'Employee, date, shift, and work type are required.'], 422);
        }
        if (!$this->validDate($date)) {
            return response()->json(['success' => false, 'message' => 'Invalid schedule date.'], 422);
        }
        if (!isset(self::SHIFTS[$shift])) {
            return response()->json(['success' => false, 'message' => 'Invalid shift.'], 422);
        }
        if (!in_array($workType, self::WORK_TYPES, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid work type.'], 422);
        }

        return DB::transaction(function () use ($employeeId, $date, $shift, $workType, $remarks) {
            $employee = DB::table('employees as e')
                ->join('accounts as a', 'a.employee_id', '=', 'e.employee_id')
                ->where('e.employee_id', $employeeId)
                ->whereIn('a.role', ['employee', 'manager'])
                ->where('e.employment_status', '<>', 'terminated')
                ->first(['e.employee_id', 'e.employment_status', 'a.status as account_status']);

            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Selected employee account was not found.'], 404);
            }
            if (strtolower((string) $employee->employment_status) === 'on_leave') {
                return response()->json(['success' => false, 'message' => 'This employee is currently on leave.'], 409);
            }
            if (DB::table('leave_requests')->where('employee_id', $employeeId)->where('status', 'approved')->where('start_date', '<=', $date)->where('end_date', '>=', $date)->exists()) {
                return response()->json(['success' => false, 'message' => 'This employee has approved leave covering this date.'], 409);
            }
            if (DB::table('schedules')->where('employee_id', $employeeId)->where('schedule_date', $date)->where('status', '<>', 'cancelled')->exists()) {
                return response()->json(['success' => false, 'message' => 'This employee already has a schedule on this date.'], 409);
            }

            $shiftData = self::SHIFTS[$shift];
            $id = DB::table('schedules')->insertGetId([
                'employee_id' => $employeeId,
                'schedule_date' => $date,
                'shift_name' => $shiftData['shift_name'],
                'start_time' => $shiftData['start_time'],
                'end_time' => $shiftData['end_time'],
                'work_type' => $workType,
                'remarks' => $remarks !== '' ? $remarks : null,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Schedule saved successfully.', 'schedule_id' => (int) $id], 201);
        });
    }

    private function reassignSchedule(array $input): JsonResponse
    {
        $scheduleId = (int) ($input['schedule_id'] ?? 0);
        $replacementId = trim((string) ($input['replacement_employee_id'] ?? ''));
        if ($scheduleId <= 0 || $replacementId === '') {
            return response()->json(['success' => false, 'message' => 'Schedule and replacement employee are required.'], 422);
        }

        return DB::transaction(function () use ($scheduleId, $replacementId) {
            $schedule = DB::table('schedules')->where('id', $scheduleId)->first(['id', 'employee_id', 'schedule_date']);
            if (!$schedule) {
                return response()->json(['success' => false, 'message' => 'Schedule not found.'], 404);
            }
            if ((string) $schedule->employee_id === $replacementId) {
                return response()->json(['success' => false, 'message' => 'Replacement employee must be different from the current employee.'], 422);
            }

            $replacement = DB::table('employees as e')
                ->join('accounts as a', 'a.employee_id', '=', 'e.employee_id')
                ->where('e.employee_id', $replacementId)
                ->whereIn('a.role', ['employee', 'manager'])
                ->where('e.employment_status', '<>', 'terminated')
                ->first(['e.employee_id', 'e.employment_status']);

            if (!$replacement) {
                return response()->json(['success' => false, 'message' => 'Replacement employee was not found.'], 404);
            }
            if (strtolower((string) $replacement->employment_status) === 'on_leave') {
                return response()->json(['success' => false, 'message' => 'The replacement employee is currently on leave.'], 409);
            }
            if (DB::table('leave_requests')->where('employee_id', $replacementId)->where('status', 'approved')->where('start_date', '<=', $schedule->schedule_date)->where('end_date', '>=', $schedule->schedule_date)->exists()) {
                return response()->json(['success' => false, 'message' => 'The replacement employee has approved leave on this date.'], 409);
            }
            if (DB::table('schedules')->where('employee_id', $replacementId)->where('schedule_date', $schedule->schedule_date)->where('status', '<>', 'cancelled')->where('id', '<>', $scheduleId)->exists()) {
                return response()->json(['success' => false, 'message' => 'The replacement employee already has a schedule on this date.'], 409);
            }

            DB::table('schedules')->where('id', $scheduleId)->update([
                'employee_id' => $replacementId,
                'updated_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Schedule re-assigned successfully.']);
        });
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
