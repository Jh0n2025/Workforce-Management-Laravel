<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OvertimeApiController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $rows = DB::table('overtime_requests as o')
            ->join(
                'employees as e',
                'e.employee_id',
                '=',
                'o.employee_id'
            )
            ->orderByDesc('o.overtime_date')
            ->orderByDesc('o.id')
            ->get([
                'o.id',
                'o.employee_id',
                'o.overtime_date',
                'o.overtime_hours',
                'o.reason',
                'o.status',
                'e.first_name',
                'e.middle_name',
                'e.last_name',
            ]);

        $pending = 0;
        $approved = 0;
        $totalHours = 0.0;

        $data = $rows->map(function ($row) use (
            &$pending,
            &$approved,
            &$totalHours
        ) {
            $status = strtolower((string) $row->status);
            $hours = (float) $row->overtime_hours;

            if ($status === 'pending') {
                $pending++;
            }

            if ($status === 'approved') {
                $approved++;
            }

            $totalHours += $hours;

            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ], fn ($value) => $value !== null && $value !== '')));

            return [
                'id' => (int) $row->id,
                'employeeId' => (string) $row->employee_id,
                'employee' => $name,
                'date' => (string) $row->overtime_date,
                'hours' => $hours,
                'reason' => $row->reason ?? '',
                'status' => strtoupper($status),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'summary' => [
                'pending' => $pending,
                'approved' => $approved,
                'total_hours' => round($totalHours, 2),
            ],
            'data' => $data,
        ]);
    }
}