<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_activities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'activity_type',
        'related_model_type',
        'related_model_id',
        'metadata',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic relation).
     */
    public function relatedModel()
    {
        return $this->morphTo('related', 'related_model_type', 'related_model_id');
    }

    /**
     * Log a new user activity.
     *
     * @param  int  $userId
     * @param  string  $activityType
     * @param  \Illuminate\Database\Eloquent\Model|null  $relatedModel
     * @param  array  $metadata
     * @return \App\Models\UserActivity
     */
    public static function log($userId, $activityType, $relatedModel = null, array $metadata = [])
    {
        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'related_model_type' => $relatedModel ? get_class($relatedModel) : null,
            'related_model_id' => $relatedModel ? $relatedModel->id : null,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Log activity for the currently authenticated user.
     *
     * @param  string  $activityType
     * @param  \Illuminate\Database\Eloquent\Model|null  $relatedModel
     * @param  array  $metadata
     * @return \App\Models\UserActivity|null
     */
    public static function logForCurrentUser($activityType, $relatedModel = null, array $metadata = [])
    {
        if (!Auth::check()) {
            return null;
        }

        return self::log(Auth::id(), $activityType, $relatedModel, $metadata);
    }

    /**
     * Get timeline of activities for a user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getTimeline($userId, $limit = 20)
    {
        return self::where('user_id', $userId)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get()
                   ->map(function ($activity) {
                       return [
                           'id' => $activity->id,
                           'type' => $activity->activity_type,
                           'description' => $activity->getDescription(),
                           'time' => $activity->created_at,
                           'time_ago' => $activity->created_at->diffForHumans(),
                           'metadata' => $activity->metadata,
                           'related_id' => $activity->related_model_id,
                           'related_type' => $activity->related_model_type
                       ];
                   });
    }

    /**
     * Get a human-readable description of the activity.
     *
     * @return string
     */
    public function getDescription()
    {
        $descriptions = [
            'search' => 'Hai cercato crociere',
            'view' => 'Hai visualizzato i dettagli',
            'favorite_add' => 'Hai aggiunto ai preferiti',
            'favorite_remove' => 'Hai rimosso dai preferiti',
            'alert_create' => 'Hai attivato un alert prezzo',
            'alert_modify' => 'Hai modificato un alert prezzo',
            'alert_delete' => 'Hai cancellato un alert',
            'share' => 'Hai condiviso',
            'download' => 'Hai scaricato i dettagli',
            'contact' => 'Hai richiesto informazioni'
        ];

        $base = $descriptions[$this->activity_type] ?? 'Attività sconosciuta';
        
        // Add details if available
        if ($this->metadata) {
            if (isset($this->metadata['cruise_name'])) {
                $base .= ' di <strong>' . e($this->metadata['cruise_name']) . '</strong>';
            } elseif (isset($this->metadata['destination'])) {
                $base .= ' per <strong>' . e($this->metadata['destination']) . '</strong>';
            }
        }

        return $base;
    }

    /**
     * Scope: Filter by activity type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope: Get recent activities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: For a specific user.
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
     * Get activity statistics for a user.
     *
     * @param  int  $userId
     * @param  int  $days
     * @return array
     */
    public static function getStatsForUser($userId, $days = 30)
    {
        $activities = self::where('user_id', $userId)
                         ->where('created_at', '>=', now()->subDays($days))
                         ->get();

        return [
            'total' => $activities->count(),
            'by_type' => $activities->groupBy('activity_type')
                                   ->map(fn($group) => $group->count())
                                   ->toArray(),
            'most_active_day' => $activities->groupBy(function($activity) {
                return $activity->created_at->format('Y-m-d');
            })->sortByDesc(fn($group) => $group->count())->keys()->first(),
        ];
    }

    /**
     * Get most common activities for a user.
     *
     * @param  int  $userId
     * @param  int  $limit
     * @return array
     */
    public static function getMostCommonActivities($userId, $limit = 5)
    {
        return self::where('user_id', $userId)
                   ->selectRaw('activity_type, COUNT(*) as count')
                   ->groupBy('activity_type')
                   ->orderBy('count', 'desc')
                   ->limit($limit)
                   ->pluck('count', 'activity_type')
                   ->toArray();
    }

    /**
     * Delete old activities (data retention).
     *
     * @param  int  $days
     * @return int Number of deleted records
     */
    public static function deleteOldActivities($days = 365)
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }
}
