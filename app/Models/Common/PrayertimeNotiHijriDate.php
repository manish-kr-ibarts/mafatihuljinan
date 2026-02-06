<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayertimeNotiHijriDate extends Model
{
    use HasFactory;

    protected $table = 'prayertime_noti_hijri_dates';

    protected $fillable = [
        'hijri_date',
        'hijri_day',
        'hijri_monthname',
        'hijri_year',
        'day_difference',
    ];
}
