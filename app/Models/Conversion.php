<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'size'
    ];

    protected $appends = ['url'];
    protected $hidden = ['id'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function url(): Attribute
    {
        return new Attribute(
            fn() =>
            Storage::disk(app()->isProduction() ? 'do' : 'public')->{app()->isProduction() ? 'temporaryUrl' : 'url'}("videos/{$this->video->hashed_id}/{$this->name}.mp4", now()->addMinute())
        );
    }
}
