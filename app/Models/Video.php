<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE = 'complete';

    protected $appends = ['thumbnail_url'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function thumbnailUrl(): Attribute
    {
        return new Attribute(fn () => "https://clipbin.ams3.cdn.digitaloceanspaces.com/videos/{$this->id}/thumbnail.mp4");
    }

    public function conversions() 
    {
        return $this->hasMany(Conversion::class);
    }
}
