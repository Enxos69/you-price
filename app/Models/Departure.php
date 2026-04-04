<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departure extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['id', 'product_id', 'dep_date', 'arr_date', 'duration', 'min_price'];

    protected $casts = [
        'dep_date'  => 'date',
        'arr_date'  => 'date',
        'duration'  => 'integer',
        'min_price' => 'float',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Ultimo snapshot prezzi per ciascuna categoria cabina.
     * Usa MAX(id) come proxy per l'ultima registrazione.
     */
    public function latestPrices(): HasMany
    {
        return $this->hasMany(PriceHistory::class)
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('price_history')
                    ->groupBy('departure_id', 'category_code');
            });
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getMinPriceAttribute(): ?float
    {
        // Usa la colonna denormalizzata se disponibile (fast path)
        if (array_key_exists('min_price', $this->attributes) && $this->attributes['min_price'] !== null) {
            return (float) $this->attributes['min_price'];
        }

        if ($this->relationLoaded('latestPrices')) {
            return $this->latestPrices->min('price') ?? null;
        }

        return $this->latestPrices()->min('price');
    }

    public function getMaxPriceAttribute(): ?float
    {
        if ($this->relationLoaded('latestPrices')) {
            return $this->latestPrices->max('price') ?? null;
        }

        return $this->latestPrices()->max('price');
    }

    public function getPriceRangeAttribute(): string
    {
        $min = $this->min_price;
        $max = $this->max_price;

        if (! $min) {
            return 'Prezzo non disponibile';
        }

        if ($min === $max) {
            return '€' . number_format($min, 0, ',', '.');
        }

        return '€' . number_format($min, 0, ',', '.') . ' - €' . number_format($max, 0, ',', '.');
    }

    public function getHasPricesAttribute(): bool
    {
        return $this->min_price !== null;
    }

    public function getFormattedDurationAttribute(): string
    {
        return $this->duration . ' nott' . ($this->duration === 1 ? 'e' : 'i');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('dep_date', '>=', Carbon::today());
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('dep_date', [$from, $to]);
    }

    /**
     * Filtra le partenze con min_price <= $maxPrice.
     * Usa la colonna denormalizzata: nessuna subquery su price_history.
     */
    public function scopeWithinBudget(Builder $query, float $maxPrice): Builder
    {
        return $query->where('min_price', '<=', $maxPrice);
    }

    public static function formatPrice(?float $price): string
    {
        if (! $price || $price <= 0) {
            return '-';
        }

        return '€' . number_format($price, 0, ',', '.');
    }
}
