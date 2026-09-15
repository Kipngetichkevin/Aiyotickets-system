<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
  <!-- Grid -->
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    
    <!-- Card for Current Events -->
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
      <div class="p-4 md:p-5">
        <div class="flex items-center gap-x-2">
          <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
            Current Events
          </p>
        </div>

        <div class="mt-1 flex items-center gap-x-2">
          <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200" id="current-events">
            {{ $currentEventsCount }} <!-- Display the count of current events -->
          </h3>
        </div>
      </div>
    </div>
    <!-- End Card -->

    <!-- Card for Tickets Sold -->
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
      <div class="p-4 md:p-5">
        <div class="flex items-center gap-x-2">
          <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
            Tickets Sold
          </p>
        </div>

        <div class="mt-1 flex items-center gap-x-2">
          <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200" id="tickets-sold">
          {{ $ticketsSold }} <!-- Display total tickets sold -->
          </h3>
        </div>
      </div>
    </div>
    <!-- End Card -->

    <!-- Card for Total Sales -->
    <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
      <div class="p-4 md:p-5">
        <div class="flex items-center gap-x-2">
          <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
            Total Sales
          </p>
        </div>

        <div class="mt-1 flex items-center gap-x-2">
          <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200" id="total-sales">
            KES {{ number_format($totalSales, 2) }} <!-- Display total sales -->
          </h3>
        </div>
      </div>
    </div>
    <!-- End Card -->

    <!-- Card for Net Sales -->
    <div class="flex flex-col bg-white border shadow-sm ```php
    rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
      <div class="p-4 md:p-5">
        <div class="flex items-center gap-x-2">
          <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
            Net Sales
          </p>
        </div>

        <div class="mt-1 flex items-center gap-x-2">
          <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200" id="net-sales">
            KES {{ number_format($netSales, 2) }} <!-- Display net sales -->
          </h3>
        </div>
      </div>
    </div>
    <!-- End Card -->
  </div>
  <!-- End Grid -->
</div>
