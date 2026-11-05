<?php

namespace App\Enums;

enum GalleryCategory: string
{
    case Collection = 'collection';
    case Event = 'event';
    case CompletedDesign = 'completed_design';
}
