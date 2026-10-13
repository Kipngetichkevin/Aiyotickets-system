<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        // Reports page not built yet (resources/views/reports doesn't exist)
        return redirect()->route('dashboard')->with('info', 'Reports are coming soon.');
    }
}