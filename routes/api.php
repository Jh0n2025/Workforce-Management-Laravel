<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\CriticalAlertController;
use App\Http\Controllers\Api\OvertimeApiController;
use App\Http\Controllers\Api\RfidAssignmentController;
use App\Http\Controllers\Api\ManagerAssignmentController;
use App\Http\Controllers\Api\EmployeeProfileController;
use App\Http\Controllers\Api\TimesheetApprovalController;
use App\Http\Controllers\Api\ScheduleApiController;
use App\Http\Controllers\Api\SharedDataController;
use App\Http\Controllers\Api\KioskApiController;
use App\Http\Controllers\Api\AnalyticsApiController;
use App\Http\Controllers\Api\EmployeeCreationController;
use App\Http\Controllers\Api\EmployeeSearchController;
use App\Http\Controllers\Api\ManagerTeamController;
use App\Http\Controllers\Api\ScheduleShiftRequestController;
use App\Http\Controllers\Api\ManagerWorkforceController;
use App\Http\Controllers\Api\EmployeeTimesheetGrowthController;
use App\Http\Controllers\Api\AccountProfileController;
use App\Http\Controllers\Api\RfidAuthController;

Route::post('/auth/login', [
    AuthController::class,
    'login',
]);
Route::post('/auth/verify-otp', [
    AuthController::class,
    'verifyOtp',
]);
Route::post('/auth/resend-otp', [
    AuthController::class,
    'resendOtp',
]);
// Legacy-compatible authentication endpoints.
Route::post('/auth/login.php', [
    AuthController::class,
    'login',
]);

Route::post('/auth/verify-otp.php', [
    AuthController::class,
    'verifyOtp',
]);

Route::post('/auth/resend-otp.php', [
    AuthController::class,
    'resendOtp',
]);

Route::post('/auth/logout.php', function (Request $request) {
    \Illuminate\Support\Facades\Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully.',
    ]);
})->middleware('auth:sanctum');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/dashboard/admin.php', [
    DashboardController::class,
    'admin',
])->middleware('auth:sanctum');
Route::get('/employees/list.php', [
    EmployeeApiController::class,
    'index',
])->middleware('auth:sanctum');
Route::get('/employees/roster.php', [
    EmployeeApiController::class,
    'roster',
])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attendance/today.php', [
        AttendanceApiController::class,
        'today',
    ]);
    Route::get('/attendance/records.php', [
        AttendanceApiController::class,
        'records',
    ]);
    Route::get('/attendance/corrections.php', [
        AttendanceApiController::class,
        'corrections',
    ]);
});
Route::get('/leave/list.php', [
    LeaveApiController::class,
    'index',
])->middleware('auth:sanctum');
Route::post('/leave/create.php', [
    LeaveApiController::class,
    'create',
])->middleware('auth:sanctum');

Route::get('/leave/my-requests.php', [
    LeaveApiController::class,
    'myRequests',
])->middleware('auth:sanctum');

Route::get('/leave/team-list.php', [
    LeaveApiController::class,
    'teamList',
])->middleware('auth:sanctum');

Route::post('/leave/admin-create.php', [
    LeaveApiController::class,
    'adminCreate',
])->middleware('auth:sanctum');

Route::post('/leave/update-status.php', [
    LeaveApiController::class,
    'updateStatus',
])->middleware('auth:sanctum');

Route::post('/leave/manager-update-status.php', [
    LeaveApiController::class,
    'managerUpdateStatus',
])->middleware('auth:sanctum');
Route::get('/overview/critical-alerts.php', [
    CriticalAlertController::class,
    'index',
])->middleware('auth:sanctum');
Route::get('/overtime/admin.php', [
    OvertimeApiController::class,
    'admin',
])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/kiosk/rfid-assignments.php', [
        RfidAssignmentController::class,
        'index',
    ]);
    Route::post('/kiosk/rfid-assignments.php', [
        RfidAssignmentController::class,
        'store',
    ]);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employees/assign-manager.php', [
        ManagerAssignmentController::class,
        'index',
    ]);
    Route::post('/employees/assign-manager.php', [
        ManagerAssignmentController::class,
        'store',
    ]);
});
Route::get('/employees/profile.php', [
    EmployeeProfileController::class,
    'show',
])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/timesheet-approvals.php', [
        TimesheetApprovalController::class,
        'index',
    ]);

    Route::post('/timesheet-approvals.php', [
        TimesheetApprovalController::class,
        'store',
    ]);
});
Route::match(['get', 'post'], '/schedules/admin.php', [
    ScheduleApiController::class,
    'admin',
])->middleware('auth:sanctum');

Route::match(['get', 'post'], '/schedules/manager.php', [
    ScheduleShiftRequestController::class,
    'managerSchedule',
])->middleware('auth:sanctum');

Route::match(['get', 'post'], '/shift-requests/my.php', [
    ScheduleShiftRequestController::class,
    'myShiftRequests',
])->middleware('auth:sanctum');

Route::match(['get', 'post'], '/shift-requests/manager.php', [
    ScheduleShiftRequestController::class,
    'managerShiftRequests',
])->middleware('auth:sanctum');
Route::get('/shared-data.php', [SharedDataController::class, 'index'])
    ->middleware('auth:sanctum');
    Route::get('/kiosk/logs.php', [KioskApiController::class, 'logs'])
    ->middleware('auth:sanctum');
    Route::get('/analytics/admin.php', [AnalyticsApiController::class, 'admin'])
    ->middleware('auth:sanctum');
    Route::post('/employees/create.php', [
    EmployeeCreationController::class,
    'store',
])->middleware('auth:sanctum');
Route::get('/employees/search.php', [
    EmployeeSearchController::class,
    'index',
])->middleware('auth:sanctum');
Route::get('/employees/manager-team.php', [
    ManagerTeamController::class,
    'index',
])->middleware('auth:sanctum');

// Employee attendance actions and history.
Route::middleware('auth:sanctum')->group(function () {
    Route::match(['get', 'post'], '/attendance/my-clock.php', [AttendanceApiController::class, 'myClock']);
    Route::get('/attendance/my-records.php', [AttendanceApiController::class, 'myRecords']);
});

Route::get('/timesheet/my.php', [
    EmployeeTimesheetGrowthController::class,
    'myTimesheet',
])->middleware('auth:sanctum');

Route::get('/timesheet/manager.php', [
    EmployeeTimesheetGrowthController::class,
    'managerTimesheets',
])->middleware('auth:sanctum');

Route::get('/growth/my.php', [
    EmployeeTimesheetGrowthController::class,
    'myGrowth',
])->middleware('auth:sanctum');
Route::match(['get', 'post'], '/account/profile.php', [
    AccountProfileController::class,
    'profile',
])->middleware('auth:sanctum');

Route::post('/account/profile-photo.php', [
    AccountProfileController::class,
    'photo',
])->middleware('auth:sanctum');
Route::post('/auth/rfid-login.php', [
    RfidAuthController::class,
    'login',
]);

Route::post('/employees/verify-admin-rfid.php', [
    RfidAuthController::class,
    'verifyAdminRfid',
])->middleware('auth:sanctum');
Route::get('/schedules/my.php', [
    ScheduleShiftRequestController::class,
    'mySchedule',
])->middleware('auth:sanctum');