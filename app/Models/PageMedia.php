<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageMedia extends Model
{
    protected $table = 'page_media';

    protected $fillable = [
        'order_id', 'page_id', 'draft_token', 'field_key', 'path', 'thumb_path', 'mime',
        'original_name', 'size', 'width', 'height', 'sort_order', 'metadata',
    ];

    protected $hidden = ['draft_token', 'path', 'thumb_path'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function url(bool $thumb = false): string
    {
        return route('media.show', ['media' => $this->id, 'variant' => $thumb ? 'thumb' : 'full', 'v' => $this->updated_at?->timestamp]);
    }

    public function caption(): ?string
    {
        return data_get($this->metadata, 'caption');
    }
}
