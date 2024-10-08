<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class BeginProcessingVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    public $timeout = 120;

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
        $sizes = config('videos.sizes', []);

        $jobs = [];

        foreach ($sizes as $name => $size) {
            $jobs[] = new ProcessVideoSize($this->video, $name);
        }

        $video = $this->video;

        $batch = Bus::batch($jobs)
            ->then(function () use ($video) {
                dispatch_sync(new UploadVideoToCloudStorage($video));
                $video->status = Video::STATUS_COMPLETE;
                $video->save();
            })
            ->catch(function ($e) use ($video) {
                logger()->error($e->getMessage());
                $video->status = Video::STATUS_ERROR;
                $video->save();
            })
            ->dispatch();

        $this->video->batch_id = $batch->id;
        $this->video->save();
    }
}
