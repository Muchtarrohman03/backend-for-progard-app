<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
}
