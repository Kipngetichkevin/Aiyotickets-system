<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class ReportController extends Controller
{
    public function index()
    {
        // Render the reports page
        return view('reports.index');
    }
}