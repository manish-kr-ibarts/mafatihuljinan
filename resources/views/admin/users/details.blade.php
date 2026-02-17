@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="h-screen bg-gray-100 p-4 flex items-start">
    <div class="bg-white rounded-xl shadow-lg w-full p-6 space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('admin.users') }}" class="px-4 py-2 bg-[#034E7A] text-white rounded hover:bg-gray-600"><i class="fas fa-arrow-left"></i> Back</a>

            <!-- Top Actions -->
            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-[#034E7A] text-white rounded hover:bg-gray-600">Edit User</a>
                <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete User</button>
                </form>
            </div>
        </div>


        <!-- User Info -->
        <div class="grid grid-cols-2 gap-6 border border-gray-200 rounded p-4 bg-gray-50">
            <div>
                <h3 class="text-gray-500 font-semibold">Name</h3>
                <p class="text-gray-800">{{ $user->name }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold">Email</h3>
                <p class="text-gray-800">{{ $user->email }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold">Email Verified</h3>
                <p class="text-gray-800">
                    @if($user->email_verified_at)
                    <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Verified</span>
                    @else
                    <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Not Verified</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold">Created At</h3>
                <p class="text-gray-800">{{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-' }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold">Updated At</h3>
                <p class="text-gray-800">{{ $user->updated_at ? $user->updated_at->format('Y-m-d H:i') : '-' }}</p>
            </div>
        </div>

        <!-- Bookmarked Posts -->
        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-xl font-bold text-[#034E7A] mb-2">Bookmarked Posts</h3>
            @if($GroupedBookmarkPosts->isEmpty())
            <p class="text-gray-600">No favorite posts found.</p>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($GroupedBookmarkPosts as $language => $bookmarks)
                <div class="bg-gray-50 rounded-lg p-4 shadow-sm border border-gray-200">
                    <h4 class="text-lg font-semibold text-[#026b9c] border-b border-gray-200 pb-1 mb-2">
                        {{ ucfirst($language) }}
                    </h4>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        @foreach($bookmarks as $bookmark)
                        <li>
                            {{ $bookmark->post->title ?? 'Untitled' }}
                            <span class="text-gray-500 text-sm">
                                ({{ str_replace('-', ' ', ucfirst($bookmark->post_type)) }})
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Favorite Posts -->
        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-xl font-bold text-[#034E7A] mb-2">Favorite Posts</h3>
            @if($GroupedFavorites->isEmpty())
            <p class="text-gray-600">No favorite posts found.</p>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($GroupedFavorites as $language => $favorites)
                <div class="bg-gray-50 rounded-lg p-4 shadow-sm border border-gray-200">
                    <h4 class="text-lg font-semibold text-[#026b9c] border-b border-gray-200 pb-1 mb-2">
                        {{ ucfirst($language) }}
                    </h4>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        @foreach($favorites as $favorite)
                        <li>
                            {{ $favorite->post->title ?? 'Untitled' }}
                            <span class="text-gray-500 text-sm">
                                ({{ str_replace('-', ' ', ucfirst($favorite->post_type)) }})
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <!-- Created Notes -->
        <div class="border-t border-gray-200 pt-4 pb-4">
            <h3 class="text-xl font-bold text-[#034E7A] mb-2">Created Notes</h3>
            @if($GroupedNotes->isEmpty())
            <p class="text-gray-600">No notes found.</p>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($GroupedNotes as $language => $notes)
                <div class="bg-gray-50 rounded-lg p-4 shadow-sm border border-gray-200">
                    <h4 class="text-lg font-semibold text-[#026b9c] border-b border-gray-200 pb-1 mb-2">
                        {{ ucfirst($language) }}
                    </h4>
                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                        @foreach($notes as $note)
                        <li>
                            <a href="javascript:void(0)"
                                class="text-blue-600 hover:underline font-medium"
                                data-title="{{ $note->title }}"
                                data-content="{{ $note->content }}"
                                data-language="{{ $note->language }}"
                                onclick="viewNote(this)">
                                {{ $note->title }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>

<!-- Note Details Modal -->
<div id="noteModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <div>
                <h3 id="modalTitle" class="text-2xl font-bold text-[#034E7A]">Note Title</h3>
                <p id="modalLanguage" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <button onclick="closeNoteModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal Body -->
        <div class="p-6">
            <div id="modalContent" class="text-gray-700 leading-relaxed whitespace-pre-wrap max-h-[60vh] overflow-y-auto pr-2">
                Note content goes here...
            </div>
        </div>
        <!-- Modal Footer -->
        <div class="flex justify-end p-6 border-t border-gray-100 bg-gray-50 rounded-b-xl">
            <button onclick="closeNoteModal()" class="px-6 py-2 bg-[#034E7A] text-white rounded-lg hover:bg-opacity-90 transition-all font-medium">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function viewNote(element) {
        const title = element.getAttribute('data-title');
        const content = element.getAttribute('data-content');
        const language = element.getAttribute('data-language');

        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalContent').innerText = content || 'No content available.';
        document.getElementById('modalLanguage').innerText = 'Language: ' + language.charAt(0).toUpperCase() + language.slice(1);

        document.getElementById('noteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }

    function closeNoteModal() {
        document.getElementById('noteModal').classList.add('hidden');
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }

    // Close on click outside
    document.getElementById('noteModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeNoteModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeNoteModal();
        }
    });
</script>
@endsection