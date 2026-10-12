<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeCreationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $input = json_decode($request->getContent(), true);

        if (!is_array($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON request.',
            ], 400);
        }

        $employeeId = trim((string) ($input['employee_no'] ?? ''));
        $firstName = trim((string) ($input['first_name'] ?? ''));
        $middleName = trim((string) ($input['middle_name'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');
        $department = trim((string) ($input['department_name'] ?? ''));
        $position = trim((string) ($input['position_name'] ?? ''));
        $hireDate = $input['hire_date'] ?? null;
        $address = trim((string) ($input['address'] ?? ''));
        $employmentType = trim((string) ($input['employment_type'] ?? 'REGULAR'));
        $employmentStatus = strtoupper(
            trim((string) ($input['employment_status'] ?? 'ACTIVE'))
        );

        if (
            $employeeId === '' ||
            $firstName === '' ||
            $lastName === '' ||
            $email === '' ||
            $password === '' ||
            $department === '' ||
            $position === ''
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete all required employee fields.',
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid Gmail address.',
            ], 422);
        }

        if (!preg_match('/@gmail\.com$/i', $email)) {
            return response()->json([
                'success' => false,
                'message' => 'Employee Gmail must use an @gmail.com address.',
            ], 422);
        }

        if (strlen($password) < 8) {
            return response()->json([
                'success' => false,
                'message' => 'Password must be at least 8 characters.',
            ], 422);
        }

        $statusMap = [
            'ACTIVE' => 'active',
            'LEAVE' => 'on_leave',
            'INACTIVE' => 'inactive',
        ];

        if (!isset($statusMap[$employmentStatus])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid employment status.',
            ], 422);
        }

        $dbStatus = $statusMap[$employmentStatus];

        if ($hireDate === '') {
            $hireDate = null;
        }

        if ($hireDate !== null) {
            $date = is_string($hireDate)
                ? \DateTime::createFromFormat('!Y-m-d', $hireDate)
                : false;

            if (
                !$date ||
                $date->format('Y-m-d') !== $hireDate
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hire date.',
                ], 422);
            }
        }

        $accountStatus = $dbStatus === 'active'
            ? 'active'
            : 'inactive';

        try {
            $result = DB::transaction(function () use (
                $employeeId,
                $firstName,
                $middleName,
                $lastName,
                $phone,
                $email,
                $password,
                $department,
                $position,
                $hireDate,
                $address,
                $employmentType,
                $employmentStatus,
                $dbStatus,
                $accountStatus
            ) {
                if (DB::table('employees')
                    ->where('employee_id', $employeeId)
                    ->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee ID already exists.',
                    ], 409);
                }

                if (DB::table('employees')->where('email', $email)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This Gmail address is already assigned to an employee.',
                    ], 409);
                }

                if (DB::table('accounts')->where('email', $email)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This Gmail address already has an account.',
                    ], 409);
                }

                DB::table('employees')->insert([
                    'employee_id' => $employeeId,
                    'first_name' => $firstName,
                    'middle_name' => $middleName !== '' ? $middleName : null,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone !== '' ? $phone : null,
                    'department_name' => $department,
                    'position_name' => $position,
                    'hire_date' => $hireDate,
                    'address' => $address !== '' ? $address : null,
                    'employment_type' => $employmentType !== ''
                        ? $employmentType
                        : 'REGULAR',
                    'employment_status' => $dbStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('accounts')->insert([
                    'employee_id' => $employeeId,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'employee',
                    'status' => $accountStatus,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Employee and employee account created successfully.',
                    'employee' => [
                        'employee_no' => $employeeId,
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => $phone,
                        'department_name' => $department,
                        'position_name' => $position,
                        'hire_date' => $hireDate,
                        'address' => $address,
                        'employment_type' => $employmentType,
                        'employment_status' => $employmentStatus,
                    ],
                    'account' => [
                        'email' => $email,
                        'role' => 'employee',
                        'status' => $accountStatus,
                    ],
                ], 201);
            });

            return $result;
        } catch (Throwable $e) {
            Log::error('Employee creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to create employee and employee account.',
            ], 500);
        }
    }
}
