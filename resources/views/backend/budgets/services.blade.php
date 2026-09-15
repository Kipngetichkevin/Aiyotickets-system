<h2 class="text-xl font-semibold">Services</h2>

<table class="table-auto w-full mt-4">
    <thead>
        <tr>
            <th class="px-4 py-2">Service</th>
            <th class="px-4 py-2">Price</th>
            <th class="px-4 py-2">Quantity</th>
            <th class="px-4 py-2">Delivery Date</th>
            <th class="px-4 py-2">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($budget->services as $service)
            <tr>
                <td class="border px-4 py-2">{{ $service->service }}</td>
                <td class="border px-4 py-2">{{ $service->price }}</td>
                <td class="border px-4 py-2">{{ $service->qty }}</td>
                <td class="border px-4 py-2">{{ $service->delivery_date }}</td>
                <td class="border px-4 py-2">{{ $service->total_cost }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="mt-4 text-lg font-semibold">Grand Total Cost: {{ $budget->grand_total_cost }}</p>
