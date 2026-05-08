<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodirParticipant extends Model
{
    protected $fillable = [
        'codir_id', 'nom_complet', 'email', 'fonction', 'present',
    ];

    protected $casts = ['present' => 'boolean'];

    public function codir()
    {
        return $this->belongsTo(Codir::class);
    }
}
