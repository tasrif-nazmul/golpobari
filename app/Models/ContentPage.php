<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContentPage extends Model
{
    protected $fillable = ['slug', 'title', 'body', 'meta_description'];

    /** Markdown rendered with all raw HTML stripped and unsafe links removed. */
    public function html(): string
    {
        return Str::markdown($this->body ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
