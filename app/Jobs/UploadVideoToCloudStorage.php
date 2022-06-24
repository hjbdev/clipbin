<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadVideoToCloudStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Move thumbnail
        $disk = Storage::disk('do');
        $disk->putFileAs("videos/{$this->video->hashed_id}", new File(storage_path('app/') . $this->video->thumbnail), 'thumbnail.jpg');

        // move conversions
        foreach ($this->video->conversions as $conversion) {
            $disk->putFileAs("videos/{$this->video->hashed_id}", new File(storage_path('app/') . $conversion->path), "{$conversion->name}.mp4");
        }

        Storage::disk('local')->deleteDirectory('videos/' . $this->video->hashed_id);
    }
}
