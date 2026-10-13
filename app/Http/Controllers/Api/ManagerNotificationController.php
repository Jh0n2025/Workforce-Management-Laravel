<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ManagerNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $account = $request->user();
        if (!$account || $account->role !== 'manager') {
            return response()->json([
                'success' => false,
                'message' => 'Manager access required.',
            ], 403);
        }

        try {
            $managerEmployeeId = trim((string) ($account->employee_id ?? ''));
            $managerInternalId = $managerEmployeeId !== ''
                ? DB::table('employees')->where('employee_id', $managerEmployeeId)->value('id')
                : null;

            if (!$managerInternalId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager employee record not found.',
                ], 404);
            }

            $teamIds = DB::table('employees')
                ->where('manager_id', (int) $managerInternalId)
                ->where('employment_status', '<>', 'terminated')
                ->pluck('employee_id')
                ->map(fn ($value) => (string) $value)
                ->all();

            $pendingLeaveCount = 0;
            $latestPendingLeave = null;
            $pendingShiftCount = 0;
            $latestPendingShift = null;
            $latestScheduleChange = null;

            if ($teamIds !== []) {
                $pendingLeaves = DB::table('leave_requests')
                    ->whereIn('employee_id', $teamIds)
                    ->where('status', 'pending');
                $pendingLeaveCount = (clone $pendingLeaves)->count();
                $latestPendingLeave = (clone $pendingLeaves)->max('created_at');

                $pendingShifts = DB::table('shift_requests')
                    ->whereIn('employee_id', $teamIds)
                    ->where('status', 'pending');
                $pendingShiftCount = (clone $pendingShifts)->count();
                $latestPendingShift = (clone $pendingShifts)->max('created_at');

                $latestScheduleChange = DB::table('schedules')
                    ->whereIn('employee_id', $teamIds)
                    ->where('status', '<>', 'cancelled')
                    ->max('updated_at');
            }

            $reads = DB::table('manager_notification_reads')
                ->where('manager_id', (int) $managerInternalId)
                ->get(['notification_type', 'read_at'])
                ->mapWithKeys(fn ($row) => [(string) $row->notification_type => $row->read_at]);

            $isUnread = static function (string $type, ?string $latestActivity, bool $hasPending) use ($reads): bool {
                $readAt = $reads->get($type);
                if ($type === 'analytics') {
                    return $readAt === null;
                }
                if (!$hasPending && $readAt === null) {
                    return false;
                }
                if ($readAt === null) {
                    return $hasPending;
                }
                if (!$latestActivity) {
                    return false;
                }
                try {
                    return Carbon::parse($latestActivity)->gt(Carbon::parse((string) $readAt));
                } catch (Throwable) {
                    return false;
                }
            };

            $leaveMessage = $pendingLeaveCount > 0
                ? $pendingLeaveCount . ' pending leave request' . ($pendingLeaveCount === 1 ? '' : 's') . ' need review.'
                : 'No pending leave requests currently. Open the team leave requests to review activity.';

            $scheduleMessage = $pendingShiftCount > 0
                ? $pendingShiftCount . ' pending shift request' . ($pendingShiftCount === 1 ? '' : 's') . ' need review. Check team assignments.'
                : 'Review the team schedule and current assignments.';

            $scheduleLatestActivity = collect([$latestPendingShift, $latestScheduleChange])
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->sort()
                ->last();

            $notifications = [
                [
                    'type' => 'analytics',
                    'message' => 'Open the team attendance analytics and review current workforce activity.',
                    'unread' => $isUnread('analytics', null, false),
                ],
                [
                    'type' => 'leave',
                    'message' => $leaveMessage,
                    'count' => $pendingLeaveCount,
                    'unread' => $isUnread('leave', $latestPendingLeave ? (string) $latestPendingLeave : null, $pendingLeaveCount > 0),
                ],
                [
                    'type' => 'schedule',
                    'message' => $scheduleMessage,
                    'count' => $pendingShiftCount,
                    'unread' => $isUnread('schedule', $scheduleLatestActivity ? (string) $scheduleLatestActivity : null, $pendingShiftCount > 0),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'unread_count' => count(array_filter($notifications, fn ($item) => $item['unread'])),
            ]);
        } catch (Throwable $e) {
            Log::error('Manager notifications API failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to load manager notifications.',
            ], 500);
        }
    }

    public function markRead(Request $request): JsonResponse
    {
        $account = $request->user();
        if (!$account || $account->role !== 'manager') {
            return response()->json([
                'success' => false,
                'message' => 'Manager access required.',
            ], 403);
        }

        $managerEmployeeId = trim((string) ($account->employee_id ?? ''));
        $managerInternalId = $managerEmployeeId !== ''
            ? DB::table('employees')->where('employee_id', $managerEmployeeId)->value('id')
            : null;
        if (!$managerInternalId) {
            return response()->json([
                'success' => false,
                'message' => 'Manager employee record not found.',
            ], 404);
        }

        $type = trim((string) $request->input('notification_type', ''));
        if (!in_array($type, ['analytics', 'leave', 'schedule'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid notification type.',
            ], 422);
        }

        try {
            DB::table('manager_notification_reads')->updateOrInsert(
                [
                    'manager_id' => (int) $managerInternalId,
                    'notification_type' => $type,
                ],
                ['read_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification status saved.',
            ]);
        } catch (Throwable $e) {
            Log::error('Manager notification read update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to save notification status.',
            ], 500);
        }
    }
}
