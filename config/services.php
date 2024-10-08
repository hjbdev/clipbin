<?php

return [
    'ffmpeg' => [
        'binary' => env('FFMPEG_BINARY', '/usr/bin/ffmpeg'),
        'probe_binary' => env('FFPROBE_BINARY', '/usr/bin/ffprobe'),
    ]
];
