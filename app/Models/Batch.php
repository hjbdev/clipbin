<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'job_batches';
    protected $keyType = 'string';
    public $incrementing = false;

    use HasFactory;
}
