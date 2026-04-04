<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id', 'cruise_line_id', 'ship_id', 'area_id',
        'port_from_id', 'port_to_id', 'cruise_name',
        'is_package', 'matchcode', 'sea',
    ];

    protected $casts = [
        'is_package' => 'boolean',
        'sea'        => 'boolean',
    ];

    public function cruiseLine(): BelongsTo
    {
        return $this->belongsTo(CruiseLine::class);
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function portFrom(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_from_id');
    }

    public function portTo(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_to_id');
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class);
    }

    public function itinerary(): HasMany
    {
        return $this->hasMany(ProductItinerary::class)->orderBy('day_number');
    }

    public function scopeFuture($query)
    {
        return $query->whereHas('departures', fn($q) => $q->future());
    }
}
