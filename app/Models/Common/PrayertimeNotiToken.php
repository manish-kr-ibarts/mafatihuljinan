<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayertimeNotiToken extends Model
{
    use HasFactory;

    protected $table = 'prayertime_noti_tokens';

    protected $fillable = [
        'user_id',
        'fcm_token',
        'timezone',
        'user_lat',
        'user_long',
        'fajr',
        'sunrise',
        'dhuhr',
        'sunset',
        'maghrib',
        'prayer_updated_at',
        'day_difference',
    ];

    protected $casts = [
        'prayer_updated_at' => 'datetime',
    ];
}
