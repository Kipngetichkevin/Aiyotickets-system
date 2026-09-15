<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50">

    @include('includes.Header')

    <div class="max-w-2xl mx-auto min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="text-center py-16">
            <div class="mb-8 flex justify-center">
                <div class="bg-green-500 rounded-full p-3">
                    <svg class="w-16 h-16 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>

            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                Thank You!
            </h1>

            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
                Your purchase was successful! We've sent your ticket details to your email. Check your inbox and spam folder just in case.
            </p>

  

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('home') }}" 
                   class="py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-300">
                    Return Home
                </a>
                <!-- Order details route('orders.show', $orderId) -->
                <a href="#"
                   class="py-3 px-6 border border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg font-medium transition-all duration-300">
                    View Order Details
                </a>
            </div>
        </div>
    </div>

    @include('includes.Footer')

</body>
</html>