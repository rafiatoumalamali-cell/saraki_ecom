<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function badgeTone(): string
    {
        return $this === self::Published ? 'success' : 'neutral';
    }

    public function label(): string
    {
        return __('statuses.post.'.$this->value);
    }
}
