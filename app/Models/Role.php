<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Definisci la tabella se il nome non segue la convenzione
    protected $table = 'roles';

    // Indica i campi che possono essere assegnati in massa
    protected $fillable = ['name'];
}

