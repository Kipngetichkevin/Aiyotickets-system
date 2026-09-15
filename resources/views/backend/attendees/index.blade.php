@vite('resources/css/app.css')

@include('backend.includes.header')

@include('backend.includes.Sidebar')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

<!-- Content -->
<div class="w-full lg:ps-64">
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold">Attendee List</h2>

    <table class="table-auto w-full mt-4">
        <thead>
            <tr>
                <th>Attendee Name</th>
                <th>Ticket Type</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Payment Mode</th>
                <th>Transaction Code</th>
                <th>QR Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendees as $attendee)
                <tr>
                    <td>{{ $attendee->name }}</td>
                    <td>{{ $attendee->ticket->name }}</td>
                    <td>{{ $attendee->gender }}</td>
                    <td>{{ $attendee->phone }}</td>
                    <td>{{ $attendee->email }}</td>
                    <td>{{ $attendee->payment->payment_mode }}</td>
                    <td>{{ $attendee->payment->transaction_code }}</td>
                    <td><img src="{{ $attendee->qr_code }}" alt="QR Code" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    </div>
</div>

