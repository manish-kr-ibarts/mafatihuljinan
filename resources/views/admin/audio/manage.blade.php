@extends('layouts.admin')
@section('title', 'Manage Tracked Audio')

@section('content')

<!-- Tracked Audio Upload Section -->
<div class="mt-1">
    <div class="bg-white rounded shadow p-5 mb-6">
        <h2 class="text-lg font-semibold text-[#034E7A] mb-4">Upload and Track Audio File</h2>

        <!-- Upload Form -->
        <form action="{{ route('admin.audio.manage.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <div class="mb-4 flex items-center gap-4">
                    <div>
                        <div class="mb-4">
                            <label for="languageSelect" class="block text-sm font-medium text-[#034E7A] mb-2">Language:</label>
                            <select id="languageSelect" name="language"
                                onchange="changeLanguage(this.value)"
                                class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[#034E7A]">
                                @foreach(validLanguages() as $lang)
                                <option value="{{ $lang }}" {{ $language == $lang ? 'selected' : '' }}>
                                    {{ ucfirst($lang) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#034E7A] mb-2">Select Post Type</label>
                            <select name="post_type"
                                class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[#034E7A]"
                                required>
                                <option value="">Select Type</option>
                                @foreach(commonPostTypeOptions() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            @error('post_type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-[#034E7A] mb-2">Choose Audio File (MP3, WAV, AAC)</label>
                <input
                    type="file"
                    name="audio"
                    accept=".mp3, .wav, .aac"
                    class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[#034E7A]"
                    required>
                @error('audio')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button
                type="submit"
                class="bg-[#034E7A] text-white px-5 py-2 rounded hover:bg-[#02629B] transition">
                Upload & Track Audio
            </button>
        </form>
    </div>
</div>

<hr>

<!-- All Tracked Audio Files -->
@if($audioFiles->count() > 0)
<div class="bg-white rounded shadow p-5 mt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-[#034E7A]">All Tracked Audio Files ({{ ucfirst($language) }})</h2>

        <!-- Per Page Selector -->
        <div class="flex items-center gap-2">
            <label for="perPageSelect" class="text-sm text-gray-700">Show:</label>
            <select id="perPageSelect"
                onchange="changePerPage(this.value)"
                class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-[#034E7A]">
                <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-gray-700">per page</span>
        </div>
    </div>

    <!-- Audio Files Container -->
    <div id="audioFilesContainer">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-3 border">Filename / Type</th>
                        <th class="p-3 border">URL / Player</th>
                        <th class="p-3 border">Uploaded By</th>
                        <th class="p-3 border text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audioFiles as $file)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border">
                            <div class="font-medium text-[#034E7A]">{{ $file->file_name }}</div>
                            <div class="text-xs text-gray-500">Type: {{ $file->post_type }}</div>
                        </td>
                        <td class="p-3 border">
                            <div class="flex flex-col gap-2">
                                <audio controls class="h-8 w-64">
                                    <source src="{{ $file->url }}" type="audio/mpeg">
                                </audio>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs truncate max-w-xs text-gray-600">{{ $file->url }}</span>
                                    <button onclick="copyToClipboard('{{ $file->url }}')"
                                        class="text-[10px] px-2 py-0.5 rounded bg-blue-600 text-white hover:bg-blue-700 transition">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 border">
                            <div class="text-sm text-gray-700">{{ $file->user->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $file->created_at->format('M d, Y H:i') }}</div>
                        </td>
                        <td class="p-3 border text-center">
                            <form action="{{ route('admin.audio.manage.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Delete this audio file and its database record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-sm px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700 transition">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-6">
        {{ $audioFiles->appends(['language' => $language, 'per_page' => request('per_page', 25)])->links() }}
    </div>

</div>
@else
<div class="bg-white rounded shadow p-5 mt-6">
    <h2 class="text-lg font-semibold text-[#034E7A] mb-3">No Tracked Audio Files Found</h2>
    <p class="text-gray-600">Try changing the language or upload a new file.</p>
</div>
@endif

@endsection

<!-- Scripts -->
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert("URL copied to clipboard!");
        });
    }

    function changePerPage(perPage) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPage);
        currentUrl.searchParams.delete('page');
        window.location.href = currentUrl.toString();
    }

    function changeLanguage(lang) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('language', lang);
        currentUrl.searchParams.delete('page');
        window.location.href = currentUrl.toString();
    }
</script>