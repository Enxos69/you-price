<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role',
        'abilitato'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role', 'id');
    }

    public function isAdmin()
    {
        return $this->role === '1';
    }

    public function isUser()
    {
        return $this->role === '2';
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    /**
     * Get the user's favorite cruises.
     * Relazione many-to-many con Cruise attraverso user_favorites.
     */
    public function favorites()
    {
        return $this->belongsToMany(Cruise::class, 'user_favorites')
            ->withTimestamps()
            ->withPivot('note');
    }

    /**
     * Get the user's price alerts.
     * Relazione one-to-many con PriceAlert.
     */
    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class);
    }

    /**
     * Get the user's activities.
     * Relazione one-to-many con UserActivity.
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the user's cruise views.
     * Relazione one-to-many con UserCruiseView.
     */
    public function cruiseViews()
    {
        return $this->hasMany(UserCruiseView::class);
    }

    /**
     * Get the user's search logs.
     * (Relazione già esistente con SearchLog, inclusa qui per completezza)
     */
    public function searchLogs()
    {
        return $this->hasMany(SearchLog::class);
    }

    /**
     * Get dashboard statistics for the user.
     * Returns an array with key metrics for the dashboard.
     *
     * @return array
     */
    public function getDashboardStats()
    {
        return [
            'total_searches' => SearchLog::where('user_id', $this->id)->count(),
            'cruises_viewed' => UserCruiseView::getTotalViewedByUser($this->id),
            'favorites_count' => $this->favorites()->count(),
            'active_alerts' => PriceAlert::getActiveCountForUser($this->id),
            'member_since' => $this->created_at->format('F Y'),
            'last_activity' => UserActivity::where('user_id', $this->id)
                ->latest()
                ->first()
                ?->created_at
        ];
    }

    /**
     * Check if the user has favorited a specific cruise.
     *
     * @param  int  $cruiseId
     * @return bool
     */
    public function hasFavorited($cruiseId)
    {
        return UserFavorite::isFavorite($this->id, $cruiseId);
    }

    /**
     * Get the user's most viewed cruises.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMostViewedCruises($limit = 5)
    {
        return UserCruiseView::getMostViewedByUser($this->id, $limit);
    }

    /**
     * Get the user's recent activities.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecentActivities($limit = 10)
    {
        return UserActivity::getTimeline($this->id, $limit);
    }

    /**
     * Get triggered price alerts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTriggeredAlerts()
    {
        return PriceAlert::getTriggeredAlerts($this->id);
    }

    /**
     * Get user's favorite cruises with details.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFavoriteCruises($limit = null)
    {
        $query = $this->favorites()->with('cruise');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->orderBy('user_favorites.created_at', 'desc')->get();
    }

    /**
     * Get user's active price alerts with cruise details.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePriceAlerts()
    {
        return $this->priceAlerts()
            ->active()
            ->with('cruise')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
