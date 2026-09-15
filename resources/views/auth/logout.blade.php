<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Include your CSS -->
</head>
<body>
    <div class="text-center">
        <!-- Button to open the logout modal -->
        <button type="button" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" aria-haspopup="dialog" aria-expanded="false" aria-controls="logout-modal" data-hs-overlay="#logout-modal">
            Logout
        </button>
    </div>

    <!-- Logout Modal -->
    <div id="logout-modal" class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto" role="dialog" tabindex="-1" aria-labelledby="logout-modal-label">
        <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-neutral-900 dark:border-neutral-800">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h3 id="logout-modal-label" class="block text-2xl font-bold text-gray-800 dark:text-neutral-200">Logout</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                            Are you sure you want to log out?
                        </p>
                    </div>

                    <div class="mt-5">
                        <!-- Logout Form -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <div class="grid gap-y-4">
                                <button type="submit" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                                    Logout
                                </button>
                            </div>
                        </form>
                        <!-- End Logout Form -->

                        <div class="mt-4 text-center">
                            <a href="{{ url('/') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include any necessary JavaScript to handle modal interactions -->
    <script>
        // Add JavaScript here to handle the modal display logic if not using a JavaScript library that does this for you
    </script>
</body>
</html>
