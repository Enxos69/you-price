<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipCabinImage extends Model
{
    protected $fillable = ['ship_id', 'category_code', 'image_url', 'gallery_name'];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
