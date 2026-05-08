<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'telephone',
        'poste',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'actif' => 'boolean',
    ];

    // --- Accesseurs ---
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    // --- Vérification des rôles ---
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function canManage(): bool
    {
        return in_array($this->role, ['admin', 'secretaire']);
    }

    // --- Relations ---
    public function reunionsCreees()
    {
        return $this->hasMany(Reunion::class, 'created_by');
    }

    public function accordsCrees()
    {
        return $this->hasMany(Accord::class, 'created_by');
    }
}
