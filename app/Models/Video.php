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
    const STATUS_ERROR = 'error';

    protected $appends = ['thumbnail_url'];
    protected $casts = [
        'public' => 'boolean'
    ];

    protected $hidden = ['id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function thumbnailUrl(): Attribute
    {
        return new Attribute(fn () => "https://clipbin.ams3.cdn.digitaloceanspaces.com/videos/{$this->hashed_id}/thumbnail.jpg");
    }

    public function conversions() 
    {
        return $this->hasMany(Conversion::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id', null);
    }

    public function incompleteBatch()
    {
        return $this->batch()->whereNull('finished_at');
    }
}
