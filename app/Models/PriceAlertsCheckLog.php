<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceAlertsCheckLog extends Model
{
    public $timestamps = false;

    protected $table = 'price_alerts_check_log';

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getDurationAttribute(): ?string
    {
        if (! $this->finished_at || ! $this->started_at) {
            return null;
        }

        $seconds = $this->finished_at->diffInSeconds($this->started_at);

        return $seconds >= 60
            ? floor($seconds / 60) . 'm ' . ($seconds % 60) . 's'
            : $seconds . 's';
    }
}
