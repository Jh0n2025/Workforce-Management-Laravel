<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        $account = $request->user();

        if (!$account || $account->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $today = Carbon::today();
        $todayValue = $today->toDateString();
        $weekStart = $today->copy()->startOfWeek();
        $weekStartValue = $weekStart->toDateString();

        $totalEmployees = DB::table('employees')
            ->where('employment_status', 'active')
            ->count();

        $clockedIn = DB::table('attendance_records as a')
            ->join('employees as e', 'e.employee_id', '=', 'a.employee_id')
            ->whereDate('a.attendance_date', $todayValue)
            ->whereNotNull('a.time_in')
            ->whereNull('a.time_out')
            ->where('e.employment_status', 'active')
            ->where('a.status', '<>', 'on_leave')
            ->count();

        $lateThisWeek = DB::table('attendance_records as a')
            ->join('employees as e', 'e.employee_id', '=', 'a.employee_id')
            ->whereBetween('a.attendance_date', [
                $weekStartValue,
                $todayValue,
            ])
            ->where('a.status', 'late')
            ->where('e.employment_status', 'active')
            ->count();

        $onLeaveToday = DB::table('leave_requests as lr')
            ->join('employees as e', 'e.employee_id', '=', 'lr.employee_id')
            ->where('lr.status', 'approved')
            ->where('lr.start_date', '<=', $todayValue)
            ->where('lr.end_date', '>=', $todayValue)
            ->where('e.employment_status', '<>', 'terminated')
            ->distinct()
            ->count('lr.employee_id');

        $pendingLeaves = DB::table('leave_requests')
            ->where('status', 'pending')
            ->count();

        $pendingTimesheets = DB::table('timesheet_reviews')
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_employees' => $totalEmployees,
                'clocked_in' => $clockedIn,
                'active_tours' => 0,
                'late_this_week' => $lateThisWeek,
                'on_leave_today' => $onLeaveToday,
                'pending_leaves' => $pendingLeaves,
                'pending_timesheets' => $pendingTimesheets,
            ],
        ]);
    }
}