<?php

namespace App\Http\Controllers;

use App\Jobs\BeginProcessingVideo;
use App\Models\Video;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Frame\CustomFrameFilter;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
        $videos = Video::whereCreatedBy(auth()->id())->with('incompleteBatch')->orderByDesc('id')->paginate(12);
        $title = 'Your Videos';
        return inertia('Dashboard', compact('videos', 'title'));
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
            'resumableType' => 'required|in:video/mp4,video/x-matroska,video/quicktime',
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

        if (request()->has('originally_created_at')) {
            $originallyCreatedAt = Carbon::createFromTimestampMs(request()->get('originally_created_at'));
        }

        $video = new Video();
        $video->title = $fileName;
        $video->originally_created_at = $originallyCreatedAt ?? null;
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
            ->addFilter(new CustomFrameFilter('scale=640:-1'))
            ->save($finalPath . 'thumbnail.jpg');

        // Upload thumbnail straight to cloud
        $disk = Storage::disk(app()->isProduction() ? 'do' : 'public');
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
        $video = Video::with('conversions', 'creator:id,name')->findOrFail(Hashids::connection('video')->decode($hashedId))->first();
        return Inertia::render('Videos/Show', compact('video'));
    }

    public function stream($hashedId)
    {
        $video = Video::with('conversions')->findOrFail(Hashids::connection('video')->decode($hashedId))->first();
        $conversion = $video->conversions->first();
        $disk = Storage::disk(app()->isProduction() ? 'do' : 'public');
        $stream = $disk->readStream("videos/{$video->hashed_id}/{$conversion->name}.mp4");
        
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'video/mp4',
            'Content-Length' => $disk->size("videos/{$video->hashed_id}/{$conversion->name}.mp4"),
        ]);
    }

    public function thumbnail($hashedId)
    {
        $video = Video::findOrFail(Hashids::connection('video')->decode($hashedId))->first();
        return Storage::disk(app()->isProduction() ? 'do' : 'public')->response("videos/{$video->hashed_id}/thumbnail.jpg");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $hashedId)
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
    public function update(Request $request, $hashedId)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'public' => 'nullable|boolean'
        ]);
        $video = Video::whereCreatedBy(auth()->id())->whereId(Hashids::connection('video')->decode($hashedId))->firstOrFail();
        if ($request->get('title')) {
            $video->title = $request->get('title');
        }
        if ($request->has('public')) {
            $video->public = $request->get('public');
        }
        $video->save();
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function destroy($hashedId)
    {
        $video = Video::whereCreatedBy(auth()->id())->whereId(Hashids::connection('video')->decode($hashedId))->firstOrFail();

        Storage::disk('local')->deleteDirectory('videos/' . $video->hashed_id);
        if (env('APP_ENV') === 'production') {
            Storage::disk(app()->isProduction() ? 'do' : 'public')->deleteDirectory('videos/' . $video->hashed_id);
        }

        $video->delete();

        return redirect()->to('/my-videos');
    }
    // public function thumbnail($videoId)
    // {
    //     $video = Video::findOrFail($videoId);
    //     return response()->file(storage_path("app/{$video->thumbnail}"));
    // }
}
