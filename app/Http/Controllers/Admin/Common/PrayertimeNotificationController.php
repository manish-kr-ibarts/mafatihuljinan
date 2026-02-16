<?php

namespace App\Http\Controllers\Admin\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Common\PrayertimeNotiMessage;
use App\Models\Common\PrayertimeNotiToken;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Common\UserFcmToken;
use App\Models\Common\NotificationSchedule;
use App\Models\Common\PrayertimeNotiHijriDate;
use App\Services\HijriDateService;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\AndroidNotification;
use Kreait\Firebase\Messaging\Aps;
use Illuminate\Support\Facades\Auth;

class PrayertimeNotificationController extends Controller
{
    public function index()
    {
        $notifications = PrayertimeNotiMessage::orderBy('created_at', 'desc')->get();
        return view('admin.prayertime-notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.prayertime-notifications.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'frequency'            => 'required|string',
            'language'             => 'nullable|string',
            'notification_type'    => 'required|string',
            'notification_title'   => 'required|string|max:255',
            'notification_message' => 'required|string',
            'minute'               => 'nullable|integer|min:0|max:59',
            'hour'                 => 'nullable|integer|min:0|max:23',
            'day'                  => 'nullable|integer|min:1|max:31',
            'week_day'             => 'nullable|string',
            'month'                => 'nullable|string',
            'year'                 => 'nullable|integer',
            'prayer_type'          => 'nullable|string',
            'status'               => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        PrayertimeNotiMessage::create($request->all());
        logActivity(Auth::user(), 'Create', 'Created one Prayer time notification : ' . $request->notification_title);
        return redirect()->route('admin.prayertime-notifications.index')
            ->with('success', 'Prayer time notification scheduled successfully.');
    }

    public function edit(PrayertimeNotiMessage $prayertimeNotification)
    {
        return view('admin.prayertime-notifications.edit', compact('prayertimeNotification'));
    }

    public function update(Request $request, PrayertimeNotiMessage $prayertimeNotification)
    {
        $validator = Validator::make($request->all(), [
            'frequency'            => 'required|string',
            'language'             => 'nullable|string',
            'notification_type'    => 'required|string',
            'notification_title'   => 'required|string|max:255',
            'notification_message' => 'required|string',
            'minute'               => 'nullable|integer|min:0|max:59',
            'hour'                 => 'nullable|integer|min:0|max:23',
            'day'                  => 'nullable|integer|min:1|max:31',
            'week_day'             => 'nullable|string',
            'month'                => 'nullable|string',
            'year'                 => 'nullable|integer',
            'prayer_type'          => 'nullable|string',
            'status'               => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $prayertimeNotification->update($request->all());
        logActivity(Auth::user(), 'Update', 'Updated one Prayer time notification : ' . $request->notification_title);
        return redirect()->route('admin.prayertime-notifications.index')
            ->with('success', 'Prayer time notification updated successfully.');
    }

    public function destroy(PrayertimeNotiMessage $prayertimeNotification)
    {
        $prayertimeNotification->delete();
        logActivity(Auth::user(), 'Delete', 'Deleted one Prayer time notification : ' . $prayertimeNotification->notification_title);
        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    public function syncPrayerTimes()
    {
        set_time_limit(300); // Increasing time limit for bulk update

        $tokens = PrayertimeNotiToken::whereNull('prayer_updated_at')
            ->orWhere('prayer_updated_at', '<=', now()->subHours(2))
            ->get();

        $totalUpdated = 0;
        foreach ($tokens as $token) {
            $latitude = $token->user_lat;
            $longitude = $token->user_long;
            $timezone = $token->timezone ?? 'Asia/Kolkata';

            if (empty($latitude) || empty($longitude)) {
                continue;
            }

            try {
                $method = 0;
                $timestamp = Carbon::now($timezone)->timestamp;

                $response = Http::get("https://api.aladhan.com/v1/timings/{$timestamp}", [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                    'method'    => $method,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['status']) && $data['status'] === 'OK') {
                        $timings = $data['data']['timings'];
                        $fajr = Carbon::parse($timings['Fajr']);
                        $maghrib = Carbon::parse($timings['Maghrib']);
                        $token->update([
                            'fajr'              => $timings['Fajr'],
                            'sunrise'           => $timings['Sunrise'],
                            'dhuhr'             => $timings['Dhuhr'],
                            'sunset'            => $timings['Sunset'],
                            'maghrib'           => $timings['Maghrib'],
                            '30_min_before_fajr' => $fajr->subMinutes(30)->format('H:i'),
                            '30_min_after_maghrib' => $maghrib->addMinutes(30)->format('H:i'),
                            'prayer_updated_at' => now(),
                        ]);

                        $totalUpdated++;
                        // Small delay to avoid hitting API rate limits if necessary
                        usleep(50000); // 50ms delay
                        usleep(200000); // 200ms delay
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to update prayer times for token ID {$token->id}: " . $e->getMessage());
                continue;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Prayer times updated successfully.',
            'total_updated_rows' => $totalUpdated
        ]);
    }

    public function syncHijriDates()
    {
        try {
            for ($i = -5; $i <= 5; $i++) {
                $date = Carbon::now()->addDays($i)->format('Y-m-d');
                $hijri = new HijriDateService(strtotime($date));

                $hijrimonth = $hijri->get_month();
                $hijrimonthname = $hijri->get_month_name($hijrimonth);
                $hijrimonthname = str_replace("'", "", $hijrimonthname);

                PrayertimeNotiHijriDate::updateOrCreate(
                    ['day_difference' => $i],
                    [
                        'hijri_date'      => $hijri->get_date(),
                        'hijri_day'       => $hijri->get_day(),
                        'hijri_monthname' => $hijrimonthname,
                        'hijri_year'      => $hijri->get_year(),
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Hijri dates updated successfully for offsets -5 to +5.'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update Hijri dates: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update Hijri dates: ' . $e->getMessage()
            ], 500);
        }
    }



    // scheduled notification for hijri date
    public function sendScheduledNotification()
    {
        $hijriMonths = [
            "january"   => "Muharram",
            "february"  => "Safar",
            "march"     => "Rabi'ul Awwal",
            "april"     => "Rabi'ul Akhir",
            "may"       => "Jumadal Ula",
            "june"      => "Jumadal Akhira",
            "july"      => "Rajab",
            "august"    => "Sha'ban",
            "september" => "Ramadan",
            "october"   => "Shawwal",
            "november"  => "Dhul Qa'ada",
            "december"  => "Dhul Hijja"
        ];
        $activeMessages = PrayertimeNotiMessage::where('status', 'active')->get();
        if ($activeMessages->isEmpty()) {
            return;
        }

        $timezones = PrayertimeNotiToken::whereNotNull('timezone')->distinct()->pluck('timezone');
        $hijriCache = [];

        foreach ($timezones as $timezone) {
            try {
                $now = Carbon::now($timezone);
                $dayName = $now->format('l');
                $currentTime = $now->format('H:i');
                // 5 minute window: (now - 5 min, now]
                $differenceTime = 0;
                $differenceTime = $now->copy()->subMinutes(5)->format('H:i');
                // dd($currentTime, $differenceTime);
                foreach ($activeMessages as $message) {
                    $prayerType = $message->prayer_type;
                    if (!$prayerType) continue;

                    $frequency = $message->frequency;

                    // Fetch tokens
                    if ($prayerType == '30_min_before_fajr') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('30_min_before_fajr', '>=', $differenceTime)
                            ->where('30_min_before_fajr', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } elseif ($prayerType == 'fajr') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('fajr', '>=', $differenceTime)
                            ->where('fajr', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } elseif ($prayerType == 'sunrise') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('sunrise', '>=', $differenceTime)
                            ->where('sunrise', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } elseif ($prayerType == 'dhuhr') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('dhuhr', '>=', $differenceTime)
                            ->where('dhuhr', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } elseif ($prayerType == 'sunset') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('sunset', '>=', $differenceTime)
                            ->where('sunset', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } elseif ($prayerType == 'maghrib') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('maghrib', '>=', $differenceTime)
                            ->where('maghrib', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } elseif ($prayerType == '30_min_after_maghrib') {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->where('30_min_after_maghrib', '>=', $differenceTime)
                            ->where('30_min_after_maghrib', '<=', $currentTime)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    } else {
                        $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                            ->whereNotNull('fcm_token')
                            ->where('language', $message->language)
                            ->get();
                    }

                    if ($tokens->isEmpty()) continue;

                    $matchingFcmTokens = [];
                    foreach ($tokens as $token) {
                        if ($frequency == 'daily') {
                            $matchingFcmTokens[] = $token->fcm_token;
                        } else  if ($frequency == 'weekly') {
                            if (strtolower($dayName) == strtolower($message->week_day)) {
                                $matchingFcmTokens[] = $token->fcm_token;
                            }
                        } else {
                            if ($token->day_difference != 0 && ($token->day_difference != '' && $token->day_difference != null)) {
                                $date = Carbon::now($timezone)->addDays((int)$token->day_difference)->format('Y-m-d');
                            } else {
                                $date = Carbon::now($timezone)->format('Y-m-d');
                            }
                            $hijri = new HijriDateService(strtotime($date));

                            $hijriday = $hijri->get_day();
                            $hijrimonth = $hijri->get_month();
                            $hijrimonthname = $hijri->get_month_name($hijrimonth);
                            $hijriyear = $hijri->get_year();

                            if ($frequency == 'monthly') {
                                if ($hijriday  != $message->day) {
                                    continue;
                                }
                                $matchingFcmTokens[] = $token->fcm_token;
                            } else if ($frequency == 'yearly') {
                                if ($hijriday != $message->day || $hijrimonthname != $hijriMonths[strtolower($message->month)]) {
                                    continue;
                                }
                                $matchingFcmTokens[] = $token->fcm_token;
                            } else if ($frequency == 'custom') {
                                if ($hijriday != $message->day || $hijrimonthname != $hijriMonths[strtolower($message->month)] || $hijriyear != $message->year) {
                                    continue;
                                }
                                $matchingFcmTokens[] = $token->fcm_token;
                            }
                        }
                    }
                    if (!empty($matchingFcmTokens)) {
                        $this->sendPushNotificationBulk($matchingFcmTokens, $message);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error processing notifications for timezone {$timezone}: " . $e->getMessage());
            }
        }
    }

    protected function checkFrequency($message, $hijriData, $dayName)
    {
        $hijriMonths = [
            "january"   => "Muharram",
            "february"  => "Safar",
            "march"     => "Rabi'ul Awwal",
            "april"     => "Rabi'ul Akhir",
            "may"       => "Jumadal Ula",
            "june"      => "Jumadal Akhira",
            "july"      => "Rajab",
            "august"    => "Sha'ban",
            "september" => "Ramadan",
            "october"   => "Shawwal",
            "november"  => "Dhul Qa'ada",
            "december"  => "Dhul Hijja"
        ];
        $hijriDay   = $hijriData->get_day();
        $hijriMonth = $hijriData->get_month_name($hijriData->get_month());
        $hijriYear  = $hijriData->get_year();

        return match ($message->frequency) {
            'daily'   => true,

            'weekly'  => strtolower($message->week_day) === strtolower($dayName),

            'monthly' => (int)$message->day === (int)$hijriDay,

            'yearly'  => (int)$message->day === (int)$hijriDay &&
                ($hijriMonths[strtolower($message->month)] ?? null) === $hijriMonth,

            'custom'  => (int)$message->day === (int)$hijriDay &&
                ($hijriMonths[strtolower($message->month)] ?? null) === $hijriMonth &&
                (int)$message->year === (int)$hijriYear,

            default   => false,
        };
    }


    public function sendNotificationToAll($notificationSchedule)
    {
        $tokens = PrayertimeNotiToken::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        if (!empty($tokens)) {
            $this->sendPushNotificationBulk($tokens, $notificationSchedule);
        }
    }


    public function sendPushNotificationBulk($tokens, $notificationSchedule)
    {
        if (empty($tokens)) return;

        $message = CloudMessage::new()
            ->withNotification(Notification::create(
                $notificationSchedule->notification_title,
                $notificationSchedule->notification_message
            ))
            ->withData([
                'message' => $notificationSchedule->notification_message,
            ])
            ->withAndroidConfig(AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                ],
            ]))
            ->withApnsConfig(ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'alert' => [
                            'title' => $notificationSchedule->notification_title,
                            'body' => $notificationSchedule->notification_message,
                        ],
                    ],
                ],
            ]));

        $chunks = array_chunk($tokens, 500);
        foreach ($chunks as $chunk) {
            try {
                Firebase::messaging()->sendMulticast($message, $chunk);
                Log::info('Bulk notifications sent successfully', ['count' => count($chunk)]);
            } catch (\Exception $e) {
                Log::error('Bulk notification failed', ['error' => $e->getMessage()]);
            }
        }
    }

    public function sendPushNotification($token, $notificationSchedule)
    {
        $this->sendPushNotificationBulk([$token], $notificationSchedule);
    }
}
