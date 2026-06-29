<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $appends = [
        'before_url',
        'after_url',
    ];
    protected $with = [
        'employee.profile.division',
        'approver.profile',
        'category',
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
        'start',
        'end',
        'category_id',
        'employee_id',
        'status',
        'submitted_at',
        'description',
        'before',
        'after',
        'approved_by',
        'comment',
    ];

    protected static function booted(): void
    {
        static::updated(function (Overtime $overtime) {
            if ($overtime->wasChanged('status')) {
                \App\Events\OvertimeStatusUpdated::dispatch($overtime);
            }
        });
        static::created(function (Overtime $overtime) {
            \App\Events\OvertimeCreated::dispatch($overtime);
        });
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
