<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRequest extends Model
{
    protected $table = 'shift_requests';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'requested_date',
        'requested_shift',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'reviewed_by',
            'id'
        );
    }
}