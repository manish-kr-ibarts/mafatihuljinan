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

        return redirect()->route('admin.prayertime-notifications.index')
            ->with('success', 'Prayer time notification updated successfully.');
    }

    public function destroy(PrayertimeNotiMessage $prayertimeNotification)
    {
        $prayertimeNotification->delete();
        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    public function syncPrayerTimes()
    {
        set_time_limit(300); // Increasing time limit for bulk update

        $tokens = PrayertimeNotiToken::whereNull('prayer_updated_at')
            ->orWhere('prayer_updated_at', '<=', now()->subHours(3))
            ->limit(5000)
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
                // Calculation method 0: Shia Ithna-Ashari, Leva Institute, Qum
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

                        $token->update([
                            'fajr'              => $timings['Fajr'],
                            'sunrise'           => $timings['Sunrise'],
                            'dhuhr'             => $timings['Dhuhr'],
                            'sunset'            => $timings['Sunset'],
                            'maghrib'           => $timings['Maghrib'],
                            'prayer_updated_at' => now(),
                        ]);

                        $totalUpdated++;
                        // Small delay to avoid hitting API rate limits if necessary
                        usleep(50000); // 50ms delay
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



    // scheduled notification for hinri date
    public function sendScheduledNotification()
    {
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

                // 5 minute window: (now - 5 min, now]
                $windowTimes = [];
                for ($i = 0; $i < 5; $i++) {
                    $windowTimes[] = $now->copy()->subMinutes($i)->format('H:i');
                }

                foreach ($activeMessages as $message) {
                    $prayerType = $message->prayer_type;
                    if (!$prayerType) continue;

                    $baseType = $prayerType;
                    $offset = 0;

                    if ($prayerType == '30_min_before_fajr') {
                        $baseType = 'fajr';
                        $offset = -30;
                    } elseif ($prayerType == '30_min_after_maghrib') {
                        $baseType = 'maghrib';
                        $offset = 30;
                    }

                    // Target database times: prayerTime = targetTime - offset
                    $targetDbTimes = [];
                    foreach ($windowTimes as $wt) {
                        try {
                            $targetDbTimes[] = Carbon::createFromFormat('H:i', $wt)
                                ->subMinutes($offset)
                                ->format('H:i');
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    // Fetch tokens whose specific prayer time falls in the target window
                    $tokens = PrayertimeNotiToken::where('timezone', $timezone)
                        ->whereNotNull('fcm_token')
                        ->where(function ($query) use ($baseType, $targetDbTimes) {
                            foreach ($targetDbTimes as $time) {
                                $query->orWhere($baseType, 'LIKE', $time . '%');
                            }
                        })
                        ->get();

                    if ($tokens->isEmpty()) continue;

                    $matchingFcmTokens = [];
                    foreach ($tokens as $token) {
                        $dd = $token->day_difference ?? 0;

                        // Check if it's after Maghrib to shift Hijri date
                        if ($token->maghrib) {
                            try {
                                $maghribTime = Carbon::createFromFormat('H:i', explode(' ', $token->maghrib)[0], $timezone);
                                if ($now->greaterThan($maghribTime)) {
                                    $dd++;
                                }
                            } catch (\Exception $e) {
                                // Skip if time format is invalid
                            }
                        }

                        // Cap dd at -5 to 5 as per available rows
                        $dd = max(-5, min(5, $dd));

                        if (!isset($hijriCache[$dd])) {
                            $hijriCache[$dd] = PrayertimeNotiHijriDate::where('day_difference', $dd)->first();
                        }
                        $hijriData = $hijriCache[$dd];

                        if ($hijriData && $this->checkFrequency($message, $hijriData, $dayName)) {
                            $matchingFcmTokens[] = $token->fcm_token;
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

        return match ($message->frequency) {
            'daily'   => true,
            'weekly'  => strtolower($message->week_day) === strtolower($dayName),
            'monthly' => (int)$message->day === (int)$hijriData->hijri_day,
            'yearly'  => (int)$message->day === (int)$hijriData->hijri_day &&
                ($hijriMonths[strtolower($message->month)] ?? null) === $hijriData->hijri_monthname,
            'custom'  => (int)$message->day === (int)$hijriData->hijri_day &&
                ($hijriMonths[strtolower($message->month)] ?? null) === $hijriData->hijri_monthname &&
                (int)$message->year === (int)$hijriData->hijri_year,
            default   => false,
        };
    }


    // No longer used but kept for backward compatibility if called directly
    protected function shouldSendMessage($currentTime, $message, $token, $hijriData, $dayName)
    {
        return $this->checkFrequency($message, $hijriData, $dayName);
    }


    public function sendNotification($notificationSchedule)
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
