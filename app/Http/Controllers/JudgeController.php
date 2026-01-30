<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JudgeController extends Controller
{
    public function index()
    {
        $judges = User::whereHas('role', function ($query) {
            $query->where('name', 'judge');
        })
            ->with('role')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nick_name' => $user->nick_name ?? 'Anonymous Judge',
                    'phone' => $user->phone,
                    'role' => $user->role->name ?? 'Judge',
                    'status' => 'active', // Default for now
                    'invites' => $user->votes()->count(), // Using votes as a proxy for "dispatches" or activity
                ];
            });

        return Inertia::render('Admin/Judges', ['judges' => $judges]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(?:254|\+254|0)?(7|1)(?:(?:[0-9][0-9])|(?:0[0-8]))[0-9]{6}$/'],
            'nick_name' => ['nullable', 'string', 'max:20'],
        ]);

        $phone = $request->phone;
        // Normalize phone to international format without +
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        $judgeRole = Role::where('name', 'judge')->first();

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'nick_name' => $request->nick_name,
                'role_id' => $judgeRole->id,
            ]
        );

        return back();

    }
}
