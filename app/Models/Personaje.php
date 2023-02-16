<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personaje extends Model
{
    use HasFactory;

    protected $table = 'personaje';

    public function episodio()
    {
        return $this->belongsTo(Episodio::class, 'id', 'pers_id');
    }

    public function localizacion()
    {
        return $this->belongsTo(Localizacion::class, 'id', 'pers_id');
    }

    public function origen()
    {
        return $this->belongsTo(Origen::class, 'id', 'pers_id');
    }
}
