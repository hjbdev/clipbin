<?php

namespace App\Observers;

use App\Events\VideoDeleted;
use App\Events\VideoUpdated;
use App\Models\Video;
use Vinkla\Hashids\Facades\Hashids;

class VideoObserver
{
    /**
     * Handle the Video "created" event.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function created(Video $video)
    {
        $video->hashed_id = Hashids::connection('video')->encode($video->id);
        $video->saveQuietly();
    }

    /**
     * Handle the Video "updated" event.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function updated(Video $video)
    {
        broadcast(new VideoUpdated($video));
    }

    /**
     * Handle the Video "deleted" event.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function deleted(Video $video)
    {
        broadcast(new VideoDeleted($video));
    }

    /**
     * Handle the Video "restored" event.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function restored(Video $video)
    {
        //
    }

    /**
     * Handle the Video "force deleted" event.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function forceDeleted(Video $video)
    {
        //
    }
}
