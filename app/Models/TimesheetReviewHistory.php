<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetReviewHistory extends Model
{
    protected $table = 'timesheet_review_history';

    public $timestamps = false;

    protected $fillable = [
        'timesheet_review_id',
        'employee_id',
        'period_start',
        'period_end',
        'previous_status',
        'new_status',
        'action',
        'reviewed_by',
        'reviewed_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function timesheetReview(): BelongsTo
    {
        return $this->belongsTo(
            TimesheetReview::class,
            'timesheet_review_id',
            'id'
        );
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