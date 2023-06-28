<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id)->only(['id', 'name']);
        $videos = Video::with('creator:id,name', 'conversions')
            ->where('public', true)
            ->where('status', 'complete')
            ->whereCreatedBy($id)
            ->orderByDesc('id')
            ->paginate(12);

        $title = $user['name'] . '\'s Videos';

        return inertia('Feed', compact('user', 'videos', 'title'));
    }
}
