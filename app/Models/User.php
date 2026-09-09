<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'service',
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

    public static array $services = [
        'SA' => 'Secrétariat administratif',
        'SAF' => 'Service administratif et financier',
        'SSCP' => 'Service de la stratégie de coopération et du partenariat',
        'SISCUAP' => "Service de l'information et du suivi des activités de coopération universitaire et des accords de partenariat",
    ];

    // --- Accesseurs ---
    public function getNomCompletAttribute(): string
    {
        return $this->prenom.' '.$this->nom;
    }

    public function getServiceLabelAttribute(): ?string
    {
        return self::$services[$this->service] ?? $this->service;
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

    /**
     * Peut laisser une appréciation sur un accord : gestionnaires, ou agent explicitement autorisé
     * (voir AccordAppreciateur).
     */
    public function peutApprecierAccords(): bool
    {
        return $this->canManage() || AccordAppreciateur::where('user_id', $this->id)->exists();
    }

    /**
     * Tous les utilisateurs actifs, à l'exception de celui dont l'id est fourni (ex: l'auteur d'une action).
     */
    public static function actifsSauf(?int $userId): Collection
    {
        return static::where('actif', true)->where('id', '!=', $userId)->get();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
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
