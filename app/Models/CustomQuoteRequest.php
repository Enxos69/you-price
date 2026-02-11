<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomQuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_range',
        'budget',
        'participants',
        'port_start',
        'notes',
        'phone',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
