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
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Laravel\Firebase\Facades\Firebase; // Use the facade
use Kreait\Firebase\Messaging\MulticastSendReport;

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

    public function sendScheduledNotification()
    {
        $notificationMessages = PrayertimeNotiMessage::where('status', 'active')->get();
        foreach ($notificationMessages as $notificationMessage) {
            $this->sendNotification($notificationMessage);
        }
    }

    public function sendNotification($notificationSchedule)
    {
        $tokens = UserFcmToken::where('status', 'active')->get();
        foreach ($tokens as $token) {
            $this->sendPushNotification($token->token, $notificationSchedule);
        }
    }

    public function sendPushNotification($token, $notificationSchedule)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create(
                $notificationSchedule->notification_title,
                $notificationSchedule->notification_message
            ))
            ->withAndroidConfig(
                AndroidConfig::new()
                    ->setPriority('high')
                    ->setTtl(0)
                    ->setNotification(
                        AndroidNotification::create()
                            ->setSound('default')
                            ->setPriority('high')
                    )
            )
            ->withApnsConfig(
                ApnsConfig::new()
                    ->setHeaders([
                        'apns-priority' => '10',
                        'apns-topic' => 'com.example.app', // Replace with your app's bundle ID
                    ])
                    ->setAps(
                        Aps::create()
                            ->setSound('default')
                            ->setAlert([
                                'title' => $notificationSchedule->notification_title,
                                'body' => $notificationSchedule->notification_message,
                            ])
                    )
            );

        try {
            $response = Firebase::messaging()->send($message);
            Log::info('Notification sent successfully:', [
                'token' => $token,
                'response' => $response
            ]);
        } catch (MessagingException $e) {
            Log::error('Failed to send notification:', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
        } catch (FirebaseException $e) {
            Log::error('Firebase error:', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
        }
    }
}
