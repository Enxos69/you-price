<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceAlert extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'price_alerts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'cruise_id',
        'target_price',
        'cabin_type',
        'alert_type',
        'percentage_threshold',
        'is_active',
        'current_price',
        'last_checked_at',
        'last_notification_sent_at',
        'notification_sent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'target_price' => 'decimal:2',
        'percentage_threshold' => 'decimal:2',
        'current_price' => 'decimal:2',
        'is_active' => 'boolean',
        'notification_sent' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_notification_sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the alert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cruise for this alert.
     */
    public function cruise()
    {
        return $this->belongsTo(Cruise::class);
    }

    /**
     * Scope: Get only active alerts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get alerts for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get alerts that need checking.
     * (active alerts not checked in the last 6 hours)
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedingCheck($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('last_checked_at')
                           ->orWhere('last_checked_at', '<=', now()->subHours(6));
                     });
    }

    /**
     * Check if the current price has reached the target.
     *
     * @return bool
     */
    public function isPriceReached()
    {
        if (!$this->current_price || !$this->target_price) {
            return false;
        }

        if ($this->alert_type === 'fixed_price') {
            return $this->current_price <= $this->target_price;
        } 
        
        if ($this->alert_type === 'percentage_discount' && $this->percentage_threshold) {
            // Calcola il prezzo con lo sconto percentuale
            $discountedPrice = $this->target_price * (1 - ($this->percentage_threshold / 100));
            return $this->current_price <= $discountedPrice;
        }

        return false;
    }

    /**
     * Update the current price and check if target is reached.
     *
     * @return void
     */
    public function checkPrice()
    {
        // Ottieni il prezzo dalla crociera in base al tipo di cabina
        $priceField = $this->cabin_type;
        $rawPrice = $this->cruise->{$priceField};
        
        if (!$rawPrice) {
            $this->last_checked_at = now();
            $this->save();
            return;
        }

        // Pulisci il prezzo da caratteri non numerici
        $cleanPrice = $this->cleanPrice($rawPrice);
        
        if ($cleanPrice === null) {
            $this->last_checked_at = now();
            $this->save();
            return;
        }

        $this->current_price = $cleanPrice;
        $this->last_checked_at = now();
        
        // Se il prezzo ha raggiunto il target e non è stata ancora inviata la notifica
        if ($this->isPriceReached() && !$this->notification_sent) {
            $this->notification_sent = true;
            $this->last_notification_sent_at = now();
            
            // TODO: Implementare invio notifica (email, push, etc)
            // event(new PriceAlertTriggered($this));
        }
        
        $this->save();
    }

    /**
     * Clean price string and convert to float.
     *
     * @param  string  $price
     * @return float|null
     */
    private function cleanPrice($price)
    {
        if (empty($price)) {
            return null;
        }

        // Rimuovi caratteri non numerici eccetto punto e virgola
        $cleaned = preg_replace('/[^\d,.]/', '', $price);
        
        // Gestisci formato europeo (1.234,56) e americano (1,234.56)
        if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
            // Entrambi presenti: rimuovi il separatore delle migliaia
            if (strpos($cleaned, ',') < strrpos($cleaned, '.')) {
                // Formato americano: 1,234.56
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                // Formato europeo: 1.234,56
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            }
        } elseif (strpos($cleaned, ',') !== false) {
            // Solo virgola: formato europeo
            $cleaned = str_replace(',', '.', $cleaned);
        }
        
        return floatval($cleaned);
    }

    /**
     * Get active alerts count for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getActiveCountForUser($userId)
    {
        return self::where('user_id', $userId)
                   ->where('is_active', true)
                   ->count();
    }

    /**
     * Get triggered alerts (price reached).
     *
     * @param  int|null  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTriggeredAlerts($userId = null)
    {
        $query = self::active()
                    ->where('notification_sent', true)
                    ->with('cruise');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Deactivate alert.
     *
     * @return bool
     */
    public function deactivate()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Activate alert.
     *
     * @return bool
     */
    public function activate()
    {
        $this->is_active = true;
        $this->notification_sent = false;
        return $this->save();
    }

    /**
     * Reset notification status.
     *
     * @return bool
     */
    public function resetNotification()
    {
        $this->notification_sent = false;
        $this->last_notification_sent_at = null;
        return $this->save();
    }

    /**
     * Get progress percentage towards target price.
     *
     * @return float
     */
    public function getProgressPercentage()
    {
        if (!$this->current_price || !$this->target_price) {
            return 0;
        }

        return min(100, round(($this->target_price / $this->current_price) * 100, 2));
    }

    /**
     * Get discount percentage from target.
     *
     * @return float
     */
    public function getDiscountPercentage()
    {
        if (!$this->current_price || !$this->target_price) {
            return 0;
        }

        if ($this->current_price >= $this->target_price) {
            return 0;
        }

        return round((($this->target_price - $this->current_price) / $this->target_price) * 100, 2);
    }
}
