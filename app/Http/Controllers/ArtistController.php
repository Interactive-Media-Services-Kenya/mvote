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
        return Inertia::render('Admin/Artists', ['genres' => $genres]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'genre_id' => 'required|exists:genres,id',
            'bio' => 'required|string',
            'phone' => 'nullable|string',
        ]);
        Artist::create($validated);

        return redirect()->back()->with('success', 'Artist onboarded successfully!');
    }
}
