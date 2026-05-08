<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodirAcces extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'codir_id', 'user_id', 'accorde_par', 'date_acces',
    ];

    protected $casts = ['date_acces' => 'datetime'];

    public function codir()
    {
        return $this->belongsTo(Codir::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accordePar()
    {
        return $this->belongsTo(User::class, 'accorde_par');
    }
}
