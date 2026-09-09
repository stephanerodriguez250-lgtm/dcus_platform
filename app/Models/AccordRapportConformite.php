<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccordRapportConformite extends Model
{
    use HasFactory;

    protected $fillable = [
        'accord_id', 'resume', 'points_conformes', 'points_non_conformes',
        'chemin_rapport_word', 'genere_par', 'genere_le',
    ];

    protected $casts = [
        'genere_le' => 'datetime',
    ];

    public function accord()
    {
        return $this->belongsTo(Accord::class);
    }

    public function redacteur()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }
}
