<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'phone', 'password', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Shared by the register form and the profile form so the two limits cannot drift.
     * Callers prepend "nullable" or "required".
     *
     * @var array<int, string>
     */
    public const AVATAR_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    protected $attributes = [
        'role' => 'customer',
        'locale' => 'fr',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path === null
            ? null
            : Storage::disk('public')->url($this->avatar_path);
    }

    /**
     * Two-letter monogram used when no photo has been uploaded.
     */
    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    /**
     * Replaces the photo and removes the file it superseded, so the disk cannot fill
     * with orphaned uploads from repeat profile edits.
     */
    public function storeAvatar(UploadedFile $file): void
    {
        $previous = $this->avatar_path;

        $this->forceFill(['avatar_path' => $file->store('avatars', 'public')])->save();

        if ($previous !== null) {
            Storage::disk('public')->delete($previous);
        }
    }

    public function removeAvatar(): void
    {
        if ($this->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->avatar_path);

        $this->forceFill(['avatar_path' => null])->save();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function measurements(): HasOne
    {
        return $this->hasOne(CustomerMeasurement::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }
}
