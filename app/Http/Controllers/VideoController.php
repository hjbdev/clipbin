<?php

namespace App\Http\Controllers;

use App\Jobs\BeginProcessingVideo;
use App\Models\Video;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Frame\CustomFrameFilter;
use FFMpeg\Filters\Frame\FrameFilters;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Vinkla\Hashids\Facades\Hashids;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $videos = Video::whereCreatedBy(auth()->id())->with('incompleteBatch')->orderByDesc('id')->get();
        return Inertia::render('Dashboard', compact('videos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'resumableType' => 'required|in:video/mp4',
        ]);

        // create the file receiver
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        // receive the file
        $save = $receiver->receive();



        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need, current example uses `move` function. If you are
            // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
            return $this->saveFileAndCreateVideo($save->getFile());
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
        ]);
    }

    /**
     * Saves the file
     *
     * @param UploadedFile $file
     *
     * @return JsonResponse
     */
    protected function saveFileAndCreateVideo(UploadedFile $file)
    {
        $fileName = $file->getClientOriginalName();

        $video = new Video();
        $video->title = $fileName;
        $video->creator()->associate(auth()->id());
        $video->save();

        $fileName = 'original.' . $file->getClientOriginalExtension();

        // Build the file path
        $filePath = "videos/{$video->hashed_id}/";
        $finalPath = storage_path("app/" . $filePath);

        // move the file name
        $file->move($finalPath, $fileName);

        $ffmpeg = FFMpeg::create([
            'temporary_directory' => storage_path('app/temp'),
            'ffmpeg.binaries'  => env('FFMPEG_BINARY'),
            'ffprobe.binaries' => env('FFPROBE_BINARY'),
            'timeout'          => 3600 // The timeout for the underlying process
        ]);

        $ffmpeg->open($finalPath . $fileName)
            ->frame(TimeCode::fromSeconds(0))
            ->addFilter(new CustomFrameFilter('scale=320:-1'))
            ->save($finalPath . 'thumbnail.jpg');

        // Upload thumbnail straight to cloud
        $disk = Storage::disk('do');
        $disk->putFileAs("videos/{$video->hashed_id}", new File($finalPath . 'thumbnail.jpg'), 'thumbnail.jpg');
        Storage::disk('local')->delete($finalPath . 'thumbnail.jpg');

        // create the video
        $video->path = $filePath . $fileName;
        $video->thumbnail = $filePath . 'thumbnail.jpg';
        $video->save();

        dispatch(new BeginProcessingVideo($video));

        return response()->json($video);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($hashedId)
    {
        $video = Video::with('conversions')->findOrFail(Hashids::connection('video')->decode($hashedId))->first();
        return Inertia::render('Videos/Show', compact('video'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function edit(Video $video)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Video $video)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function destroy(Video $video)
    {
        //
    }

    public function thumbnail($videoId)
    {
        $video = Video::findOrFail($videoId);
        return response()->file(storage_path("app/{$video->thumbnail}"));
    }
}
