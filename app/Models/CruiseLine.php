<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CruiseLine extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['id', 'name', 'logo_url', 'is_online', 'synced_at'];

    protected $casts = [
        'is_online' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
