<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $rows = DB::table('employees as e')
            ->leftJoin(
                'accounts as a',
                'a.employee_id',
                '=',
                'e.employee_id'
            )
            ->where(function ($query) {
                $query->where('a.role', 'employee')
                    ->orWhereNull('a.role');
            })
            ->orderBy('e.employee_id')
            ->get([
                'e.employee_id',
                'e.first_name',
                'e.middle_name',
                'e.last_name',
                'e.email',
                'e.phone',
                'e.department_name',
                'e.position_name',
                'e.hire_date',
                'e.address',
                'e.employment_type',
                'e.employment_status',
                'a.email as account_email',
                'a.role as account_role',
                'a.status as account_status',
            ]);

        $employees = $rows->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ])));

            $status = match ($row->employment_status) {
                'active' => 'ACTIVE',
                'on_leave' => 'LEAVE',
                'inactive', 'terminated' => 'INACTIVE',
                default => 'INACTIVE',
            };

            return [
                'id' => (string) $row->employee_id,
                'name' => $name,
                'email' => $row->email,
                'phone' => $row->phone,
                'position' => $row->position_name,
                'department' => strtoupper(
                    (string) $row->department_name
                ),
                'hireDate' => $row->hire_date,
                'address' => $row->address,
                'employmentType' => $row->employment_type,
                'status' => $status,
                'accountEmail' => $row->account_email,
                'accountRole' => $row->account_role,
                'accountStatus' => $row->account_status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    public function roster(Request $request): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $rows = DB::table('employees as e')
            ->leftJoin(
                'accounts as a',
                'a.employee_id',
                '=',
                'e.employee_id'
            )
            ->where('e.employment_status', '<>', 'terminated')
            ->orderByRaw("
                CASE
                    WHEN a.role = 'manager' THEN 0
                    WHEN a.role = 'employee' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('e.employee_id')
            ->get([
                'e.employee_id',
                'e.first_name',
                'e.middle_name',
                'e.last_name',
                'e.avatar_url',
                'e.position_name',
                'e.department_name',
                'e.employment_status',
                'a.role as account_role',
            ]);

        $data = $rows->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ])));

            $accountRole = strtolower(
                (string) ($row->account_role ?? '')
            );

            $position = trim((string) ($row->position_name ?? ''));
            $department = trim((string) ($row->department_name ?? ''));

            return [
                'id' => (string) $row->employee_id,
                'name' => $name,
                'role' => $accountRole === 'manager'
                    ? 'Manager'
                    : ($position !== '' ? $position : 'Employee'),
                'accountRole' => $accountRole,
                'department' => $department,
                'status' => (string) $row->employment_status,
                'avatar_url' => $row->avatar_url,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}