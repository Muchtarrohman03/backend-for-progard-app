<?php

namespace App\Models;

use App\Events\JobSubmissionStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use \App\Events\JobSubmissionCreated;

class JobSubmission extends Model
{

    protected $appends = [
        'before_url',
        'after_url',
    ];
    public function getBeforeUrlAttribute()
    {
        return $this->before
            ? asset('storage/' . $this->before)
            : null;
    }

    public function getAfterUrlAttribute()
    {
        return $this->after
            ? asset('storage/' . $this->after)
            : null;
    }


    protected $fillable = [
        'category_id',
        'employee_id',
        'submitted_at',
        'status',
        'before',
        'after',
        'approved_by',
        'comment',
    ];

    protected $with = [
        'employee.profile',
        'approver.profile',
        'category',
    ];

    // ✅ Otomatis dispatch event saat model di-update
    protected static function booted(): void
    {
        static::updated(function (JobSubmission $submission) {
            if ($submission->wasChanged('status')) {
                JobSubmissionStatusUpdated::dispatch($submission);
            }
        });

        static::created(function (JobSubmission $submission) {
            JobSubmissionCreated::dispatch($submission);
        });
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
