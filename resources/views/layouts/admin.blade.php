<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Dynamic Page Title -->
    <title>@yield('title', 'Default Page Title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('storage/website/mafa-logo.jpg') }}" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind & TinyMCE -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <script src="{{ asset('vendor/tinymce/js/tinymce/tinymce.min.js') }}"></script>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen relative bg-gray-50 font-sans antialiased">

        <!-- Sidebar (hidden by default on mobile + tablet) -->
        <aside id="sidebar"
            class="fixed xl:static top-0 left-0 z-50 w-64 h-full bg-white border-r border-gray-200 shadow-xl xl:shadow-none transform -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">

            <!-- Close button (only visible on mobile/tablet) -->
            <div class="flex justify-end p-4 border-b xl:hidden">
                <button id="closeSidebar" class="text-gray-500 hover:text-gray-900 text-2xl focus:outline-none transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <x-sidebar />
        </aside>

        <!-- Overlay ({mobile/tablet) -->
        <div id="overlay" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-40 transition-opacity"></div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto w-full">
            <!-- Header -->
            <div class="sticky top-0 z-30 flex items-center justify-between px-6 py-4 bg-white/90 backdrop-blur-md border-b border-gray-200 mb-6 transition-all">
                <!-- Hamburger for mobile/tablet -->
                <button id="openSidebar" class="xl:hidden text-gray-500 hover:text-primary text-2xl focus:outline-none transition-colors mr-4">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="flex-1">
                    <x-header :page-name="View::yieldContent('title', 'Admin Dashboard')" />
                </div>
            </div>

            <div class="px-6 pb-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r shadow-sm mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r shadow-sm mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const openSidebar = document.getElementById('openSidebar');
            const closeSidebar = document.getElementById('closeSidebar');

            // Function to open sidebar
            const openMenu = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            };

            // Function to close sidebar
            const closeMenu = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            };

            // Event listeners
            openSidebar?.addEventListener('click', openMenu);
            closeSidebar?.addEventListener('click', closeMenu);
            overlay?.addEventListener('click', closeMenu);
        });
    </script>
</body>

</html>