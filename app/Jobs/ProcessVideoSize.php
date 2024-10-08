<?php

namespace App\Jobs;

use App\Models\Video;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\FrameRate;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVideoSize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $video;
    protected $sizeName;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video, $name)
    {
        $this->video = $video;
        $this->sizeName = $name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->video->status = Video::STATUS_PROCESSING;
        $this->video->save();

        $sizes = config('videos.sizes');

        $ffmpeg = FFMpeg::create([
            'temporary_directory' => storage_path('app/temp'),
            'ffmpeg.binaries'  => config('services.ffmpeg.binary'),
            'ffprobe.binaries' => config('services.ffmpeg.probe_binary'),
            'timeout'          => 3600 // The timeout for the underlying process
        ]);

        $video = $ffmpeg->open(storage_path("app/" . $this->video->path));
        $video
            ->filters()
            ->resize(new Dimension($sizes[$this->sizeName]['width'], $sizes[$this->sizeName]['height']), ResizeFilter::RESIZEMODE_INSET, false)
            ->synchronize();

        $format = new X264();
        $format
            ->setPasses(2)
            ->setKiloBitrate($sizes[$this->sizeName]['bitrate'])
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(128)
            ->setAdditionalParameters([
                "-preset",
                "medium",
                "-movflags",
                "+faststart"
            ]);

        $video->save($format, storage_path("app/videos/{$this->video->hashed_id}/{$this->sizeName}.mp4"));

        $this->video->conversions()->create([
            'name' => $this->sizeName,
            'path' => "videos/{$this->video->hashed_id}/{$this->sizeName}.mp4",
            'size' => filesize(storage_path("app/videos/{$this->video->hashed_id}/{$this->sizeName}.mp4"))
        ]);

        $this->video->touch();
    }
}
