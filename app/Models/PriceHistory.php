<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    public $timestamps = false;

    protected $table = 'price_history';

    protected $fillable = ['departure_id', 'category_code', 'price', 'currency', 'recorded_at', 'source'];

    protected $casts = [
        'price'       => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }
}
