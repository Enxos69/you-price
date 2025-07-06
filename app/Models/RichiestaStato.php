<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RichiestaStato extends Model
{
    use HasFactory;

    // Definisci la tabella se il nome non segue la convenzione
    protected $table = 'richiesta_stato';

    public function richieste()
    {
        return $this->hasMany(Richiesta::class, 'id_richiesta_stato');
    }
}
