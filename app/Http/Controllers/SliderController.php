<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event; // Assuming your model is named Event

class SliderController extends Controller
{
    public function index()
    {
        // Fetch events from the database with only the specified fields
        $events = Event::select('id', 'name', 'date', 'location', 'image')->get(); 
        return view('slider', compact('events'));
    }
}
