<?php

namespace App\Enums;

enum PageVisibility: string
{
    case Public = 'public';     // listed in sitemap, indexable
    case Unlisted = 'unlisted'; // anyone with the link, noindex
    case Private = 'private';   // needs an access code

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
