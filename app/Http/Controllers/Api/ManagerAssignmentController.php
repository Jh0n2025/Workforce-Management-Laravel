<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerAssignmentController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === 'admin';
    }

    public function index(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $employees = DB::table('employees as e')
            ->leftJoin(
                'accounts as a',
                'a.employee_id',
                '=',
                'e.employee_id'
            )
            ->where('e.employment_status', '<>', 'terminated')
            ->where(function ($query) {
                $query->where('a.role', 'employee')
                    ->orWhereNull('a.role');
            })
            ->orderBy('e.first_name')
            ->orderBy('e.last_name')
            ->get([
                'e.employee_id',
                DB::raw("
                    TRIM(CONCAT_WS(
                        ' ',
                        e.first_name,
                        e.middle_name,
                        e.last_name
                    )) AS employee_name
                "),
                'e.manager_id',
            ]);

        $managers = DB::table('employees as e')
            ->join(
                'accounts as a',
                'a.employee_id',
                '=',
                'e.employee_id'
            )
            ->where('a.role', 'manager')
            ->where('e.employment_status', '<>', 'terminated')
            ->orderBy('e.first_name')
            ->orderBy('e.last_name')
            ->get([
                'e.id as manager_id',
                'e.employee_id',
                DB::raw("
                    TRIM(CONCAT_WS(
                        ' ',
                        e.first_name,
                        e.middle_name,
                        e.last_name
                    )) AS manager_name
                "),
                'e.department_name',
                'e.avatar_url',
            ]);

        return response()->json([
            'success' => true,
            'message' => '',
            'employees' => $employees,
            'managers' => $managers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $employeeId = trim(
            (string) $request->input('employee_id', '')
        );

        $managerId = (int) $request->input('manager_id', 0);

        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee is required.',
            ], 422);
        }

        if ($managerId < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid manager selection.',
            ], 422);
        }

        return DB::transaction(function () use (
            $employeeId,
            $managerId
        ) {
            $employee = DB::table('employees')
                ->where('employee_id', $employeeId)
                ->where('employment_status', '<>', 'terminated')
                ->lockForUpdate()
                ->first(['id']);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            if ($managerId > 0) {
                $manager = DB::table('employees as e')
                    ->join(
                        'accounts as a',
                        'a.employee_id',
                        '=',
                        'e.employee_id'
                    )
                    ->where('e.id', $managerId)
                    ->where('a.role', 'manager')
                    ->where(
                        'e.employment_status',
                        '<>',
                        'terminated'
                    )
                    ->lockForUpdate()
                    ->first(['e.id']);

                if (!$manager) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected manager was not found.',
                    ], 404);
                }
            }

            DB::table('employees')
                ->where('employee_id', $employeeId)
                ->update([
                    'manager_id' => $managerId > 0
                        ? $managerId
                        : null,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => $managerId > 0
                    ? 'Employee assigned to manager successfully.'
                    : 'Employee manager assignment removed.',
            ]);
        });
    }
}