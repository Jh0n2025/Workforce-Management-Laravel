<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceApiController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === 'admin';
    }

    private function formatTime($value, string $fallback = '--'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        return Carbon::parse((string) $value)->format('g:i A');
    }

    public function today(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

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
            ->where('e.employment_status', '<>', 'terminated')
            ->orderBy('e.employee_id')
            ->get([
                'e.employee_id',
                'e.first_name',
                'e.middle_name',
                'e.last_name',
                'e.department_name',
                'e.employment_status',
                'a.attendance_date',
                'a.time_in',
                'a.time_out',
                'a.status as attendance_status',
                'a.source',
                's.start_time',
                's.end_time',
                'lr.status as leave_status',
            ]);

        $records = $rows->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ], fn ($value) => $value !== null && $value !== '')));

            if ($row->leave_status) {
                $status = 'LEAVE';
            } elseif ($row->attendance_status) {
                $status = strtoupper((string) $row->attendance_status);
            } elseif ($row->employment_status === 'on_leave') {
                $status = 'LEAVE';
            } elseif ($row->time_in) {
                $status = 'PRESENT';
            } else {
                $status = 'ABSENT';
            }

            $schedule = 'N/A';

            if ($row->start_time && $row->end_time) {
                $schedule = $this->formatTime($row->start_time)
                    . ' - '
                    . $this->formatTime($row->end_time);
            }

            return [
                'id' => $row->employee_id,
                'name' => strtoupper($name),
                'department' => strtoupper((string) ($row->department_name ?? '')),
                'schedule' => $schedule,
                'clockIn' => $this->formatTime($row->time_in),
                'clockOut' => $this->formatTime($row->time_out),
                'status' => $status,
                'source' => $row->source,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }

    public function records(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $startDate = $request->query(
            'start_date',
            now()->startOfMonth()->toDateString()
        );

        $endDate = $request->query(
            'end_date',
            now()->toDateString()
        );

        $department = trim((string) $request->query('department', ''));
        $status = trim((string) $request->query('status', ''));
        $employeeId = trim((string) $request->query('employee_id', ''));

        $query = DB::table('attendance_records as a')
            ->join('employees as e', 'e.employee_id', '=', 'a.employee_id')
            ->leftJoin('schedules as s', function ($join) {
                $join->on('s.employee_id', '=', 'a.employee_id')
                    ->on('s.schedule_date', '=', 'a.attendance_date')
                    ->where('s.status', '<>', 'cancelled');
            })
            ->whereDate('a.attendance_date', '>=', $startDate)
            ->whereDate('a.attendance_date', '<=', $endDate);

        if ($department !== '') {
            $query->whereRaw(
                'UPPER(e.department_name) = UPPER(?)',
                [$department]
            );
        }

        if ($status !== '') {
            $query->whereRaw('UPPER(a.status) = UPPER(?)', [$status]);
        }

        if ($employeeId !== '') {
            $query->where('a.employee_id', $employeeId);
        }

        $rows = $query
            ->orderByDesc('a.attendance_date')
            ->orderBy('a.employee_id')
            ->get([
                'a.id',
                'a.employee_id',
                'a.attendance_date',
                'a.time_in',
                'a.time_out',
                'a.status',
                'a.source',
                'e.first_name',
                'e.middle_name',
                'e.last_name',
                'e.department_name',
                's.shift_name',
                's.start_time',
                's.end_time',
            ]);

        $records = $rows->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ], fn ($value) => $value !== null && $value !== '')));

            $schedule = '--';

            if ($row->start_time && $row->end_time) {
                $schedule = $this->formatTime($row->start_time)
                    . ' - '
                    . $this->formatTime($row->end_time);
            } elseif ($row->shift_name) {
                $schedule = $row->shift_name;
            }

            return [
                'id' => (int) $row->id,
                'date' => (string) $row->attendance_date,
                'employeeId' => $row->employee_id,
                'employee' => strtoupper($name),
                'department' => strtoupper((string) ($row->department_name ?? '')),
                'schedule' => $schedule,
                'clockIn' => $this->formatTime($row->time_in),
                'clockOut' => $this->formatTime($row->time_out),
                'status' => strtoupper((string) $row->status),
                'source' => $row->source,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $records,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'department' => $department,
                'status' => $status,
                'employee_id' => $employeeId,
            ],
        ]);
    }

    public function corrections(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $rows = DB::table('attendance_corrections as ac')
            ->join('employees as e', 'e.employee_id', '=', 'ac.employee_id')
            ->leftJoin('attendance_records as ar', 'ar.id', '=', 'ac.attendance_record_id')
            ->orderByRaw("CASE WHEN ac.status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('ac.created_at')
            ->orderByDesc('ac.id')
            ->get([
                'ac.id',
                'ac.employee_id',
                'ac.correction_date',
                'ac.requested_clock_in',
                'ac.requested_clock_out',
                'ac.reason',
                'ac.attachment',
                'ac.status',
                'ar.time_in as current_clock_in',
                'ar.time_out as current_clock_out',
                'e.first_name',
                'e.middle_name',
                'e.last_name',
            ]);

        $records = $rows->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ], fn ($value) => $value !== null && $value !== '')));

            return [
                'id' => (int) $row->id,
                'employeeId' => $row->employee_id,
                'employee' => $name,
                'date' => (string) $row->correction_date,
                'clockin' => $row->current_clock_in,
                'clockout' => $row->current_clock_out,
                'requestedClockin' => $row->requested_clock_in,
                'requestedClockout' => $row->requested_clock_out,
                'reason' => $row->reason ?? '',
                'attachment' => $row->attachment ?? '',
                'status' => strtoupper((string) $row->status),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $records,
        ]);
    }

    /**
     * Employee: get today's attendance/schedule and record clock-in/clock-out.
     * Preserves the original api/attendance/my-clock.php response format.
     */
    public function myClock(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user?->role !== 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'Employee access required.',
            ], 403);
        }

        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee session not found.',
            ], 401);
        }

        $today = now()->toDateString();

        if ($request->isMethod('GET')) {
            try {
                $attendance = DB::table('attendance_records')
                    ->where('employee_id', $employeeId)
                    ->whereDate('attendance_date', $today)
                    ->orderByDesc('id')
                    ->first([
                        'id', 'attendance_date', 'time_in', 'time_out', 'status',
                    ]);

                $schedule = DB::table('schedules')
                    ->where('employee_id', $employeeId)
                    ->whereDate('schedule_date', $today)
                    ->where('status', '<>', 'cancelled')
                    ->orderByDesc('id')
                    ->first(['shift_name', 'start_time', 'end_time']);

                if ($attendance) {
                    $attendance = (array) $attendance;
                    $attendance['shift_name'] = $schedule->shift_name ?? null;
                    $attendance['start_time'] = $schedule->start_time ?? null;
                    $attendance['end_time'] = $schedule->end_time ?? null;
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'attendance' => $attendance ?: null,
                        'schedule' => $schedule ? (array) $schedule : null,
                    ],
                ]);
            } catch (Throwable $e) {
                Log::error('Employee clock API read failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to load today attendance.',
                ], 500);
            }
        }

        if (!$request->isMethod('POST')) {
            return response()->json([
                'success' => false,
                'message' => 'Only GET and POST requests are allowed.',
            ], 405);
        }

        $action = trim((string) $request->input('action', ''));
        if (!in_array($action, ['time_in', 'time_out'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid attendance action.',
            ], 400);
        }

        try {
            $payload = DB::transaction(function () use ($employeeId, $today, $action) {
                $attendance = DB::table('attendance_records')
                    ->where('employee_id', $employeeId)
                    ->whereDate('attendance_date', $today)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first(['id', 'time_in', 'time_out', 'status']);

                $schedule = DB::table('schedules')
                    ->where('employee_id', $employeeId)
                    ->whereDate('schedule_date', $today)
                    ->where('status', '<>', 'cancelled')
                    ->orderByDesc('id')
                    ->first(['shift_name', 'start_time', 'end_time']);

                if ($action === 'time_in') {
                    $onApprovedLeave = DB::table('leave_requests')
                        ->where('employee_id', $employeeId)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->exists();

                    if ($onApprovedLeave) {
                        return [
                            'success' => false,
                            'message' => 'You are on approved leave today and cannot clock in.',
                        ];
                    }

                    if ($attendance && !empty($attendance->time_in)) {
                        return [
                            'success' => false,
                            'message' => 'You are already clocked in today.',
                        ];
                    }

                    $status = 'present';
                    if ($schedule && !empty($schedule->start_time)) {
                        $scheduledStart = Carbon::parse($today . ' ' . $schedule->start_time);
                        if (now()->greaterThan($scheduledStart)) {
                            $status = 'late';
                        }
                    }

                    if ($attendance) {
                        DB::table('attendance_records')
                            ->where('id', $attendance->id)
                            ->update([
                                'time_in' => now(),
                                'time_out' => null,
                                'status' => $status,
                                'source' => 'manual',
                                'notes' => 'Clocked in from employee portal.',
                                'updated_at' => now(),
                            ]);
                        $attendanceId = (int) $attendance->id;
                    } else {
                        $attendanceId = (int) DB::table('attendance_records')->insertGetId([
                            'employee_id' => $employeeId,
                            'attendance_date' => $today,
                            'time_in' => now(),
                            'time_out' => null,
                            'status' => $status,
                            'source' => 'manual',
                            'notes' => 'Clocked in from employee portal.',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], 'id');
                    }

                    return [
                        'success' => true,
                        'message' => $status === 'late'
                            ? 'Clock in recorded as late.'
                            : 'Clock in recorded successfully.',
                        'action' => 'time_in',
                        'attendance_id' => $attendanceId,
                        'status' => $status,
                    ];
                }

                if (!$attendance || empty($attendance->time_in)) {
                    return [
                        'success' => false,
                        'message' => 'Please clock in first.',
                    ];
                }

                if (!empty($attendance->time_out)) {
                    return [
                        'success' => false,
                        'message' => 'You are already clocked out today.',
                    ];
                }

                DB::table('attendance_records')
                    ->where('id', $attendance->id)
                    ->update([
                        'time_out' => now(),
                        'source' => 'manual',
                        'updated_at' => now(),
                    ]);

                return [
                    'success' => true,
                    'message' => 'Clock out recorded successfully.',
                    'action' => 'time_out',
                    'attendance_id' => (int) $attendance->id,
                ];
            });

            return response()->json($payload);
        } catch (Throwable $e) {
            Log::error('Employee clock API failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to update attendance.',
            ], 500);
        }
    }

    /** Employee/manager: return attendance history for the signed-in account. */
    public function myRecords(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user?->role, ['employee', 'manager'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee session not found.',
            ], 401);
        }

        try {
            $records = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->orderByDesc('attendance_date')
                ->orderByDesc('id')
                ->get([
                    'id', 'attendance_date', 'time_in', 'time_out', 'status', 'source',
                ]);

            return response()->json([
                'success' => true,
                'records' => $records,
            ]);
        } catch (Throwable $e) {
            Log::error('My attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to load attendance records.',
            ], 500);
        }
    }

}