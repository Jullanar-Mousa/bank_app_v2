<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulationScenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'risk_level',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}