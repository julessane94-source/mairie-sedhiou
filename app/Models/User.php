<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Profil;
use App\Models\Demande;
use App\Models\Message;
use App\Models\Payment;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
        'role',
        'statut',
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

    // Relations
    public function profil(): HasOne
    {
        return $this->hasOne(Profil::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class, 'citoyen_id');
    }

    public function demandesAssignees(): HasMany
    {
        return $this->hasMany(Demande::class, 'agent_assigne_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'citoyen_id');
    }

    // Métiers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCitoyen(): bool
    {
        return $this->role === 'citoyen';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }
}
