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

    protected $appends = ['thumbnail_url', 'created_at_ago'];
    protected $casts = [
        'public' => 'boolean',
        'created_at' => 'datetime'
    ];

    protected $hidden = ['id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdAtAgo(): Attribute
    {
        return new Attribute(fn() => $this->created_at->diffForHumans());
    }

    public function thumbnailUrl(): Attribute
    {
        return new Attribute(
            fn() =>
            Storage::disk(app()->isProduction() ? 'do' : 'public')->{app()->isProduction() ? 'temporaryUrl' : 'url'}("videos/{$this->hashed_id}/thumbnail.jpg", now()->addMinute())
        );
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
