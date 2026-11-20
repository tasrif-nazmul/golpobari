<?php

namespace App\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Pending => 'amber',
            self::Active => 'emerald',
            self::Expired => 'orange',
            self::Suspended => 'rose',
            self::Archived => 'zinc',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
