<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductItinerary extends Model
{
    protected $table = 'product_itinerary';

    protected $fillable = ['product_id', 'port_id', 'day_number', 'arrival_time', 'departure_time'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
