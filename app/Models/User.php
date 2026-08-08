<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\AdminAccess;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function adminApiTokens(): HasMany
    {
        return $this->hasMany(AdminApiToken::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot(['role', 'permissions'])
            ->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /** Legacy: system admin / super admin. */
    public function isAdmin(): bool
    {
        return AdminAccess::isSuperAdmin($this);
    }

    public function isSuperAdmin(): bool
    {
        return AdminAccess::isSuperAdmin($this);
    }

    public function canAccessConsole(): bool
    {
        return AdminAccess::canAccessConsole($this);
    }
}
