<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'Employee access required.',
            ], 403);
        }

        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        try {
            $search = '%' . $query . '%';

            $rows = DB::table('employees')
                ->select([
                    'employee_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'department_name',
                    'position_name',
                    'employment_status',
                ])
                ->where(function ($builder) use ($search) {
                    $builder
                        ->where('employee_id', 'ILIKE', $search)
                        ->orWhere('first_name', 'ILIKE', $search)
                        ->orWhere('middle_name', 'ILIKE', $search)
                        ->orWhere('last_name', 'ILIKE', $search)
                        ->orWhere('department_name', 'ILIKE', $search)
                        ->orWhere('position_name', 'ILIKE', $search);
                })
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->limit(10)
                ->get();

            $results = $rows->map(function ($row) {
                $name = implode(' ', array_filter([
                    trim((string) ($row->first_name ?? '')),
                    trim((string) ($row->middle_name ?? '')),
                    trim((string) ($row->last_name ?? '')),
                ], fn ($part) => $part !== ''));

                return [
                    'employee_id' => $row->employee_id,
                    'name' => $name,
                    'department' => $row->department_name ?? '',
                    'position' => $row->position_name ?? '',
                    'status' => $row->employment_status ?? '',
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (Throwable $e) {
            Log::error('Employee search API failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to search employees.',
            ], 500);
        }
    }
}
