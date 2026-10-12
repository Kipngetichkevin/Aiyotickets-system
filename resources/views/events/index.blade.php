@extends('layouts.app')

@section('content')
<!-- ===== Main Content Start ===== -->
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Events' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    @include('partials.table.table-events', ['events' => $events])

</main>
<!-- ===== Main Content End ===== -->
@endsection