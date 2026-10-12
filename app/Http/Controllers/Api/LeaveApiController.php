<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeaveApiController extends Controller
{
    private const ALLOWED_LEAVE_TYPES = [
        'Service Incentive Leave (SIL)',
        'Vacation Leave',
        'Sick Leave',
        'Maternity Leave',
        'Paternity Leave',
    ];

    /** Admin leave registry (preserves the existing API response). */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        try {
            $rows = DB::table('leave_requests as lr')
                ->join('employees as e', 'e.employee_id', '=', 'lr.employee_id')
                ->orderByDesc('lr.created_at')
                ->orderByDesc('lr.id')
                ->get([
                    'lr.id', 'lr.employee_id', 'lr.leave_type', 'lr.start_date',
                    'lr.end_date', 'lr.reason', 'lr.status', 'lr.approved_by',
                    'lr.approved_at', 'lr.created_at', 'e.avatar_url',
                    'e.first_name', 'e.middle_name', 'e.last_name',
                ]);

            $data = $rows->map(function ($row) {
                $name = implode(' ', array_filter([
                    trim((string) ($row->first_name ?? '')),
                    trim((string) ($row->middle_name ?? '')),
                    trim((string) ($row->last_name ?? '')),
                ], fn ($part) => $part !== ''));

                $status = strtolower((string) $row->status);
                $statusMap = [
                    'pending' => 'Pending', 'approved' => 'Approved',
                    'rejected' => 'Declined', 'cancelled' => 'Cancelled',
                ];

                return [
                    'id' => (int) $row->id,
                    'employeeId' => (string) $row->employee_id,
                    'name' => $name,
                    'avatar_url' => $row->avatar_url,
                    'type' => $row->leave_type,
                    'dates' => Carbon::parse($row->start_date)->format('M j, Y')
                        . ' - ' . Carbon::parse($row->end_date)->format('M j, Y'),
                    'reason' => $row->reason ?? '',
                    'status' => $statusMap[$status] ?? ucfirst($status),
                    'startDate' => (string) $row->start_date,
                    'endDate' => (string) $row->end_date,
                ];
            })->values();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::error('Leave list API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load leave requests.'], 500);
        }
    }

    /** Employee or manager submits their own leave request. */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['employee', 'manager'], true)) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json(['success' => false, 'message' => 'Employee session not found.'], 401);
        }

        $input = json_decode($request->getContent(), true);
        if (!is_array($input)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON request.'], 400);
        }

        $leaveType = trim((string) ($input['leave_type'] ?? ''));
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $endDate = trim((string) ($input['end_date'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));

        if (!in_array($leaveType, self::ALLOWED_LEAVE_TYPES, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid leave type.'], 422);
        }
        if ($leaveType === '' || $startDate === '' || $endDate === '') {
            return response()->json(['success' => false, 'message' => 'Leave type, start date, and end date are required.'], 422);
        }
        if (!$this->validDate($startDate) || !$this->validDate($endDate)) {
            return response()->json(['success' => false, 'message' => 'Dates must use YYYY-MM-DD format.'], 422);
        }
        if ($endDate < $startDate) {
            return response()->json(['success' => false, 'message' => 'End date cannot be before start date.'], 422);
        }
        if ($leaveType === 'Service Incentive Leave (SIL)') {
            $employee = DB::table('employees')->where('employee_id', $employeeId)->first(['hire_date']);
            $hireDate = trim((string) ($employee->hire_date ?? ''));
            if ($hireDate === '') {
                return response()->json(['success' => false, 'message' => 'SIL cannot be requested because your hire date is not available.'], 422);
            }
            if ((new DateTime($hireDate))->modify('+1 year')->format('Y-m-d') > $startDate) {
                return response()->json(['success' => false, 'message' => 'Service Incentive Leave (SIL) requires at least 1 year of service.'], 422);
            }
        }

        try {
            return DB::transaction(function () use ($employeeId, $leaveType, $startDate, $endDate, $reason) {
                if (DB::table('leave_requests')->where('employee_id', $employeeId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_date', '<=', $endDate)->where('end_date', '>=', $startDate)->exists()) {
                    return response()->json(['success' => false, 'message' => 'You already have a pending or approved leave request covering part of these dates.'], 409);
                }

                $id = DB::table('leave_requests')->insertGetId([
                    'employee_id' => $employeeId,
                    'leave_type' => $leaveType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'reason' => $reason !== '' ? $reason : null,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json(['success' => true, 'message' => 'Leave request submitted successfully.', 'id' => (int) $id]);
            });
        } catch (Throwable $e) {
            Log::error('Leave create API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to submit leave request.'], 500);
        }
    }

    /** Current employee/manager's own leave requests. */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['employee', 'manager'], true)) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }
        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json(['success' => false, 'message' => 'Employee session not found.'], 401);
        }

        try {
            $requests = DB::table('leave_requests')
                ->where('employee_id', $employeeId)
                ->orderByDesc('created_at')->orderByDesc('id')
                ->get(['id', 'leave_type', 'start_date', 'end_date', 'reason', 'status', 'approved_at', 'created_at']);
            return response()->json(['success' => true, 'requests' => $requests]);
        } catch (Throwable $e) {
            Log::error('My leave requests API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load leave requests.'], 500);
        }
    }

    /** Manager sees leave requests from employees assigned to them. */
    public function teamList(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user?->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Manager access required.'], 403);
        }
        $employeeId = trim((string) ($user->employee_id ?? ''));
        if ($employeeId === '') {
            return response()->json(['success' => false, 'message' => 'Manager employee session not found.'], 401);
        }

        try {
            $manager = DB::table('employees')->where('employee_id', $employeeId)->first(['id']);
            if (!$manager) {
                return response()->json(['success' => false, 'message' => 'Manager employee record not found.'], 404);
            }

            $rows = DB::table('leave_requests as lr')
                ->join('employees as e', 'e.employee_id', '=', 'lr.employee_id')
                ->where('e.manager_id', $manager->id)
                ->orderByDesc('lr.created_at')->orderByDesc('lr.id')
                ->get(['lr.id', 'lr.employee_id', 'lr.leave_type', 'lr.start_date', 'lr.end_date', 'lr.reason', 'lr.status', 'lr.created_at', 'e.first_name', 'e.middle_name', 'e.last_name']);

            $statusMap = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Declined', 'cancelled' => 'Cancelled'];
            $data = $rows->map(function ($row) use ($statusMap) {
                $name = implode(' ', array_filter([
                    trim((string) ($row->first_name ?? '')),
                    trim((string) ($row->middle_name ?? '')),
                    trim((string) ($row->last_name ?? '')),
                ], fn ($part) => $part !== ''));
                $status = strtolower((string) $row->status);
                return [
                    'id' => (int) $row->id,
                    'employeeId' => (string) $row->employee_id,
                    'name' => $name,
                    'type' => $row->leave_type,
                    'dates' => Carbon::parse($row->start_date)->format('M j, Y').' - '.Carbon::parse($row->end_date)->format('M j, Y'),
                    'reason' => $row->reason ?? '',
                    'status' => $statusMap[$status] ?? ucfirst($status),
                    'startDate' => (string) $row->start_date,
                    'endDate' => (string) $row->end_date,
                ];
            })->values();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::error('Manager team leave list API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to load team leave requests.'], 500);
        }
    }

    /** Admin files a leave request for an employee/manager. */
    public function adminCreate(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Administrator access required.'], 403);
        }
        $input = json_decode($request->getContent(), true);
        if (!is_array($input)) {
            return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
        }
        $employeeId = trim((string) ($input['employee_id'] ?? ''));
        $leaveType = trim((string) ($input['leave_type'] ?? ''));
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $endDate = trim((string) ($input['end_date'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));

        if ($employeeId === '' || $leaveType === '' || $startDate === '' || $endDate === '' || $reason === '') {
            return response()->json(['success' => false, 'message' => 'Applicant, leave type, dates, and reason are required.'], 422);
        }
        if (!in_array($leaveType, self::ALLOWED_LEAVE_TYPES, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid leave type.'], 422);
        }
        if (!$this->validDate($startDate) || !$this->validDate($endDate)) {
            return response()->json(['success' => false, 'message' => 'Dates must use YYYY-MM-DD format.'], 422);
        }
        if ($endDate < $startDate) {
            return response()->json(['success' => false, 'message' => 'End date cannot be before start date.'], 422);
        }
        if ($endDate < now()->toDateString()) {
            return response()->json(['success' => false, 'message' => 'Leave dates cannot be completely in the past.'], 422);
        }

        try {
            $employee = DB::table('employees as e')
                ->join('accounts as a', 'a.employee_id', '=', 'e.employee_id')
                ->where('e.employee_id', $employeeId)
                ->whereIn('a.role', ['employee', 'manager'])
                ->orderBy('a.id')
                ->first(['e.employee_id', 'e.hire_date', 'e.employment_status', 'a.role']);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Selected applicant was not found.'], 404);
            }
            if (strtolower((string) $employee->employment_status) === 'terminated') {
                return response()->json(['success' => false, 'message' => 'Terminated employees cannot be given a leave request.'], 422);
            }
            if ($leaveType === 'Service Incentive Leave (SIL)') {
                $hireDate = trim((string) ($employee->hire_date ?? ''));
                if ($hireDate === '') {
                    return response()->json(['success' => false, 'message' => 'SIL cannot be granted because the applicant has no hire date.'], 422);
                }
                if ((new DateTime($hireDate))->modify('+1 year')->format('Y-m-d') > $startDate) {
                    return response()->json(['success' => false, 'message' => 'Service Incentive Leave (SIL) requires at least 1 year of service.'], 422);
                }
            }

            return DB::transaction(function () use ($employeeId, $leaveType, $startDate, $endDate, $reason) {
                if (DB::table('leave_requests')->where('employee_id', $employeeId)->whereIn('status', ['pending', 'approved'])
                    ->where('start_date', '<=', $endDate)->where('end_date', '>=', $startDate)->exists()) {
                    return response()->json(['success' => false, 'message' => 'This applicant already has a pending or approved leave covering part of these dates.'], 409);
                }
                $id = DB::table('leave_requests')->insertGetId([
                    'employee_id' => $employeeId, 'leave_type' => $leaveType,
                    'start_date' => $startDate, 'end_date' => $endDate,
                    'reason' => $reason, 'status' => 'pending',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                return response()->json(['success' => true, 'message' => 'Leave request filed successfully.', 'id' => (int) $id]);
            });
        } catch (Throwable $e) {
            Log::error('Admin leave creation API failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to file leave request.'], 500);
        }
    }

    /** Admin updates any pending leave request. */
    public function updateStatus(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Administrator access required.'], 403);
        }
        return $this->performStatusUpdate($request, false);
    }

    /** Manager updates a pending leave request belonging to their own team only. */
    public function managerUpdateStatus(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Manager access required.'], 403);
        }
        return $this->performStatusUpdate($request, true);
    }

    private function performStatusUpdate(Request $request, bool $managerOnly): JsonResponse
    {
        $input = json_decode($request->getContent(), true);
        if (!is_array($input)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON request.'], 400);
        }
        $id = (int) ($input['id'] ?? 0);
        $action = strtolower(trim((string) ($input['action'] ?? '')));
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid leave request ID.'], 422);
        }
        $statusMap = ['approve' => 'approved', 'decline' => 'rejected'];
        if (!isset($statusMap[$action])) {
            return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
        }

        $user = $request->user();
        $accountId = (int) ($user?->id ?? 0);
        if ($accountId <= 0) {
            return response()->json(['success' => false, 'message' => ($managerOnly ? 'Manager' : 'Admin').' session not found.'], 401);
        }

        try {
            $query = DB::table('leave_requests as lr')->where('lr.id', $id)->where('lr.status', 'pending');
            if ($managerOnly) {
                $managerEmployeeId = trim((string) ($user->employee_id ?? ''));
                if ($managerEmployeeId === '') {
                    return response()->json(['success' => false, 'message' => 'Manager session not found.'], 401);
                }
                $manager = DB::table('employees')->where('employee_id', $managerEmployeeId)->first(['id']);
                if (!$manager) {
                    return response()->json(['success' => false, 'message' => 'Manager employee record not found.'], 404);
                }
                // Use a PostgreSQL-compatible subquery instead of a joined UPDATE.
                $teamEmployeeIds = DB::table('employees')
                    ->select('employee_id')
                    ->where('manager_id', $manager->id);
                $query->whereIn('lr.employee_id', $teamEmployeeIds);
            }

            $updated = $query->update([
                'status' => $statusMap[$action],
                'approved_by' => $accountId,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
            if ($updated === 0) {
                return response()->json(['success' => false, 'message' => $managerOnly
                    ? 'Leave request was not found, is already processed, or does not belong to your team.'
                    : 'Leave request was not found or was already processed.'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Leave request updated successfully.']);
        } catch (Throwable $e) {
            Log::error(($managerOnly ? 'Manager' : 'Admin').' leave status update failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to update leave request.'], 500);
        }
    }

    private function validDate(string $value): bool
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
