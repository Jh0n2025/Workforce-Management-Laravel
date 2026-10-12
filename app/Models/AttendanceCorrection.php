<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    protected $table = 'attendance_corrections';

    protected $fillable = [
        'employee_id',
        'attendance_record_id',
        'correction_date',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'attachment',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'correction_date' => 'date',
            'requested_clock_in' => 'datetime',
            'requested_clock_out' => 'datetime',
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