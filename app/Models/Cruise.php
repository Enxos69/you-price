<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cruise extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship', 'cruise', 'duration', 'from', 'to', 'details',
        'line', 'night', 'partenza', 'arrivo',
        'interior', 'oceanview', 'balcony', 'minisuite', 'suite'
    ];

    protected $casts = [
        'partenza' => 'date',
        'arrivo' => 'date',
    ];
}
