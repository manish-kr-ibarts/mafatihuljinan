@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="border rounded-2xl">
    <div class="w-full max-w-7xl mx-auto rounded-xl ">

        <!-- Header Title -->
        <div class="p-4 border-b border-gray-200">
            <h1 class="text-lg sm:text-xl font-bold text-[#034E7A]">Activity Logs</h1>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200 flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-x-4 sm:space-y-0">
            <!-- Search Input -->
            <input
                type="text"
                id="logSearch"
                placeholder="Search by User, Action, or Description..."
                class="w-full sm:flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        </div>

        <!-- Horizontal Scroll Container -->
        <div class="overflow-x-auto p-4 m-2 my-4 border rounded-xl">
            <div class="min-w-max">
                <!-- Table Header -->
                <div class="rounded-xl p-1 grid grid-cols-[40px_150px_150px_1fr_120px_150px] gap-0 bg-[#034E7A] text-white sticky top-0 z-10 text-xs sm:text-sm">
                    <div class="px-1 py-2 font-semibold truncate">#</div>
                    <div class="px-1 py-2 font-semibold truncate">User</div>
                    <div class="px-1 py-2 font-semibold truncate">Action</div>
                    <div class="px-1 py-2 font-semibold truncate">Description</div>
                    <div class="px-1 py-2 font-semibold truncate">IP Address</div>
                    <div class="px-1 py-2 font-semibold truncate">Date</div>
                </div>

                <!-- Table Body -->
                <div class="divide-y divide-gray-200">
                    @forelse($logs as $index => $log)
                    <div class="grid grid-cols-[40px_150px_150px_1fr_120px_150px] gap-0 hover:bg-gray-50 items-center log-row text-xs sm:text-sm">
                        <div class="px-1 py-2 truncate">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</div>
                        <div class="px-1 py-2 font-medium text-gray-800 truncate">
                            @if($log->user)
                            <a href="{{ route('admin.users.show', $log->user->id) }}" class="hover:underline text-blue-600">
                                @if( Auth::user()->id == $log->user->id)
                                You
                                @else
                                {{ $log->user->name }}
                                @endif
                            </a>
                            @else
                            <span class="text-gray-500">Unknown User</span>
                            @endif
                        </div>
                        <div class="px-1 py-2 text-gray-700 truncate">{{ $log->action }}</div>
                        <div class="px-1 py-2 text-gray-700 break-words">{{ $log->description }}</div>
                        <div class="px-1 py-2 text-gray-700 truncate">{{ $log->ip_address }}</div>
                        <div class="px-1 py-2 text-gray-600 truncate whitespace-nowrap">{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}</div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-gray-500">
                        No activity logs found.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- JS Frontend Filter -->
<script>
    const searchInput = document.getElementById('logSearch');

    function filterLogs() {
        const filterText = searchInput.value.toLowerCase();

        document.querySelectorAll('.log-row').forEach(row => {
            const user = row.children[1].textContent.toLowerCase();
            const action = row.children[2].textContent.toLowerCase();
            const description = row.children[3].textContent.toLowerCase();

            const matchesSearch = user.includes(filterText) || action.includes(filterText) || description.includes(filterText);

            row.style.display = matchesSearch ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterLogs);
</script>
@endsection