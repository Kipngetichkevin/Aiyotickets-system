  @vite('resources/css/app.css')
  @include('backend.includes.header')
  @include('backend.includes.Sidebar')

  <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
      <!-- Content -->
      <div class="w-full lg:ps-64">
          @include('backend.includes.Grid')
          @include('backend.includes.Charts')
          @include('backend.includes.Card')


      </div>
      <!-- End Content -->
  </div>

  <!-- JS Implementing Plugins -->
  <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
