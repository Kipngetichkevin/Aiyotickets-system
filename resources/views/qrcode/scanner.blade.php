{{-- resources/views/qrcode/scanner.blade.php — the organiser's scanner (back office) --}}
@extends('layouts.app')

@section('title', 'Scan — ' . $title)

@section('content')
@include('qrcode._scanner')
@endsection
