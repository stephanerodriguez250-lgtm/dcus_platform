<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccordAppreciateur extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'accorde_par'];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accordePar()
    {
        return $this->belongsTo(User::class, 'accorde_par');
    }
}
