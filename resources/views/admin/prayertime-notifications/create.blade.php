@extends('layouts.admin')

@section('title', 'Schedule Prayer Notification')

@section('content')
<div class="mx-auto bg-white px-8 py-4 rounded shadow rounded-2xl">

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.prayertime-notifications.store') }}" method="POST" class="space-y-3">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Language --}}
            <div>
                <label class="block font-medium mb-1 text-[#034E7A]">Language</label>
                <select name="language" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                    @foreach(validLanguages() as $language)
                    <option value="{{ $language }}">{{ ucfirst($language) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Frequency --}}
            <div>
                <label class="block font-medium mb-1 text-[#034E7A]">Frequency</label>
                <select name="frequency" id="frequency" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]" required onchange="toggleFormFields()">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            {{-- Notification Type --}}
            <div>
                <label class="block font-medium mb-1 text-[#034E7A]">Notification Type</label>
                <select name="notification_type" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]" required>
                    <option value="normal">Normal</option>
                    <option value="event">Event</option>
                    <option value="amaal_and_namaz">Amaal and Namaz</option>
                    <option value="update">Update</option>
                    <option value="quote">Quote</option>
                </select>
            </div>

            {{-- Prayer Type --}}
            <div>
                <label class="block font-medium mb-1 text-[#034E7A]">Prayer Type</label>
                <select name="prayer_type" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                    <option value="30_min_before_fajr">30 min before Fajr</option>
                    <option value="fajr" selected>Fajr</option>
                    <option value="sunrise">Sunrise</option>
                    <option value="dhuhr">Dhuhr</option>
                    <option value="sunset">Sunset</option>
                    <option value="maghrib">Maghrib</option>
                    <option value="30_min_after_maghrib">30 min after Maghrib</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Day of Month --}}
            <div id="day_field" style="display: none;">
                <label class="block font-medium mb-1 text-[#034E7A]">Day of Month</label>
                <select name="day" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                    <option value="">Select Day</option>
                    @for ($i = 1; $i <= 31; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                </select>
            </div>

            {{-- Week Day --}}
            <div id="week_day_field" style="display: none;">
                <label class="block font-medium mb-1 text-[#034E7A]">Week Day</label>
                <select name="week_day" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>

            {{-- Month --}}
            <div id="month_field" style="display: none;">
                <label class="block font-medium mb-1 text-[#034E7A]">Month</label>
                <select name="month" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                    <option value="">Select Month</option>
                    <option value="january">Muharram</option>
                    <option value="february">Safar</option>
                    <option value="march">Rabi al Awwal</option>
                    <option value="april">Rabi al Thani</option>
                    <option value="may">Jumada al Awwal</option>
                    <option value="june">Jumada al Thani</option>
                    <option value="july">Rajab</option>
                    <option value="august">Sha Ban</option>
                    <option value="september">Ramadan</option>
                    <option value="october">Shawwal</option>
                    <option value="november">Dhul Qi Dah</option>
                    <option value="december">Dhul Hijjah</option>
                </select>
            </div>

            {{-- Year --}}
            <div id="year_field" style="display: none;">
                <label class="block font-medium mb-1 text-[#034E7A]">Year</label>
                <select name="year" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                    <option value="">Select Year</option>
                    @for ($i = 1445; $i <= 1500; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Title --}}
            <div class="md:col-span-2">
                <label class="block font-medium mb-1 text-[#034E7A]">Notification Title</label>
                <input type="text" name="notification_title" value="{{ old('notification_title') }}" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]" required placeholder="E.g. Time for Fajr Prayer">
            </div>

            {{-- Message --}}
            <div class="md:col-span-2">
                <label class="block font-medium mb-1 text-[#034E7A]">Notification Message</label>
                <textarea name="notification_message" rows="3" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]" required placeholder="Enter message here...">{{ old('notification_message') }}</textarea>
            </div>
        </div>
        <div>
            <label class="block font-medium mb-1 text-[#034E7A]">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-[#034E7A]">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('admin.prayertime-notifications.index') }}"
                class="px-5 py-2 border rounded text-gray-700 hover:bg-gray-100 transition">
                Cancel
            </a>
            <button type="submit" class="bg-[#034E7A] text-white px-8 py-2 rounded hover:bg-[#02629B] transition shadow-md">
                Schedule Notification
            </button>
        </div>
    </form>
</div>

<script>
    function toggleFormFields() {
        const freq = document.getElementById('frequency').value;
        const dayField = document.getElementById('day_field');
        const weekDayField = document.getElementById('week_day_field');
        const monthField = document.getElementById('month_field');
        const yearField = document.getElementById('year_field');

        // Reset
        dayField.style.display = 'none';
        weekDayField.style.display = 'none';
        monthField.style.display = 'none';
        yearField.style.display = 'none';

        if (freq === 'weekly') {
            weekDayField.style.display = 'block';
        } else if (freq === 'monthly') {
            dayField.style.display = 'block';
        } else if (freq === 'yearly') {
            dayField.style.display = 'block';
            monthField.style.display = 'block';
        } else if (freq === 'custom') {
            dayField.style.display = 'block';
            monthField.style.display = 'block';
            yearField.style.display = 'block';
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', toggleFormFields);
</script>
@endsection