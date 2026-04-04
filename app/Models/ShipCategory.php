<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipCategory extends Model
{
    protected $fillable = ['ship_id', 'cl_cat', 'cruisehost_cat', 'description'];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
