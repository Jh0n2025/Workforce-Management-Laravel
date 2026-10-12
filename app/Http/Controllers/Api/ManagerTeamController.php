<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ManagerTeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user?->role !== 'manager') {
            return response()->json([
                'success' => false,
                'message' => 'Manager access required.',
            ], 403);
        }

        $managerEmployeeId = trim(
            (string) ($user->employee_id ?? '')
        );

        if ($managerEmployeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Manager session not found.',
            ], 401);
        }

        try {
            $manager = DB::table('employees')
                ->where('employee_id', $managerEmployeeId)
                ->first(['id']);

            if (!$manager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager employee record not found.',
                ], 404);
            }

            $employees = DB::table('employees')
                ->where('manager_id', $manager->id)
                ->where('employment_status', '<>', 'terminated')
                ->orderBy('employee_id')
                ->get([
                    'employee_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'position_name',
                    'department_name',
                    'employment_status',
                ])
                ->map(function ($row) {
                    $name = implode(' ', array_filter([
                        trim((string) ($row->first_name ?? '')),
                        trim((string) ($row->middle_name ?? '')),
                        trim((string) ($row->last_name ?? '')),
                    ], fn ($part) => $part !== ''));

                    return [
                        'id' => (string) $row->employee_id,
                        'name' => $name,
                        'position' => (string) ($row->position_name ?? ''),
                        'department' => (string) ($row->department_name ?? ''),
                        'status' => (string) ($row->employment_status ?? ''),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $employees,
            ]);
        } catch (Throwable $e) {
            Log::error('Manager team API failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to load manager team.',
            ], 500);
        }
    }
}