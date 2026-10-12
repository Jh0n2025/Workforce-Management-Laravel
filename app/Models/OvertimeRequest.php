<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $table = 'overtime_requests';

    protected $fillable = [
        'employee_id',
        'attendance_record_id',
        'overtime_date',
        'overtime_hours',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'overtime_hours' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id',
            'employee_id'
        );
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceRecord::class,
            'attendance_record_id',
            'id'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'reviewed_by',
            'id'
        );
    }
}