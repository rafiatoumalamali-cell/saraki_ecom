<?php

namespace App\Enums;

enum FitPreference: string
{
    case Slim = 'slim';
    case Regular = 'regular';
    case Relaxed = 'relaxed';

    public function label(): string
    {
        return __('measurements.fit.'.$this->value);
    }
}
