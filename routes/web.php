<?php

use App\Http\Controllers\PublicVideoFeed;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/videos/{hashedId}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/{hashedId}/stream.mp4', [VideoController::class, 'stream'])->name('videos.stream');
Route::geT('/videos/{hashedId}/thumbnail.jpg', [VideoController::class, 'thumbnail'])->name('videos.thumbnail');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('videos', VideoController::class)->only('store', 'destroy', 'update');
    Route::get('/my-videos', [VideoController::class, 'index'])->name('my-videos');
    Route::get('/', PublicVideoFeed::class)->name('dashboard');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
