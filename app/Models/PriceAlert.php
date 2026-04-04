<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAlert extends Model
{
    protected $table = 'price_alerts';

    protected $fillable = [
        'user_id',
        'departure_id',
        'target_price',
        'category_code',
        'alert_type',
        'percentage_threshold',
        'is_active',
        'current_price',
        'last_checked_at',
        'last_notification_sent_at',
        'notification_sent',
    ];

    protected $casts = [
        'target_price'              => 'decimal:2',
        'percentage_threshold'      => 'decimal:2',
        'current_price'             => 'decimal:2',
        'is_active'                 => 'boolean',
        'notification_sent'         => 'boolean',
        'last_checked_at'           => 'datetime',
        'last_notification_sent_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNeedingCheck($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('last_checked_at')
                                         ->orWhere('last_checked_at', '<=', now()->subHours(6)));
    }

    // -------------------------------------------------------------------------
    // Business logic
    // -------------------------------------------------------------------------

    public function isPriceReached(): bool
    {
        if (! $this->current_price || ! $this->target_price) {
            return false;
        }

        if ($this->alert_type === 'fixed_price') {
            return $this->current_price <= $this->target_price;
        }

        if ($this->alert_type === 'percentage_discount' && $this->percentage_threshold) {
            $discountedPrice = $this->target_price * (1 - ($this->percentage_threshold / 100));
            return $this->current_price <= $discountedPrice;
        }

        return false;
    }

    /**
     * Recupera il prezzo più recente dalla price_history per la categoria monitorata.
     */
    public function checkPrice(): void
    {
        $latestPrice = PriceHistory::where('departure_id', $this->departure_id)
            ->where('category_code', $this->category_code)
            ->orderByDesc('id')
            ->value('price');

        $this->last_checked_at = now();

        if ($latestPrice === null) {
            $this->save();
            return;
        }

        $this->current_price = $latestPrice;

        if ($this->isPriceReached() && ! $this->notification_sent) {
            $this->notification_sent          = true;
            $this->last_notification_sent_at  = now();
            // TODO: event(new PriceAlertTriggered($this));
        }

        $this->save();
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    public static function getActiveCountForUser(int $userId): int
    {
        return self::where('user_id', $userId)->where('is_active', true)->count();
    }

    public static function getTriggeredAlerts(?int $userId = null)
    {
        $query = self::active()
                     ->where('notification_sent', true)
                     ->with('departure.product');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    public function activate(): bool
    {
        $this->is_active        = true;
        $this->notification_sent = false;
        return $this->save();
    }

    public function resetNotification(): bool
    {
        $this->notification_sent         = false;
        $this->last_notification_sent_at = null;
        return $this->save();
    }

    public function getProgressPercentage(): float
    {
        if (! $this->current_price || ! $this->target_price) return 0;
        return min(100, round(($this->target_price / $this->current_price) * 100, 2));
    }

    public function getDiscountPercentage(): float
    {
        if (! $this->current_price || ! $this->target_price) return 0;
        if ($this->current_price >= $this->target_price) return 0;
        return round((($this->target_price - $this->current_price) / $this->target_price) * 100, 2);
    }
}
