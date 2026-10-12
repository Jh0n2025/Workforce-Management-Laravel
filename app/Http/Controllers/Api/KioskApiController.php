<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class KioskApiController extends Controller
{
    public function logs(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required.',
            ], 403);
        }

        try {
            $logs = DB::table('kiosk_log_entries as k')
                ->join(
                    'employees as e',
                    'e.employee_id',
                    '=',
                    'k.employee_id'
                )
                ->select([
                    'k.id',
                    'k.employee_id',
                    'k.rfid_uid',
                    'k.kiosk_name',
                    'k.scan_status',
                    'k.scanned_at',
                ])
                ->selectRaw("
                    TRIM(CONCAT_WS(
                        ' ',
                        e.first_name,
                        NULLIF(e.middle_name, ''),
                        e.last_name
                    )) AS employee_name
                ")
                ->orderByDesc('k.scanned_at')
                ->orderByDesc('k.id')
                ->get()
                ->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'employeeId' => (string) $row->employee_id,
                    'employee' => (string) $row->employee_name,
                    'rfidUid' => (string) ($row->rfid_uid ?? ''),
                    'kioskName' => (string) ($row->kiosk_name ?? ''),
                    'scanStatus' => strtoupper(
                        (string) ($row->scan_status ?? '')
                    ),
                    'scannedAt' => (string) ($row->scanned_at ?? ''),
                ])
                ->values();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (Throwable $e) {
            Log::error('Kiosk log API failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to load kiosk log entries.',
            ], 500);
        }
    }
}