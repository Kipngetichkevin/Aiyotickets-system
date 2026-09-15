<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>

<body>

    @include('includes.Header')



    <div class="max-w-6xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <div class="bg-white rounded-lg shadow-lg light:bg-neutral-900">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-neutral-800">Create Event </h2>
                <p class="mt-2 text-gray-800 dark:text-neutral-800">Fill in the form below to create a new event.</p>
            </div>
            @include('includes.Messages')
            nn
            <form action="{{ route('event.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-6">
                    <div>
                        <label for="name"
                            class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Name</label>
                        <input type="text" name="name" id="name"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 light:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-800">
                    </div>
                    <div>
                        <label for="description"
                            class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Description</label>
                        <textarea name="description" id="description"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 light:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-800"></textarea>
                    </div>
                    <div>
                        <label for="date"
                            class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Date</label>
                        <input type="date" name="date" id="date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 light:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-800">
                    </div>
                    <div>
                        <label for="location"
                            class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Location</label>
                        <input type="text" name="location" id="location"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 light:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-800">
                    </div>
                    <div>
                        <label for="price"
                            class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Price</label>
                        <input type="number" name="price" id="price"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 light:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-800">
                    </div>
                    <div>
                        <label for="background_image"
                            class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Background
                            Image</label>
                        <input type="file" name="image" id="background_image"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 light:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-800">
                    </div>
                    <div>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-base leading-6 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring active:bg-blue-700 transition ease-in-out duration-150">
                            Create Event
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>


    @include('includes.Footer')

</body>

</html>
