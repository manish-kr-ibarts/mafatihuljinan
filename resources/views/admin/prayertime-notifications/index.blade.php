@extends('layouts.admin')

@section('title', 'Prayer Time Notifications')

@section('content')
<div class="mx-auto bg-white p-6 rounded-2xl shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-[#034E7A]">
            Scheduled Prayer Notifications
        </h1>

        <a href="{{ route('admin.prayertime-notifications.create') }}"
            class="bg-[#034E7A] text-white px-4 py-2 rounded hover:bg-[#02629B] transition">
            + Schedule New
        </a>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-[#034E7A]">
                <tr>
                    <th class="border px-3 py-2">Language</th>
                    <th class="border px-3 py-2 text-left">Title / Message</th>
                    <th class="border px-3 py-2">Frequency</th>
                    <th class="border px-3 py-2">Type</th>
                    <th class="border px-3 py-2">Prayer Type</th>
                    <th class="border px-3 py-2">Time/Schedule</th>
                    <th class="border px-3 py-2">Status</th>
                    <th class="border px-3 py-2">Created At</th>
                    <th class="border px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $hijriMonths = [
                'january' => 'Muharram',
                'february' => 'Safar',
                'march' => 'Rabi al Awwal',
                'april' => 'Rabi al Thani',
                'may' => 'Jumada al Awwal',
                'june' => 'Jumada al Thani',
                'july' => 'Rajab',
                'august' => 'Sha Ban',
                'september' => 'Ramadan',
                'october' => 'Shawwal',
                'november' => 'Dhul Qi Dah',
                'december' => 'Dhul Hijjah'
                ];
                @endphp
                @forelse ($notifications as $notification)
                <tr class="hover:bg-gray-50 text-center">
                    <td class="border px-3 py-2">{{ strtoupper($notification->language) }}</td>
                    <td class="border px-3 py-2 text-left">
                        <div class="font-medium">{{ $notification->notification_title }}</div>
                        <div class="text-xs text-gray-500">
                            {{ \Illuminate\Support\Str::limit($notification->notification_message, 50) }}
                        </div>
                    </td>

                    <td class="border px-3 py-2 capitalize">
                        {{ strtoupper($notification->frequency) }}
                    </td>

                    <td class="border px-3 py-2 uppercase">
                        {{ $notification->notification_type }}
                    </td>

                    <td class="border px-3 py-2 uppercase">
                        {{ $notification->prayer_type ?? '—' }}
                    </td>

                    <td class="border px-3 py-2 text-xs">
                        @if($notification->hour !== null && $notification->minute !== null)
                        Time: {{ sprintf('%02d:%02d', $notification->hour, $notification->minute) }}<br>
                        @endif

                        @if($notification->frequency == 'weekly' && $notification->week_day)
                        Day: {{ $notification->week_day }}
                        @elseif($notification->frequency == 'monthly' && $notification->day)
                        Day: {{ $notification->day }}
                        @elseif($notification->frequency == 'yearly' && $notification->month && $notification->day)
                        {{ $hijriMonths[$notification->month] ?? $notification->month }} {{ $notification->day }}
                        @elseif($notification->frequency == 'custom')
                        {{ $notification->day ?? '' }} {{ $hijriMonths[$notification->month] ?? $notification->month }} {{ $notification->year ?? '' }}
                        @endif
                    </td>

                    <td class="border px-3 py-2">
                        <span class="px-2 py-1 rounded text-xs {{ $notification->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($notification->status) }}
                        </span>
                    </td>

                    <td class="border px-3 py-2 text-xs">
                        {{ $notification->created_at->format('d M Y H:i') }}
                    </td>

                    <td class="border px-2 py-1">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.prayertime-notifications.edit', $notification->id) }}"
                                class="text-blue-600 hover:text-blue-800 transition p-1" title="Edit">
                                Edit
                            </a>
                            <form action="{{ route('admin.prayertime-notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this notification?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition p-1" title="Delete">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">
                        No prayer notifications scheduled.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection