{{-- resources/views/qrcode/gate.blade.php — the scanner as door staff see it through a gate link --}}
@extends('layouts.gate')

@section('title', $title)

@section('content')
@include('qrcode._scanner', ['standalone' => true])
@endsection
