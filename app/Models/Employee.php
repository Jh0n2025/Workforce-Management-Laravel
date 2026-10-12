<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'avatar_url',
        'date_of_birth',
        'gender',
        'email',
        'phone',
        'department_name',
        'position_name',
        'hire_date',
        'address',
        'emergency_name',
        'emergency_relationship',
        'emergency_phone',
        'employment_type',
        'employment_status',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function account(): HasOne
    {
        return $this->hasOne(
            Account::class,
            'employee_id',
            'employee_id'
        );
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'manager_id',
            'id'
        );
    }
}