@vite('resources/css/app.css')

@include('backend.includes.header')

@include('backend.includes.Sidebar')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <div class="w-full lg:ps-64">
<div class="container">
    <h2 class="text-2xl font-bold">Budget Details for {{ $budget->name }}</h2>
    <div>
        <p><strong>Point of Contact:</strong> {{ $budget->point_of_contact }}</p>
        <p><strong>Phone:</strong> {{ $budget->phone }}</p>
        <p><strong>Email:</strong> {{ $budget->email }}</p>
        <p><strong>Address:</strong> {{ $budget->address }}</p>
        <p><strong>Role:</strong> {{ $budget->role }}</p>
        <p><strong>Category:</strong> {{ $budget->category }}</p>
        <p><strong>Contract Signature:</strong> {{ $budget->contract_signature }}</p>
        <p><strong>Cost:</strong> KES {{ $budget->cost }}</p>
    </div>

    <h3 class="text-xl font-semibold mt-4">Associated Services</h3>
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th>Service</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budget->services as $service)
            <tr>
                <td>{{ $service->service }}</td>
                <td>KES {{ $service->price }}</td>
                <td>{{ $service->qty }}</td>
                <td>{{ $service->delivery_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('backend.includes.Footer')
    </div>
</div>
<!-- Required plugins -->
<script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>