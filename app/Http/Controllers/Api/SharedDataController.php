<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharedDataController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
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

        if (!in_array($collection, $allowedCollections, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported collection.',
            ], 422);
        }

        $data = match ($collection) {
            'employees' => DB::table('employees')
                ->orderBy('employee_id')
                ->get(),

            'attendanceRecords' => DB::table('attendance_records')
                ->orderByDesc('attendance_date')
                ->get(),

            'leaveRequests' => DB::table('leave_requests')
                ->orderByDesc('created_at')
                ->get(),

            'shiftRequests' => DB::table('shift_requests')
                ->orderByDesc('created_at')
                ->get(),

            'schedules' => DB::table('schedules')
                ->orderBy('schedule_date')
                ->get(),
        };

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
