<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriticalAlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $today = now()->toDateString();

        $alerts = DB::table('schedules as s')
            ->join(
                'employees as e',
                'e.employee_id',
                '=',
                's.employee_id'
            )
            ->join(
                'accounts as a',
                'a.employee_id',
                '=',
                'e.employee_id'
            )
            ->join(
                'leave_requests as lr',
                'lr.employee_id',
                '=',
                'e.employee_id'
            )
            ->where('a.role', 'manager')
            ->where('a.status', 'active')
            ->where('e.employment_status', '<>', 'terminated')
            ->where('lr.leave_type', 'Sick Leave')
            ->whereIn('lr.status', ['pending', 'approved'])
            ->where('s.status', 'scheduled')
            ->whereDate('s.schedule_date', '>=', $today)
            ->whereColumn('s.schedule_date', '>=', 'lr.start_date')
            ->whereColumn('s.schedule_date', '<=', 'lr.end_date')
            ->orderBy('s.schedule_date')
            ->orderBy('s.start_time')
            ->orderBy('e.employee_id')
            ->get([
                's.id as schedule_id',
                'lr.id as leave_id',
                's.employee_id',
                's.schedule_date',
                's.shift_name',
                's.start_time',
                's.end_time',
                's.work_type',
                's.remarks',
                'lr.start_date as leave_start_date',
                'lr.end_date as leave_end_date',
                'lr.status as leave_status',
                DB::raw("
                    TRIM(CONCAT_WS(
                        ' ',
                        e.first_name,
                        e.middle_name,
                        e.last_name
                    )) AS employee_name
                "),
                'e.position_name',
            ]);

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }
}