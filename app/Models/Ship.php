<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ship extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id', 'cruise_line_id', 'name', 'description',
        'image_url', 'features', 'decks', 'images_refreshed_at',
    ];

    protected $casts = [
        'features'            => 'array',
        'decks'               => 'array',
        'images_refreshed_at' => 'datetime',
    ];

    public function cruiseLine(): BelongsTo
    {
        return $this->belongsTo(CruiseLine::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function cabinImages(): HasMany
    {
        return $this->hasMany(ShipCabinImage::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ShipCategory::class);
    }
}
