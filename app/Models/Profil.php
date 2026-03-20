<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profil extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telephone',
        'adresse',
        'ville',
        'code_postal',
        'date_naissance',
        'lieu_naissance',
        'numero_registre',
        'num_id',
        'type_id',
        'photo_profil',
        'bio',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
