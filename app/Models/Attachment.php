<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file_path',
        'original_name',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
