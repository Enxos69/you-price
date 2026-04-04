<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavorite extends Model
{
    protected $table = 'user_favorites';

    protected $fillable = ['user_id', 'departure_id', 'note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public static function isFavorite(int $userId, string $departureId): bool
    {
        return self::where('user_id', $userId)
                   ->where('departure_id', $departureId)
                   ->exists();
    }

    public static function toggle(int $userId, string $departureId, ?string $note = null): bool
    {
        $favorite = self::where('user_id', $userId)
                        ->where('departure_id', $departureId)
                        ->first();

        if ($favorite) {
            $favorite->delete();
            return false;
        }

        self::create(['user_id' => $userId, 'departure_id' => $departureId, 'note' => $note]);
        return true;
    }

    public static function getCountForUser(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    public static function getRecentForUser(int $userId, int $limit = 10)
    {
        return self::where('user_id', $userId)
                   ->with(['departure.product.ship', 'departure.product.cruiseLine', 'departure.latestPrices'])
                   ->orderByDesc('created_at')
                   ->limit($limit)
                   ->get();
    }
}
