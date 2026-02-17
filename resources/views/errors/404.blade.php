<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Mafatihuljinan</title>
    <link rel="icon" href="{{ asset('storage/website/mafa-logo.jpg') }}" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind & TinyMCE -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6 font-sans antialiased">
    <div class="m-5 w-full text-center">
        <!-- Logo -->
        <div class="mb-10 flex justify-center">
            <a href="{{ url('/') }}" class="inline-flex items-center space-x-3 group">
                <img src="{{ asset('storage/website/mafa-logo.jpg') }}" alt="Mafatihuljinan Logo" class="w-16 h-16 rounded-xl shadow-lg group-hover:scale-105 transition-transform duration-300">
                <span class="text-3xl font-bold text-gray-800 tracking-tight">Mafatihuljinan</span>
            </a>
        </div>

        <!-- 404 Illustration Area -->
        <div class="relative mb-8">
            <h1 class="text-[12rem] font-extrabold text-gray-500 select-none leading-none">404</h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="animate-bounce">
                    <i class="fas fa-search-location text-7xl text-[#034E7A] drop-shadow-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-4">
            <h2 class="text-4xl font-bold text-gray-900">Oops! Page not found</h2>
        </div>

        <!-- Actions -->
        <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="bg-[#034E7A] hover:bg-[#023a5c] text-white px-8 py-3.5 rounded-full font-semibold shadow-xl shadow-[#034E7A]/20 transition-all duration-300 transform hover:-translate-y-1 flex items-center">
                <i class="fas fa-home mr-2"></i>
                Go Back Home
            </a>
            <button onclick="window.history.back()" class="px-8 py-3.5 rounded-full font-semibold text-gray-700 border-2 border-gray-200 hover:border-[#034E7A] hover:text-[#034E7A] transition-all duration-300 bg-white">
                <i class="fas fa-arrow-left mr-2"></i>
                Previous Page
            </button>
        </div>
    </div>
</body>

</html>