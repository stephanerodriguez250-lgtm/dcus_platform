<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccordHistorique extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'accord_id', 'evenement', 'commentaire', 'modifie_par', 'date_modification',
    ];

    protected $casts = [
        'date_modification' => 'datetime',
    ];

    public function accord()
    {
        return $this->belongsTo(Accord::class);
    }

    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }
}
