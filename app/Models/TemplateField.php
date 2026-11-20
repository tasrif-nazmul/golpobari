<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateField extends Model
{
    public const TYPES = [
        'text' => 'Short text',
        'textarea' => 'Long text',
        'date' => 'Date',
        'email' => 'Email',
        'url' => 'Link (URL)',
        'phone' => 'Phone',
        'select' => 'Dropdown',
        'color' => 'Colour',
        'repeater' => 'List of items',
        'image' => 'Single photo',
        'gallery' => 'Photo gallery',
    ];

    public const MEDIA_TYPES = ['image', 'gallery'];

    protected $fillable = [
        'template_id', 'key', 'label', 'type', 'section', 'placeholder', 'help',
        'options', 'is_required', 'max_length', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['options' => 'array', 'is_required' => 'boolean'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function isMedia(): bool
    {
        return in_array($this->type, self::MEDIA_TYPES, true);
    }

    public function toSchema(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'section' => $this->section,
            'placeholder' => $this->placeholder,
            'help' => $this->help,
            'required' => $this->is_required,
            'max_length' => $this->max_length,
            'options' => $this->options ?? [],
        ];
    }
}
