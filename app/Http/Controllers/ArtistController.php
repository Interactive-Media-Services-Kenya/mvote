<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArtistController extends Controller
{
    public function index()
    {
        $genres = Genre::all();
        $artists = Artist::with('genre')->get()->map(function ($artist) {
            return [
                'id' => $artist->id,
                'name' => $artist->name,
                'genre' => $artist->genre->title ?? 'Unknown',
                'bio' => $artist->bio,
                'image' => $artist->photo ?? 'https://via.placeholder.com/150',
                'status' => $artist->is_active ? 'onboarded' : 'inactive',
            ];
        });

        return Inertia::render('Admin/Artists', [
            'genres' => $genres,
            'artists' => $artists
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'genre_id' => 'required|exists:genres,id',
            'bio' => 'required|string',
            'phone' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);
        
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('artists', 'public');
            $validated['photo'] = '/storage/' . $path;
        } else {
            $validated['photo'] = "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($validated['name']);
        }
        
        Artist::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'genre_id' => 'required|exists:genres,id',
            'bio' => 'required|string',
            'phone' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Optional: Delete old photo if it exists locally
            if ($artist->photo && strpos($artist->photo, '/storage/artists/') === 0) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $artist->photo));
            }

            $path = $request->file('photo')->store('artists', 'public');
            $validated['photo'] = '/storage/' . $path;
        }

        $artist->update($validated);

        return redirect()->back();
    }
}
