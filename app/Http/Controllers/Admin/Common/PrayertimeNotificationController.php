<?php

namespace App\Http\Controllers\Admin\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Common\PrayertimeNotiMessage;
use Illuminate\Support\Facades\Validator;

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
}
