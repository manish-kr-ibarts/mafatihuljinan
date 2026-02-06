<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayertimeNotiMessage extends Model
{
    use HasFactory;

    protected $table = 'prayertime_noti_messages';

    protected $fillable = [
        'frequency',
        'language',
        'notification_type',
        'notification_title',
        'notification_message',
        'minute',
        'hour',
        'day',
        'week_day',
        'month',
        'year',
        'prayer_type',
        'status',
    ];
}
