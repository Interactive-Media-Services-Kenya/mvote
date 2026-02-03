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
                'status' => $artist->status,
                'lineup_order' => $artist->lineup_order,
                'scheduled_time' => $artist->scheduled_time ? $artist->scheduled_time->format('H:i') : '--:--',
            ];
        });

        return Inertia::render('Admin/Artists', [
            'genres' => $genres,
            'artists' => $artists->sortBy('lineup_order')->values()
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

        broadcast(new \App\Events\LineupUpdated())->toOthers();

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

        broadcast(new \App\Events\LineupUpdated())->toOthers();

        return redirect()->back();
    }

    public function destroy(Artist $artist)
    {
        // Delete local photo if it exists
        if ($artist->photo && strpos($artist->photo, '/storage/artists/') === 0) {
            \Storage::disk('public')->delete(str_replace('/storage/', '', $artist->photo));
        }

        $artist->delete();

        broadcast(new \App\Events\LineupUpdated())->toOthers();

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'artists' => 'required|array',
            'artists.*' => 'required|integer|exists:artists,id',
        ]);

        foreach ($validated['artists'] as $index => $id) {
            Artist::where('id', $id)->update(['lineup_order' => $index]);
        }

        // Broadcast event for real-time updates
        broadcast(new \App\Events\LineupUpdated())->toOthers();

        return back()->with('success', 'Lineup reordered successfully.');
    }
}
