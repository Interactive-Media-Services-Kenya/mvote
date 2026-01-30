<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LineupController extends Controller
{
    public function index()
    {
        $event = Event::with('questions')->where('is_active', true)->latest()->first();
        
        return Inertia::render('Lineup', [
            'event' => $event
        ]);
    }

    public function artist($id)
    {
        $event = Event::with('questions')->where('is_active', true)->latest()->first();
        
        return Inertia::render('ArtistProfile', [
            'id' => $id,
            'event' => $event
        ]);
    }
}
