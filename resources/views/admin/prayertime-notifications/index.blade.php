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

    {{-- Frontend Filters --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
        @php
        $languages = $notifications->pluck('language')->unique()->filter();
        $frequencies = $notifications->pluck('frequency')->unique()->filter();
        $prayerTypes = $notifications->pluck('prayer_type')->unique()->filter();
        @endphp

        {{-- Language Filter --}}
        <div>
            <label for="languageFilter" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Language</label>
            <select id="languageFilter" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#034E7A] text-gray-700 bg-white">
                <option value="">ALL LANGUAGES</option>
                @foreach($languages as $lang)
                <option value="{{ strtoupper($lang) }}">{{ strtoupper($lang) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Frequency Filter --}}
        <div>
            <label for="frequencyFilter" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Frequency</label>
            <select id="frequencyFilter" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#034E7A] text-gray-700 bg-white">
                <option value="">ALL FREQUENCIES</option>
                @foreach($frequencies as $freq)
                <option value="{{ strtoupper($freq) }}">{{ strtoupper($freq) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Prayer Type Filter --}}
        <div>
            <label for="prayerTypeFilter" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Prayer Type</label>
            <select id="prayerTypeFilter" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#034E7A] text-gray-700 bg-white">
                <option value="">ALL PRAYER TYPES</option>
                @foreach($prayerTypes as $type)
                <option value="{{ strtoupper($type) }}">{{ strtoupper(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const languageFilter = document.getElementById('languageFilter');
        const frequencyFilter = document.getElementById('frequencyFilter');
        const prayerTypeFilter = document.getElementById('prayerTypeFilter');
        const tableRows = document.querySelectorAll('tbody tr');

        function filterTable() {
            const langValue = languageFilter.value.toUpperCase();
            const freqValue = frequencyFilter.value.toUpperCase();
            const typeValue = prayerTypeFilter.value.toUpperCase();

            tableRows.forEach(row => {
                // Skip rows that don't have enough cells (like the 'No records' row)
                if (row.cells.length < 8) return;

                const langText = row.cells[0].textContent.trim().toUpperCase();
                const freqText = row.cells[2].textContent.trim().toUpperCase();
                // Ensure we get the raw text content for prayer type
                const typeText = row.cells[4].textContent.trim().toUpperCase();

                const matchesLang = langValue === '' || langText === langValue;
                const matchesFreq = freqValue === '' || freqText === freqValue;
                const matchesType = typeValue === '' || typeText === typeValue;

                if (matchesLang && matchesFreq && matchesType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        languageFilter.addEventListener('change', filterTable);
        frequencyFilter.addEventListener('change', filterTable);
        prayerTypeFilter.addEventListener('change', filterTable);
    });
</script>
@endsection