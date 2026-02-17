<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
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
        'start',
        'end',
        'category_id',
        'employee_id',
        'status',
        'submitted_at',
        'description',
        'before',
        'after',
    ];

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
