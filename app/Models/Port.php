<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Port extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['id', 'name', 'latitude', 'longitude', 'country_code'];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function productsFrom(): HasMany
    {
        return $this->hasMany(Product::class, 'port_from_id');
    }

    public function productsTo(): HasMany
    {
        return $this->hasMany(Product::class, 'port_to_id');
    }

    public function itineraryStops(): HasMany
    {
        return $this->hasMany(ProductItinerary::class);
    }
}
