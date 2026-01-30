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
        $artists = \App\Models\Artist::with('genre')->where('is_active', true)->get()->map(function($artist) {
            return [
                'id' => $artist->id,
                'name' => $artist->name,
                'genre' => $artist->genre->title ?? 'Unknown',
                'image' => $artist->photo ?? 'https://via.placeholder.com/150',
                'status' => 'upcoming', // This should be dynamic later
                'voteCount' => 0, // This should be dynamic later
            ];
        });
        
        return Inertia::render('Lineup', [
            'event' => $event,
            'artists' => $artists
        ]);
    }

    public function artist($id)
    {
        $event = Event::with('questions')->where('is_active', true)->latest()->first();
        $artistModel = \App\Models\Artist::with('genre')->findOrFail($id);
        
        $artist = [
            'id' => $artistModel->id,
            'name' => $artistModel->name,
            'genre' => $artistModel->genre->title ?? 'Unknown',
            'image' => $artistModel->photo ?? 'https://via.placeholder.com/150',
            'bio' => $artistModel->bio,
            'scheduledTime' => '21:00', // Placeholder
            'discography' => [], // Placeholder for now
        ];

        return Inertia::render('ArtistProfile', [
            'artist' => $artist,
            'event' => $event
        ]);
    }
}
