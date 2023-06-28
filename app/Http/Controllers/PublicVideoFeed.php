<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class PublicVideoFeed extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $videos = Video::with('creator:id,name', 'conversions')->where('public', true)->where('status', 'complete')->orderByDesc('id')->paginate(12);
        $title = 'Feed';

        return inertia('Feed', compact('videos', 'title'));
    }
}
