<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
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
        'image_path',
    ];

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function getImageFullPathAttribute()
    {
        if (! $this->image_path) {
            return null;
        }

        return storage_path('app/public/' . $this->image_path);
    }
}
