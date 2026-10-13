<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SharedDataController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = strtolower((string) ($user?->role ?? ''));

        if (! in_array($role, ['admin', 'manager', 'employee'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated Workforce Management access is required.',
            ], 403);
        }

        $collection = (string) $request->query('collection', '');
        $allowedCollections = [
            'employees',
            'attendanceRecords',
            'leaveRequests',
            'shiftRequests',
            'schedules',
        ];

        if (! in_array($collection, $allowedCollections, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported collection.',
            ], 422);
        }

        $currentEmployee = null;
        $teamEmployeeIds = [];

        if ($role !== 'admin') {
            $employeeId = trim((string) ($user?->employee_id ?? ''));
            if ($employeeId === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is not linked to an employee record.',
                ], 403);
            }

            $currentEmployee = DB::table('employees')
                ->where('employee_id', $employeeId)
                ->first();

            if (! $currentEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'The employee record for this account was not found.',
                ], 403);
            }

            if ($role === 'manager') {
                $teamEmployeeIds = DB::table('employees')
                    ->where('manager_id', $currentEmployee->id)
                    ->orderBy('employee_id')
                    ->pluck('employee_id')
                    ->map(fn ($id) => (string) $id)
                    ->all();
            } else {
                $teamEmployeeIds = [$employeeId];
            }
        }

        $applyEmployeeScope = function ($query, string $column = 'employee_id') use ($role, $currentEmployee, $teamEmployeeIds) {
            if ($role === 'employee') {
                $query->where($column, (string) $currentEmployee->employee_id);
            } elseif ($role === 'manager') {
                if ($teamEmployeeIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn($column, $teamEmployeeIds);
                }
            }

            return $query;
        };

        $data = match ($collection) {
            'employees' => $this->employees($role, $currentEmployee),
            'attendanceRecords' => $this->attendanceRecords($applyEmployeeScope),
            'leaveRequests' => $this->leaveRequests($applyEmployeeScope),
            'shiftRequests' => $this->shiftRequests($applyEmployeeScope),
            'schedules' => $this->schedules($applyEmployeeScope),
        };

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /** Return only records visible to the current role, in the legacy JS shape. */
    private function employees(string $role, ?object $currentEmployee): array
    {
        $query = DB::table('employees as e')
            ->leftJoin('employees as manager', 'manager.id', '=', 'e.manager_id')
            ->select([
                'e.employee_id', 'e.first_name', 'e.middle_name', 'e.last_name',
                'e.email', 'e.department_name', 'e.position_name', 'e.employment_status',
                'manager.employee_id as manager_employee_id',
            ]);

        if ($role === 'employee') {
            $query->where('e.employee_id', (string) $currentEmployee->employee_id);
        } elseif ($role === 'manager') {
            $query->where('e.manager_id', $currentEmployee->id);
        }

        $rows = $query->orderBy('e.employee_id')->get();

        return $rows->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                trim((string) ($row->first_name ?? '')),
                trim((string) ($row->middle_name ?? '')),
                trim((string) ($row->last_name ?? '')),
            ], fn ($part) => $part !== '')));
            $initials = strtoupper(substr((string) ($row->first_name ?? ''), 0, 1)
                . substr((string) ($row->last_name ?? ''), 0, 1));
            $accountRole = DB::table('accounts')
                ->where('employee_id', (string) $row->employee_id)
                ->orderBy('id')
                ->value('role');

            return [
                'id' => (string) $row->employee_id,
                'employeeId' => (string) $row->employee_id,
                'name' => $name,
                'email' => (string) ($row->email ?? ''),
                'role' => (string) ($accountRole ?: 'employee'),
                'position' => (string) ($row->position_name ?? ''),
                'department' => (string) ($row->department_name ?? ''),
                'managerId' => $row->manager_employee_id !== null ? (string) $row->manager_employee_id : null,
                'initials' => $initials,
                'status' => strtoupper((string) ($row->employment_status ?? 'active')),
            ];
        })->values()->all();
    }

    private function attendanceRecords(callable $applyEmployeeScope): array
    {
        $query = DB::table('attendance_records')
            ->select(['id', 'employee_id', 'attendance_date', 'time_in', 'time_out', 'status']);
        $applyEmployeeScope($query);

        return $query->orderByDesc('attendance_date')->orderByDesc('id')->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'employeeId' => (string) $row->employee_id,
                    'date' => Carbon::parse($row->attendance_date)->toDateString(),
                    'schedule' => null,
                    'clockIn' => $row->time_in ? Carbon::parse($row->time_in)->format('H:i') : null,
                    'clockOut' => $row->time_out ? Carbon::parse($row->time_out)->format('H:i') : null,
                    'status' => $this->titleCaseStatus((string) $row->status),
                ];
            })->values()->all();
    }

    private function leaveRequests(callable $applyEmployeeScope): array
    {
        $query = DB::table('leave_requests as l')
            ->leftJoin('employees as e', 'e.employee_id', '=', 'l.employee_id')
            ->select([
                'l.id', 'l.employee_id', 'l.leave_type', 'l.start_date', 'l.end_date',
                'l.reason', 'l.status', 'e.first_name', 'e.last_name',
            ]);
        $applyEmployeeScope($query, 'l.employee_id');

        return $query->orderByDesc('l.created_at')->orderByDesc('l.id')->get()
            ->map(function ($row) {
                $start = Carbon::parse($row->start_date);
                $end = Carbon::parse($row->end_date);
                $dates = $start->isSameDay($end)
                    ? $start->format('M j')
                    : $start->format('M j').' - '.$end->format('M j');
                $name = trim((string) ($row->first_name ?? '').' '.(string) ($row->last_name ?? ''));

                return [
                    'id' => (int) $row->id,
                    'employeeId' => (string) $row->employee_id,
                    'name' => $name,
                    'initials' => strtoupper(substr((string) ($row->first_name ?? ''), 0, 1)
                        . substr((string) ($row->last_name ?? ''), 0, 1)),
                    'type' => (string) $row->leave_type,
                    'status' => $this->titleCaseStatus((string) $row->status),
                    'dates' => $dates,
                    'startDate' => $start->toDateString(),
                    'endDate' => $end->toDateString(),
                    'reason' => (string) ($row->reason ?? ''),
                ];
            })->values()->all();
    }

    private function shiftRequests(callable $applyEmployeeScope): array
    {
        $query = DB::table('shift_requests')
            ->select(['id', 'employee_id', 'requested_date', 'requested_shift', 'reason', 'status', 'reviewed_at', 'created_at']);
        $applyEmployeeScope($query);

        return $query->orderByDesc('created_at')->orderByDesc('id')->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'employeeId' => (string) $row->employee_id,
                'date' => Carbon::parse($row->requested_date)->toDateString(),
                'requestedDate' => Carbon::parse($row->requested_date)->toDateString(),
                'requestedShift' => (string) $row->requested_shift,
                'shift' => (string) $row->requested_shift,
                'reason' => (string) ($row->reason ?? ''),
                'status' => $this->titleCaseStatus((string) $row->status),
                'reviewedAt' => $row->reviewed_at,
                'createdAt' => $row->created_at,
            ])->values()->all();
    }

    private function schedules(callable $applyEmployeeScope): array
    {
        $query = DB::table('schedules')
            ->select(['employee_id', 'schedule_date', 'shift_name', 'start_time', 'end_time', 'status']);
        $applyEmployeeScope($query);

        $weekly = [];
        foreach ($query->orderBy('employee_id')->orderBy('schedule_date')->get() as $row) {
            $date = Carbon::parse($row->schedule_date);
            $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
            $key = (string) $row->employee_id.'|'.$weekStart->toDateString();

            if (! isset($weekly[$key])) {
                $weekly[$key] = [
                    'employeeId' => (string) $row->employee_id,
                    'week' => $weekStart->format('M j').'-'.$weekStart->copy()->addDays(6)->format('j, Y'),
                    'mon' => 'OFF', 'tue' => 'OFF', 'wed' => 'OFF',
                    'thu' => 'OFF', 'fri' => 'OFF',
                ];
            }

            $dayKey = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri'][$date->dayOfWeekIso] ?? null;
            if ($dayKey !== null) {
                $shift = trim((string) ($row->shift_name ?? ''));
                if ($shift === '' && $row->start_time && $row->end_time) {
                    $shift = Carbon::parse($row->start_time)->format('H:i').'-'.Carbon::parse($row->end_time)->format('H:i');
                }
                $weekly[$key][$dayKey] = $shift !== '' ? $shift : strtoupper((string) $row->status);
            }
        }

        return array_values($weekly);
    }

    private function titleCaseStatus(string $status): string
    {
        return match (strtolower($status)) {
            'half_day' => 'Half Day',
            'on_leave' => 'On Leave',
            default => ucfirst(str_replace('_', ' ', strtolower($status))),
        };
    }
}
