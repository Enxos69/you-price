<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_favorites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'cruise_id',
        'note'
    ];

    /**
     * Get the user that owns the favorite.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cruise that is favorited.
     */
    public function cruise()
    {
        return $this->belongsTo(Cruise::class);
    }

    /**
     * Scope: Get favorites for a specific user.
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
     * Check if a cruise is favorited by a user.
     *
     * @param  int  $userId
     * @param  int  $cruiseId
     * @return bool
     */
    public static function isFavorite($userId, $cruiseId)
    {
        return self::where('user_id', $userId)
                   ->where('cruise_id', $cruiseId)
                   ->exists();
    }

    /**
     * Toggle favorite (add/remove).
     *
     * @param  int  $userId
     * @param  int  $cruiseId
     * @param  string|null  $note
     * @return bool Returns true if added, false if removed
     */
    public static function toggle($userId, $cruiseId, $note = null)
    {
        $favorite = self::where('user_id', $userId)
                        ->where('cruise_id', $cruiseId)
                        ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // Rimosso
        } else {
            self::create([
                'user_id' => $userId,
                'cruise_id' => $cruiseId,
                'note' => $note
            ]);
            return true; // Aggiunto
        }
    }

    /**
     * Get total favorites count for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getCountForUser($userId)
    {
        return self::where('user_id', $userId)->count();
    }

    /**
     * Get recent favorites for a user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentForUser($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
                   ->with('cruise')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }
}
