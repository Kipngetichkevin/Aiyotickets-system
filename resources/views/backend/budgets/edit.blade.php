@vite('resources/css/app.css')

@include('backend.includes.Header')

@include('backend.includes.Sidebar')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

<!-- Content -->
<div class="w-full lg:ps-64">
<!-- Card Section -->
<div class="max-w-4xl px-4 py-10 sm:px-6 lg:px-8 mx-auto"><!-- Card -->
  <div class="bg-white rounded-xl shadow p-4 sm:p-7 dark:bg-neutral-800">
    <div class="mb-8">
      <h2 class="text-xl font-bold text-gray-800 dark:text-neutral-200">
        Edit Budget
      </h2>
    </div>

    <form action="{{ route('backend.budgets.update', $budget->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- Grid -->
      <div class="grid sm:grid-cols-12 gap-2 sm:gap-6">
        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Name
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="name" name="name" value="{{ $budget->name }}" required>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Point of Contact
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="point_of_contact" name="point_of_contact" value="{{ $budget->point_of_contact }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Phone
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="phone" name="phone" value="{{ $budget->phone }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Email
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="email" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="email" name="email" value="{{ $budget->email }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Address
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="address" name="address" value="{{ $budget->address }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Role
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="role" name="role" value="{{ $budget->role }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Category
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <select class="py-2 px-3 pe-9 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="category" name="category">
            <option value="personnel" {{ $budget->category == 'personnel' ? 'selected' : '' }}>Personnel</option>
            <option value="supplier" {{ $budget->category == 'supplier' ? 'selected' : '' }}>Supplier</option>
            <option value="debtor" {{ $budget->category == 'debtor' ? 'selected' : '' }}>Debtor</option>
            <option value="creditor" {{ $budget->category == 'creditor' ? 'selected' : '' }}>Creditor</option>
          </select>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Cost
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" step="0.01" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="cost" name="cost" value="{{ $budget->cost }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Contract Signature
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="text" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="contract_agreement_signature" name="contract_agreement_signature" value="{{ $budget->contract_agreement_signature }}">
        </div>
        <!-- End Col -->

        <div class="sm:col-span-3">
          <label class="inline-block text-sm text-gray-800 mt-2.5 dark:text-neutral-200">
            Logo/Personnel Image
          </label>
        </div>
        <!-- End Col -->

        <div class="sm:col-span-9">
          <input type="file" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm text-sm rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" id="logo" name="logo">
          @if($budget->logo)
            <img src="{{ Storage::url('logos/' . $budget->profile_pic) }}" alt="Logo" width="50">
          @endif
        </div>
        <!-- End Col -->
      </div>
      <!-- End Grid -->

      <div class="mt-5 flex justify-end gap-x-2">
        <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
          Cancel
        </button>
        <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
          Update
        </button>
      </div>
    </form>
  </div>
</div>

</div>
</div>

<!-- Required plugins -->
<script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>

  