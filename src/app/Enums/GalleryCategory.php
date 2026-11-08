<?php

namespace App\Enums;

enum GalleryCategory: string
{
    case Collection = 'collection';
    case Event = 'event';
    case CompletedDesign = 'completed_design';

    public function label(): string
    {
        return __('shop.gallery.categories.'.$this->value);
    }
}
