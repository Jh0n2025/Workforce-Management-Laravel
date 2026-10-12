<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimesheetReview extends Model
{
    protected $table = 'timesheet_reviews';

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'reviewed_by',
            'id'
        );
    }

    public function history(): HasMany
    {
        return $this->hasMany(
            TimesheetReviewHistory::class,
            'timesheet_review_id',
            'id'
        );
    }
}