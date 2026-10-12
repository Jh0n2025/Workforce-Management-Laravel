<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RfidAssignmentController extends Controller
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

        try {
            $rows = DB::table('rfid_cards as r')
                ->join(
                    'employees as e',
                    'e.employee_id',
                    '=',
                    'r.employee_id'
                )
                ->leftJoin(
                    'accounts as a',
                    'a.employee_id',
                    '=',
                    'e.employee_id'
                )
                ->orderByDesc('r.created_at')
                ->orderByDesc('r.id')
                ->get([
                    'r.id',
                    'r.employee_id',
                    'r.rfid_uid',
                    'r.status',
                    'r.created_at',
                    DB::raw("
                        TRIM(CONCAT_WS(
                            ' ',
                            e.first_name,
                            e.middle_name,
                            e.last_name
                        )) AS employee_name
                    "),
                    DB::raw("COALESCE(a.role, 'employee') AS account_role"),
                ]);

            $data = $rows->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'employee_id' => (string) $row->employee_id,
                    'employee_name' => trim((string) $row->employee_name),
                    'account_role' => strtolower((string) $row->account_role),
                    'rfid_uid' => (string) $row->rfid_uid,
                    'status' => (string) $row->status,
                    'created_at' => (string) $row->created_at,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            Log::error('RFID assignment list failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to load RFID assignments.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->isAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        $employeeId = trim((string) $request->input('employee_id', ''));
        $rfidUid = trim((string) $request->input('rfid_uid', ''));

        if ($employeeId === '' || $rfidUid === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID and RFID UID are required.',
            ], 422);
        }

        if (strlen($rfidUid) > 100) {
            return response()->json([
                'success' => false,
                'message' => 'RFID UID is too long.',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use (
                $employeeId,
                $rfidUid
            ) {
                $employee = DB::table('employees as e')
                    ->leftJoin(
                        'accounts as a',
                        'a.employee_id',
                        '=',
                        'e.employee_id'
                    )
                    ->where('e.employee_id', $employeeId)
                    ->where(function ($query) {
                        $query->where('a.role', 'employee')
                            ->orWhereNull('a.role');
                    })
                    ->lockForUpdate()
                    ->first([
                        'e.employee_id',
                        'e.first_name',
                        'e.middle_name',
                        'e.last_name',
                        'e.employment_status',
                    ]);

                if (!$employee) {
                    return [
                        'status' => 404,
                        'body' => [
                            'success' => false,
                            'message' => 'Employee was not found.',
                        ],
                    ];
                }

                if ($employee->employment_status !== 'active') {
                    return [
                        'status' => 422,
                        'body' => [
                            'success' => false,
                            'message' => 'Only active employees can be assigned to a kiosk RFID.',
                        ],
                    ];
                }

                $existingEmployeeCard = DB::table('rfid_cards')
                    ->where('employee_id', $employeeId)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first(['id', 'rfid_uid']);

                if ($existingEmployeeCard) {
                    return [
                        'status' => 409,
                        'body' => [
                            'success' => false,
                            'message' => 'This employee already has an active RFID assigned.',
                            'existing_rfid_uid' => (string) $existingEmployeeCard->rfid_uid,
                        ],
                    ];
                }

                $existingRfid = DB::table('rfid_cards')
                    ->where('rfid_uid', $rfidUid)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first(['id', 'employee_id']);

                if ($existingRfid) {
                    return [
                        'status' => 409,
                        'body' => [
                            'success' => false,
                            'message' => 'This RFID UID is already assigned to another employee.',
                        ],
                    ];
                }

                $assignmentId = DB::table('rfid_cards')->insertGetId([
                    'employee_id' => $employeeId,
                    'rfid_uid' => $rfidUid,
                    'status' => 'active',
                    'created_at' => now(),
                ], 'id');

                $employeeName = trim(implode(' ', array_filter([
                    $employee->first_name,
                    $employee->middle_name,
                    $employee->last_name,
                ], fn ($value) => $value !== null && $value !== '')));

                return [
                    'status' => 200,
                    'body' => [
                        'success' => true,
                        'message' => 'Employee ID successfully assigned to RFID.',
                        'data' => [
                            'id' => (int) $assignmentId,
                            'employee_id' => (string) $employeeId,
                            'employee_name' => $employeeName,
                            'rfid_uid' => $rfidUid,
                            'status' => 'active',
                        ],
                    ],
                ];
            });

            return response()->json($result['body'], $result['status']);
        } catch (Throwable $e) {
            Log::error('RFID assignment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to assign RFID to employee.',
            ], 500);
        }
    }
}