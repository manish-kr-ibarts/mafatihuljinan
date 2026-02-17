@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="border rounded-2xl">
    <div class="w-full max-w-7xl mx-auto rounded-xl ">

        <!-- Header Title -->
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-lg sm:text-xl font-bold text-[#034E7A]">Users Management</h1>
            <button onclick="openCreateUserModal()" class="px-4 py-2 bg-[#034E7A] text-white rounded-lg hover:bg-[#023b5d] transition">
                <i class="fas fa-plus mr-2"></i> Add New User
            </button>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200 flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-x-4 sm:space-y-0">
            <!-- Search Input -->
            <input
                type="text"
                id="userSearch"
                placeholder="Search by Name or Email..."
                class="w-full sm:flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">

            <!-- Role Filter -->
            <select id="roleFilter" class="w-full sm:w-auto px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="subscriber">Subscriber</option>
            </select>
        </div>

        <!-- Horizontal Scroll Container -->
        <div class="overflow-x-auto p-4 m-2 my-4 border rounded-xl">
            <div class="min-w-max">
                <!-- Table Header -->
                <!-- Table Header -->
                <div class="rounded-xl p-1 grid grid-cols-[40px_150px_200px_80px_80px_120px_1fr] gap-0 bg-[#034E7A] text-white sticky top-0 z-10 text-xs sm:text-sm">
                    <div class="px-1 py-2 font-semibold truncate">#</div>
                    <div class="px-1 py-2 font-semibold truncate">Name</div>
                    <div class="px-1 py-2 font-semibold truncate">Email</div>
                    <div class="px-1 py-2 font-semibold truncate">Role</div>
                    <div class="px-1 py-2 font-semibold truncate">Verified</div>
                    <div class="px-1 py-2 font-semibold truncate">Created At</div>
                    <div class="px-1 py-2 font-semibold text-center">Actions</div>
                </div>

                <!-- Table Body -->
                <div class="divide-y divide-gray-200">
                    @forelse($users as $index => $user)
                    <div class="grid grid-cols-[40px_150px_200px_80px_80px_120px_1fr] gap-0 hover:bg-gray-50 items-center user-row text-xs sm:text-sm">
                        <div class="px-1 py-2 truncate">{{ $index + 1 }}</div>
                        <div class="px-1 py-2 font-medium text-gray-800 truncate">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="hover:underline text-blue-600">
                                {{ $user->name }}
                            </a>
                        </div>
                        <div class="px-1 py-2 text-gray-700 truncate break-all">
                            {{ $user->email }}
                        </div>
                        <div class="px-1 py-2 text-gray-700 truncate">{{ $user->role }}</div>
                        <div class="px-1 py-2">
                            @if($user->email_verified_at)
                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full whitespace-nowrap">Yes</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full whitespace-nowrap">No</span>
                            @endif
                        </div>
                        <div class="px-1 py-2 text-gray-600 truncate whitespace-nowrap">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</div>
                        <div class="px-1 py-2 flex items-center justify-center space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition whitespace-nowrap">Edit</a>
                            <!-- <a href="{{ route('admin.users.edit-role', $user->id) }}" class="text-blue-600 px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition whitespace-nowrap">Edit Role</a> -->
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure to delete this user: {{ $user->name }} ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 px-2 py-1 rounded bg-red-50 hover:bg-red-100 transition whitespace-nowrap">Delete</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-gray-500">
                        No users found.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h3 class="text-2xl font-bold text-[#034E7A]">Add New User</h3>
            <button onclick="closeCreateUserModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1" for="name">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('name')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1" for="email">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('email')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1" for="password">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10" required>
                            <button type="button" onclick="togglePasswordVisibility('password', 'password-icon-index')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
                                <i id="password-icon-index" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-gray-600 font-semibold mb-1" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10" required>
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'confirm-password-icon-index')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
                                <i id="confirm-password-icon-index" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="md:col-span-2">
                        <label class="block text-gray-600 font-semibold mb-1">Role</label>
                        <select name="role" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm" required>
                            @foreach(getAllRoles() as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeCreateUserModal()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#034E7A] text-white rounded-lg hover:bg-opacity-90 transition font-medium">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS Frontend Filter & Modal -->
<script>
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');

    function filterUsers() {
        const filterText = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value.toLowerCase();

        document.querySelectorAll('.user-row').forEach(row => {
            const name = row.children[1].textContent.toLowerCase();
            const email = row.children[2].textContent.toLowerCase();
            const role = row.children[3].textContent.toLowerCase();

            const matchesSearch = name.includes(filterText) || email.includes(filterText);
            const matchesRole = selectedRole === "" || role === selectedRole;

            row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);

    // Modal Logic
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    // Auto-open modal if validation errors exist
    if (document.getElementById('createUserModal').dataset.hasErrors === 'true') {
        openCreateUserModal();
    }

    // Close on backdrop click
    document.getElementById('createUserModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateUserModal();
    });
</script>
@endsection