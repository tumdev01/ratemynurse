<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Image extends Model
{
    protected $table = 'images';

    protected $fillable = [
        'user_id', 'type', 'name', 'path', 'filetype', 'is_cover'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    protected $appends = ['full_path'];

    public function getFullPathAttribute()
    {
        return url($this->path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imageable()
    {
        return $this->morphTo();
    }

}
