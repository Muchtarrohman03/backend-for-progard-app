<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSubmission extends Model
{
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }
    protected $fillable = [
        'category_id',
        'employee_id',
        'submitted_at',
        'status',
        'image_path',
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
