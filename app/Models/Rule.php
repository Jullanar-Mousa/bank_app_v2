<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_name', 
        'description', 
        'weight', 
        'priority', 
        'is_active',
        'conditions',
        'action'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight' => 'integer',
            'priority' => 'integer',
            'conditions' => 'array',
        ];
    }
}