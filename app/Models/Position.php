<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'employee_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'speed' => 'float',
        'heading' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    private function getLatestPositions($query)
    {
        $positions = Position::whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('positions')
                ->groupBy('employee_id');
        })
            ->with([
                'employee:id,name,division',
                'employee.roles:id,name'
            ])
            ->get();

        return $positions->map(function ($position) {
            return [
                'employee_id' => $position->employee_id,
                'name' => $position->employee->name ?? null,
                'role' => $position->employee->roles->pluck('name')->first(),
                'division' => $position->employee->division ?? null,
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
            ];
        });
    }
}
