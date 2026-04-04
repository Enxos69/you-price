<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCruiseView extends Model
{
    protected $table = 'user_cruise_views';

    protected $fillable = ['user_id', 'departure_id', 'view_count', 'last_viewed_at'];

    protected $casts = [
        'last_viewed_at' => 'datetime',
        'view_count'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public static function recordView(int $userId, string $departureId): self
    {
        $view = self::firstOrNew(['user_id' => $userId, 'departure_id' => $departureId]);
        $view->view_count    = ($view->view_count ?? 0) + 1;
        $view->last_viewed_at = now();
        $view->save();
        return $view;
    }

    public static function getMostViewedByUser(int $userId, int $limit = 10)
    {
        return self::where('user_id', $userId)
                   ->orderByDesc('view_count')
                   ->orderByDesc('last_viewed_at')
                   ->with(['departure.product.ship', 'departure.product.cruiseLine'])
                   ->limit($limit)
                   ->get();
    }

    public static function getRecentlyViewedByUser(int $userId, int $limit = 10)
    {
        return self::where('user_id', $userId)
                   ->orderByDesc('last_viewed_at')
                   ->with(['departure.product.ship', 'departure.product.cruiseLine'])
                   ->limit($limit)
                   ->get();
    }

    public static function getTotalViewedByUser(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    public static function getTotalViewCountByUser(int $userId): int
    {
        return self::where('user_id', $userId)->sum('view_count');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeMostViewed($query)
    {
        return $query->orderByDesc('view_count');
    }

    public function scopeRecentlyViewed($query)
    {
        return $query->orderByDesc('last_viewed_at');
    }
}
