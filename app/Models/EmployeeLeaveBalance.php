<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    protected $table = 'employee_leave_balances';

    protected $fillable = [
        'employee_id',
        'balance_year',
        'vacation_days',
        'sick_days',
        'emergency_days',
    ];

    protected function casts(): array
    {
        return [
            'balance_year' => 'integer',
            'vacation_days' => 'decimal:2',
            'sick_days' => 'decimal:2',
            'emergency_days' => 'decimal:2',
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
}