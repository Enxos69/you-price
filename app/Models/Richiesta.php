<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Richiesta extends Model
{
    use HasFactory;

    /**
     * La tabella associata al modello.
     *
     * @var string
     */
    protected $table = 'richiesta';

    protected $fillable = [
        'id_utente',
        'id_richiesta_tipo',
        'id_richiesta_stato',
        'data_fine_validita',
        'budget',
        'rating',
        'note',
    ];

    /**
     * Definisce la relazione con il modello User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_utente');
    }

    /**
     * Definisce la relazione con il modello RichiestaTipo.
     */
    public function tipo()
    {
        return $this->belongsTo(RichiestaTipo::class, 'id_richiesta_tipo');
    }

    /**
     * Definisce la relazione con il modello RichiestaStato.
     */
    public function stato()
    {
        return $this->belongsTo(RichiestaStato::class, 'id_richiesta_stato');
    }

    /**
     * Ottiene il record dello stato della richiesta.
     *
     * @return \App\Models\RichiestaStato|null
     */
    public function get_stato()
    {
        return $this->stato()->first();
    }

    /**
     * Ottiene il record dell'utente della richiesta.
     *
     * @return \App\Models\User|null
     */
    public function get_user()
    {
        return $this->user()->first();
    }

    /**
     * Ottiene il record del tipo di richiesta.
     *
     * @return \App\Models\RichiestaTipo|null
     */
    public function get_tipo()
    {
        return $this->tipo()->first();
    }
}
