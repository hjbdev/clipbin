<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">

    @if (Request::route()->getName() === 'videos.show')
    @php
    $video = \App\Models\Video::findOrFail(Vinkla\Hashids\Facades\Hashids::connection('video')->decode(request()->route('hashedId')))->first();
    @endphp
    <meta property="og:title" content="{{ $video->title }}">
    <meta property="og:type" content="video">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $video->thumbnail_url }}">
    <meta property="og:video" content="{{ route('videos.stream', $video->hashed_id) }}">
    <meta property="og:video:width" content="1280">
    <meta property="og:video:height" content="720">
    <meta property="og:video:type" content="video/mp4">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>