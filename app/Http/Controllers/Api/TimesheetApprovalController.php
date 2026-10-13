<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TimesheetApprovalController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === 'admin';
    }

    private function formatTimeValue(?string $value): string
    {
        if (!$value) {
            return '—';
        }

        $timestamp = strtotime($value);

        return $timestamp === false
            ? '—'
            : date('g:i A', $timestamp);
    }

    private function calculateDailyHours(
        ?string $timeIn,
        ?string $timeOut
    ): array {
        if (!$timeIn || !$timeOut) {
            return ['regular' => 0.0, 'overtime' => 0.0];
        }

        $start = strtotime($timeIn);
        $end = strtotime($timeOut);

        if ($start === false || $end === false || $end <= $start) {
            return ['regular' => 0.0, 'overtime' => 0.0];
        }

        // Preserve the original one-hour unpaid-break calculation.
        $workedMinutes = max(
            0,
            (int) floor(($end - $start) / 60) - 60
        );

        return [
            'regular' => round(min(480, $workedMinutes) / 60, 2),
            'overtime' => round(max(0, $workedMinutes - 480) / 60, 2),
        ];
    }

    private function uiStatus(
        string $databaseStatus,
        ?string $latestHistoryStatus
    ): string {
        $databaseStatus = strtolower(trim($databaseStatus));

        if ($databaseStatus === 'approved') {
            return 'Approved';
        }

        if ($databaseStatus === 'rejected') {
            return 'Rejected';
        }

        if (strtolower(trim((string) $latestHistoryStatus)) === 'correction requested') {
            return 'Correction Requested';
        }

        return 'Pending';
    }

    private function formatPeriodLabel(
        string $start,
        string $end
    ): string {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        return $startDate->format('M j')
            . ' - '
            . $endDate->format('M j, Y');
    }

    public function index(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'ok' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        try {
            // Return the latest review for each employee/pay period.
            $reviews = DB::table('timesheet_reviews as tr')
                ->join('employees as e', 'e.employee_id', '=', 'tr.employee_id')
                ->whereRaw("
                    tr.id = (
                        SELECT MAX(tr2.id)
                        FROM timesheet_reviews tr2
                        WHERE tr2.employee_id = tr.employee_id
                          AND tr2.period_start = tr.period_start
                          AND tr2.period_end = tr.period_end
                    )
                ")
                ->whereColumn('tr.period_start', '<', 'tr.period_end')
                ->orderByDesc('tr.period_start')
                ->orderByDesc('tr.id')
                ->get([
                    'tr.id',
                    'tr.employee_id',
                    'tr.period_start',
                    'tr.period_end',
                    'tr.status',
                    'tr.reviewed_by',
                    'tr.reviewed_at',
                    'tr.remarks',
                    'tr.created_at',
                    'e.first_name',
                    'e.middle_name',
                    'e.last_name',
                    'e.department_name',
                ]);

            // Load history and retain the latest status for each review.
            $historyRows = DB::table('timesheet_review_history as h')
                ->join('employees as e', 'e.employee_id', '=', 'h.employee_id')
                ->leftJoin('accounts as ra', 'ra.id', '=', 'h.reviewed_by')
                ->leftJoin('employees as reviewer', 'reviewer.employee_id', '=', 'ra.employee_id')
                ->orderByDesc('h.reviewed_at')
                ->orderByDesc('h.id')
                ->get([
                    'h.timesheet_review_id',
                    'h.previous_status',
                    'h.new_status',
                    'h.action',
                    'h.reason',
                    'h.reviewed_at',
                    'e.first_name',
                    'e.middle_name',
                    'e.last_name',
                    DB::raw("
                        CONCAT_WS(
                            ' ',
                            reviewer.first_name,
                            reviewer.middle_name,
                            reviewer.last_name
                        ) AS reviewer_name
                    "),
                ]);

            $historyState = [];
            $history = [];

            foreach ($historyRows as $row) {
                $reviewId = (int) $row->timesheet_review_id;

                if (!array_key_exists($reviewId, $historyState)) {
                    $historyState[$reviewId] = (string) $row->new_status;
                }

                $history[] = [
                    'employee' => trim(implode(' ', array_filter([
                        $row->first_name,
                        $row->middle_name,
                        $row->last_name,
                    ]))),
                    'action' => (string) ($row->action ?? ''),
                    'previousStatus' => (string) ($row->previous_status ?? ''),
                    'newStatus' => (string) ($row->new_status ?? ''),
                    'admin' => trim((string) ($row->reviewer_name ?? '')) ?: 'Admin',
                    'timestamp' => $row->reviewed_at
                        ? Carbon::parse($row->reviewed_at)->format('F j, Y - g:i A')
                        : '—',
                    'reason' => (string) ($row->reason ?? ''),
                ];
            }

            $timesheets = [];

            foreach ($reviews as $review) {
                $reviewId = (int) $review->id;
                $employeeId = (string) $review->employee_id;
                $periodStart = (string) $review->period_start;
                $periodEnd = (string) $review->period_end;

                // Fetch scheduled days and their latest attendance records.
                $scheduleRows = DB::table('schedules as s')
                    ->leftJoin('attendance_records as a', function ($join) {
                        $join->on('a.employee_id', '=', 's.employee_id')
                            ->on('a.attendance_date', '=', 's.schedule_date')
                            ->whereRaw("
                                a.id = (
                                    SELECT MAX(a2.id)
                                    FROM attendance_records a2
                                    WHERE a2.employee_id = s.employee_id
                                      AND a2.attendance_date = s.schedule_date
                                )
                            ");
                    })
                    ->where('s.employee_id', $employeeId)
                    ->whereBetween('s.schedule_date', [$periodStart, $periodEnd])
                    ->where('s.status', '<>', 'cancelled')
                    ->orderBy('s.schedule_date')
                    ->get([
                        's.schedule_date',
                        's.shift_name',
                        's.start_time',
                        's.end_time',
                        'a.time_in',
                        'a.time_out',
                        'a.status as attendance_status',
                    ]);

                $dailyByDate = [];

                foreach ($scheduleRows as $row) {
                    $dailyByDate[(string) $row->schedule_date] = $row;
                }

                $dailyEntries = [];
                $regularHours = 0.0;
                $overtimeHours = 0.0;

                $period = CarbonPeriod::create(
                    Carbon::parse($periodStart),
                    Carbon::parse($periodEnd)
                );

                foreach ($period as $date) {
                    // The original timesheet includes weekdays only.
                    if ($date->isWeekend()) {
                        continue;
                    }

                    $dateValue = $date->toDateString();
                    $row = $dailyByDate[$dateValue] ?? null;

                    $timeIn = $row?->time_in
                        ? (string) $row->time_in
                        : null;

                    $timeOut = $row?->time_out
                        ? (string) $row->time_out
                        : null;

                    $hours = $this->calculateDailyHours($timeIn, $timeOut);

                    $regularHours += $hours['regular'];
                    $overtimeHours += $hours['overtime'];

                    $rawStatus = strtolower(
                        (string) ($row?->attendance_status ?? '')
                    );

                    $attendanceStatus = match ($rawStatus) {
                        'present', 'half_day' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                        'on_leave' => 'Leave',
                        default => '',
                    };

                    $hasApprovedLeave = DB::table('leave_requests')
                        ->where('employee_id', $employeeId)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $dateValue)
                        ->whereDate('end_date', '>=', $dateValue)
                        ->exists();

                    if ($hasApprovedLeave) {
                        $attendanceStatus = 'Leave';
                    }

                    if ($attendanceStatus === '') {
                        $shiftName = strtoupper(
                            trim((string) ($row?->shift_name ?? ''))
                        );

                        $attendanceStatus = $row && $shiftName !== ''
                            && $shiftName !== 'OFF'
                            ? 'Missing Record'
                            : 'Absent';
                    }

                    $dailyEntries[] = [
                        'date' => $dateValue,
                        'day' => $date->format('D'),
                        'timeIn' => $this->formatTimeValue($timeIn),
                        'timeOut' => $this->formatTimeValue($timeOut),
                        'break' => $timeIn && $timeOut ? '1 hr' : '—',
                        'regularHours' => $hours['regular'],
                        'overtime' => $hours['overtime'],
                        'attendanceStatus' => $attendanceStatus,
                    ];
                }

                $status = $this->uiStatus(
                    (string) $review->status,
                    $historyState[$reviewId] ?? null
                );

                $timesheets[] = [
                    'id' => (string) $reviewId,
                    'avatar' => '',
                    'employee' => trim(implode(' ', array_filter([
                        $review->first_name,
                        $review->middle_name,
                        $review->last_name,
                    ]))),
                    'employeeId' => $employeeId,
                    'department' => (string) ($review->department_name ?? ''),
                    'period' => $this->formatPeriodLabel($periodStart, $periodEnd),
                    'regularHours' => round($regularHours, 2),
                    'overtimeHours' => round($overtimeHours, 2),
                    'status' => $status,
                    'submittedDate' => $review->created_at
                        ? Carbon::parse($review->created_at)->format('F j, Y')
                        : '—',
                    'rejectionReason' => $status === 'Rejected'
                        ? (string) ($review->remarks ?? '')
                        : '',
                    'correctionReason' => $status === 'Correction Requested'
                        ? (string) ($review->remarks ?? '')
                        : '',
                    'dailyEntries' => $dailyEntries,
                ];
            }

            return response()->json([
                'ok' => true,
                'message' => '',
                'timesheets' => $timesheets,
                'history' => $history,
            ]);
        } catch (Throwable $e) {
            Log::error('Timesheet approvals GET failed: ' . $e->getMessage());

            return response()->json([
                'ok' => false,
                'message' => 'Unable to load timesheet approvals.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'ok' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $action = trim((string) $request->input('action', ''));
        $accountId = (int) $request->user()->id;

        try {
            if ($action === 'bulk_approve') {
                $ids = $request->input('ids', []);

                if (!is_array($ids)) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Invalid timesheet list.',
                    ], 422);
                }

                $changed = DB::transaction(function () use ($ids, $accountId) {
                    $changed = 0;

                    foreach ($ids as $rawId) {
                        $id = (int) $rawId;

                        if ($id <= 0) {
                            continue;
                        }

                        $review = DB::table('timesheet_reviews')
                            ->where('id', $id)
                            ->lockForUpdate()
                            ->first();

                        if (!$review || $review->status !== 'pending') {
                            continue;
                        }

                        DB::table('timesheet_reviews')
                            ->where('id', $id)
                            ->update([
                                'status' => 'approved',
                                'reviewed_by' => $accountId,
                                'reviewed_at' => now(),
                                'remarks' => 'Bulk approval by admin.',
                                'updated_at' => now(),
                            ]);

                        DB::table('timesheet_review_history')->insert([
                            'timesheet_review_id' => $id,
                            'employee_id' => $review->employee_id,
                            'period_start' => $review->period_start,
                            'period_end' => $review->period_end,
                            'previous_status' => 'Pending',
                            'new_status' => 'Approved',
                            'action' => 'Timesheet Approved',
                            'reviewed_by' => $accountId,
                            'reason' => 'Bulk approval by admin.',
                            'reviewed_at' => now(),
                        ]);

                        $changed++;
                    }

                    return $changed;
                });

                return response()->json([
                    'ok' => true,
                    'message' => 'Timesheets approved successfully.',
                    'changed' => $changed,
                ]);
            }

            if ($action !== 'update') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid timesheet action.',
                ], 400);
            }

            $id = (int) $request->input('id', 0);
            $requestedStatus = trim((string) $request->input('status', ''));
            $reason = trim((string) $request->input('reason', ''));

            if ($id <= 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid timesheet ID.',
                ], 422);
            }

            if (!in_array($requestedStatus, [
                'Approved',
                'Rejected',
                'Correction Requested',
            ], true)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid timesheet status.',
                ], 422);
            }

            $result = DB::transaction(function () use (
                $id,
                $requestedStatus,
                $reason,
                $accountId
            ) {
                $review = DB::table('timesheet_reviews')
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$review) {
                    return [
                        'status' => 404,
                        'body' => [
                            'ok' => false,
                            'message' => 'Timesheet review not found.',
                        ],
                    ];
                }

                if (in_array($review->status, ['approved', 'rejected'], true)) {
                    return [
                        'status' => 409,
                        'body' => [
                            'ok' => false,
                            'message' => 'This timesheet has already been processed.',
                        ],
                    ];
                }

                $latestHistory = DB::table('timesheet_review_history')
                    ->where('timesheet_review_id', $id)
                    ->orderByDesc('id')
                    ->value('new_status');

                $previousStatus = $this->uiStatus(
                    (string) $review->status,
                    $latestHistory
                );

                $databaseStatus = match ($requestedStatus) {
                    'Approved' => 'approved',
                    'Rejected' => 'rejected',
                    default => 'pending',
                };

                $actionLabel = match ($requestedStatus) {
                    'Approved' => 'Timesheet Approved',
                    'Rejected' => 'Timesheet Rejected',
                    default => 'Correction Requested',
                };

                DB::table('timesheet_reviews')
                    ->where('id', $id)
                    ->update([
                        'status' => $databaseStatus,
                        'reviewed_by' => $accountId,
                        'reviewed_at' => now(),
                        'remarks' => $reason !== '' ? $reason : null,
                        'updated_at' => now(),
                    ]);

                DB::table('timesheet_review_history')->insert([
                    'timesheet_review_id' => $id,
                    'employee_id' => $review->employee_id,
                    'period_start' => $review->period_start,
                    'period_end' => $review->period_end,
                    'previous_status' => $previousStatus,
                    'new_status' => $requestedStatus,
                    'action' => $actionLabel,
                    'reviewed_by' => $accountId,
                    'reason' => $reason !== '' ? $reason : null,
                    'reviewed_at' => now(),
                ]);

                return [
                    'status' => 200,
                    'body' => [
                        'ok' => true,
                        'message' => 'Timesheet approval saved successfully.',
                    ],
                ];
            });

            return response()->json($result['body'], $result['status']);
        } catch (Throwable $e) {
            Log::error('Timesheet approvals POST failed: ' . $e->getMessage());

            return response()->json([
                'ok' => false,
                'message' => 'Unable to process timesheet approval.',
            ], 500);
        }
    }
}