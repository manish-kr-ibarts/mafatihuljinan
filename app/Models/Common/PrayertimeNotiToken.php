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
        'language',
        'fcm_token',
        'timezone',
        'user_lat',
        'user_long',
        '30_min_before_fajr',
        'fajr',
        'sunrise',
        'dhuhr',
        'sunset',
        'maghrib',
        '30_min_after_maghrib',
        'prayer_updated_at',
        'day_difference',
    ];

    protected $casts = [
        'prayer_updated_at' => 'datetime',
    ];
}
