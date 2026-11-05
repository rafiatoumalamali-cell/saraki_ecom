<?php

namespace App\Models;

use App\Enums\ContactStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'subject', 'message'])]
class ContactMessage extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return ['status' => ContactStatus::class];
    }
}
