<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'path', 'size'
    ];

    protected $appends = ['url'];
    protected $hidden = ['id'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function url() : Attribute
    {
        return new Attribute(fn () => "https://clipbin.ams3.cdn.digitaloceanspaces.com/videos/{$this->video_id}/{$this->size}.mp4");
    }
}
