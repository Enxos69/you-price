<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCruiseView extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_cruise_views';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'cruise_id',
        'view_count',
        'last_viewed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_viewed_at' => 'datetime',
        'view_count' => 'integer',
    ];

    /**
     * Get the user that viewed the cruise.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cruise that was viewed.
     */
    public function cruise()
    {
        return $this->belongsTo(Cruise::class);
    }

    /**
     * Record a view for a cruise by a user.
     * Increments view_count if already exists, creates new record otherwise.
     *
     * @param  int  $userId
     * @param  int  $cruiseId
     * @return \App\Models\UserCruiseView
     */
    public static function recordView($userId, $cruiseId)
    {
        $view = self::firstOrNew([
            'user_id' => $userId,
            'cruise_id' => $cruiseId
        ]);

        $view->view_count = ($view->view_count ?? 0) + 1;
        $view->last_viewed_at = now();
        $view->save();

        return $view;
    }

    /**
     * Get most viewed cruises by a user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMostViewedByUser($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
                   ->orderBy('view_count', 'desc')
                   ->orderBy('last_viewed_at', 'desc')
                   ->with('cruise')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get recently viewed cruises by a user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentlyViewedByUser($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
                   ->orderBy('last_viewed_at', 'desc')
                   ->with('cruise')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get total number of different cruises viewed by a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getTotalViewedByUser($userId)
    {
        return self::where('user_id', $userId)->count();
    }

    /**
     * Get total view count (sum of all view_count) for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getTotalViewCountByUser($userId)
    {
        return self::where('user_id', $userId)->sum('view_count');
    }

    /**
     * Scope: Filter by user.
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
     * Scope: Filter by cruise.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $cruiseId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCruise($query, $cruiseId)
    {
        return $query->where('cruise_id', $cruiseId);
    }

    /**
     * Scope: Order by most viewed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMostViewed($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope: Order by recently viewed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentlyViewed($query)
    {
        return $query->orderBy('last_viewed_at', 'desc');
    }
}
