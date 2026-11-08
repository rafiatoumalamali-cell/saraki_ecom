<?php

namespace App\Enums;

enum ContactStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';
    case Archived = 'archived';

    /**
     * Badge tone from the design system status table (§2.4).
     */
    public function badgeTone(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Read => 'info',
            self::Replied => 'success',
            self::Archived => 'neutral',
        };
    }

    public function label(): string
    {
        return __('statuses.contact.'.$this->value);
    }

    /** An enquiry still waiting for an answer. */
    public function needsReply(): bool
    {
        return $this === self::New || $this === self::Read;
    }
}
