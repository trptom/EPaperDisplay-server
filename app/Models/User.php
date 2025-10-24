<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generic find or create for an OAuth provider.
     *
     * @param array $userData ['provider' => string, 'id' => string, 'name' => string, 'email' => string|null]
     * @return self
     */
    public static function findOrCreateFromProvider(array $userData): self
    {
        // Reuse the logic: try to match by provider+provider_id, or by email if available
        $query = self::where(function ($q) use ($userData) {
            $q->where('provider', $userData['provider'])
                ->where('provider_id', $userData['id']);
        });

        if (!empty($userData['email'])) {
            $query = $query->orWhere('email', $userData['email']);
        }

        $user = $query->first();

        if ($user) {
            $user->update([
                'name' => $userData['name'] ?? $user->name,
                'email' => $userData['email'] ?? $user->email,
                'provider' => $userData['provider'],
                'provider_id' => $userData['id'],
            ]);
            return $user;
        }

        return self::create([
            'name' => $userData['name'] ?? '',
            'email' => $userData['email'] ?? null,
            'password' => bin2hex(random_bytes(16)),
            'provider' => $userData['provider'],
            'provider_id' => $userData['id'],
        ]);
    }
}
