<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccordAppreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'accord_id', 'origine', 'objet', 'avis',
        'observations_forme', 'observations_fond',
        'redige_par', 'chemin_fiche_word',
    ];

    public function accord()
    {
        return $this->belongsTo(Accord::class);
    }

    public function redacteur()
    {
        return $this->belongsTo(User::class, 'redige_par');
    }
}
