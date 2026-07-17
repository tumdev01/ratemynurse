<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $table = 'images';

    protected $fillable = [
        'user_id',
        'imageable_id',
        'imageable_type',
        'name',
        'path',
        'filetype',
        'is_cover',
        'type'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    protected $appends = ['full_path'];

    /**
     * ให้ URL เต็มสำหรับรูปภาพ
     * 
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        // ถ้า path เป็น URL แล้ว ให้ return เลย
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        // ใช้ APP_URL เสมอ แทน url() ที่ยึดตาม Host header ของ request ปัจจุบัน — สำคัญตอน local dev
        // เพราะ WordPress ยิง request เข้ามาผ่าน docker-internal hostname (rmn_laravel_backend) ไม่ใช่
        // localhost:9000 ที่ browser เข้าถึงได้จริง ถ้าใช้ url() เฉยๆ จะฝัง hostname ที่ผิดลงไปใน path
        return rtrim(config('app.url'), '/') . '/' . ltrim($this->path, '/');
    }

    /**
     * Relationship: User
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic Relationship
     * 
     * @return MorphTo
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}