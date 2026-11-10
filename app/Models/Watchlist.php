<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_type', 
        'entity_type', 
        'entity_value', 
        'reason', 
        'added_at'
    ];

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }
}