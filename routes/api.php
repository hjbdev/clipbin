<?php

use App\Http\Controllers\VideoController;
use App\Models\Conversion;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/videos/{video}/conversions/{conversion}', function (Video $video, Conversion $conversion) {
    return response()->download(storage_path('app/' . $conversion->path));
});

Route::get('/videos/{video}/thumbnail', [VideoController::class, 'thumbnail']);
