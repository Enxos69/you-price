<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cruise extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship', 
        'cruise', 
        'duration', 
        'from', 
        'to', 
        'details',
        'line', 
        'night', 
        'partenza', 
        'arrivo',
        'interior', 
        'oceanview', 
        'balcony', 
        'minisuite', 
        'suite'
    ];

    protected $casts = [
        'partenza' => 'date',
        'arrivo' => 'date',
        'interior' => 'decimal:2',
        'oceanview' => 'decimal:2',
        'balcony' => 'decimal:2',
        'minisuite' => 'decimal:2',
        'suite' => 'decimal:2',
        'duration' => 'integer',
        'night' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'partenza',
        'arrivo',
        'created_at',
        'updated_at'
    ];

    /**
     * Scope per filtrare per compagnia
     */
    public function scopeByLine($query, $line)
    {
        return $query->where('line', 'like', '%' . $line . '%');
    }

    /**
     * Scope per filtrare per range di date
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('partenza', [$startDate, $endDate]);
    }

    /**
     * Scope per filtrare per budget massimo
     */
    public function scopeWithinBudget($query, $maxPrice, $cabinType = 'interior')
    {
        return $query->where($cabinType, '<=', $maxPrice)
                    ->whereNotNull($cabinType);
    }

    /**
     * Scope per filtrare per porto di partenza
     */
    public function scopeFromPort($query, $port)
    {
        return $query->where('partenza', 'like', '%' . $port . '%');
    }

    /**
     * Scope per filtrare per porto di arrivo
     */
    public function scopeToPort($query, $port)
    {
        return $query->where('arrivo', 'like', '%' . $port . '%');
    }

    /**
     * Accessor per formattare la durata
     */
    public function getFormattedDurationAttribute()
    {
        if ($this->duration) {
            return $this->duration . ' giorni';
        }
        if ($this->night) {
            return $this->night . ' notti';
        }
        return 'N/D';
    }

    /**
     * Accessor per il prezzo minimo
     */
    public function getMinPriceAttribute()
    {
        $prices = collect([
            $this->interior,
            $this->oceanview,
            $this->balcony,
            $this->minisuite,
            $this->suite
        ])->filter()->values();

        return $prices->isEmpty() ? null : $prices->min();
    }

    /**
     * Accessor per il prezzo massimo
     */
    public function getMaxPriceAttribute()
    {
        $prices = collect([
            $this->interior,
            $this->oceanview,
            $this->balcony,
            $this->minisuite,
            $this->suite
        ])->filter()->values();

        return $prices->isEmpty() ? null : $prices->max();
    }

    /**
     * Accessor per il range di prezzi formattato
     */
    public function getPriceRangeAttribute()
    {
        $minPrice = $this->min_price;
        $maxPrice = $this->max_price;

        if (!$minPrice && !$maxPrice) {
            return 'Prezzo non disponibile';
        }

        if ($minPrice == $maxPrice) {
            return '€' . number_format($minPrice, 0, ',', '.');
        }

        return '€' . number_format($minPrice, 0, ',', '.') . ' - €' . number_format($maxPrice, 0, ',', '.');
    }

    /**
     * Accessor per verificare se ha prezzi disponibili
     */
    public function getHasPricesAttribute()
    {
        return !is_null($this->min_price);
    }

    /**
     * Accessor per l'itinerario formattato
     */
    public function getItineraryAttribute()
    {
        $partenza = $this->partenza ? $this->partenza->format('d/m/Y') : 'N/D';
        $arrivo = $this->arrivo ? $this->arrivo->format('d/m/Y') : 'N/D';
        
        return $partenza . ' → ' . $arrivo;
    }

    /**
     * Accessor per la descrizione completa
     */
    public function getFullDescriptionAttribute()
    {
        $parts = [];
        
        if ($this->line) {
            $parts[] = $this->line;
        }
        
        if ($this->ship) {
            $parts[] = $this->ship;
        }
        
        if ($this->cruise) {
            $parts[] = $this->cruise;
        }
        
        if ($this->formatted_duration) {
            $parts[] = $this->formatted_duration;
        }

        return implode(' - ', $parts);
    }

    /**
     * Mutator per normalizzare i prezzi
     */
    public function setInteriorAttribute($value)
    {
        $this->attributes['interior'] = $this->normalizePrice($value);
    }

    public function setOceanviewAttribute($value)
    {
        $this->attributes['oceanview'] = $this->normalizePrice($value);
    }

    public function setBalconyAttribute($value)
    {
        $this->attributes['balcony'] = $this->normalizePrice($value);
    }

    public function setMinisuiteAttribute($value)
    {
        $this->attributes['minisuite'] = $this->normalizePrice($value);
    }

    public function setSuiteAttribute($value)
    {
        $this->attributes['suite'] = $this->normalizePrice($value);
    }

    /**
     * Normalizza i prezzi rimuovendo caratteri non numerici
     */
    private function normalizePrice($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Rimuovi simboli di valuta e caratteri non numerici eccetto punto e virgola
        $cleaned = preg_replace('/[€$£,\s]/', '', $value);
        
        // Converti virgola in punto per i decimali
        $cleaned = str_replace(',', '.', $cleaned);
        
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Scope per cercare per testo libero
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('ship', 'like', '%' . $searchTerm . '%')
              ->orWhere('cruise', 'like', '%' . $searchTerm . '%')
              ->orWhere('line', 'like', '%' . $searchTerm . '%')
              ->orWhere('partenza', 'like', '%' . $searchTerm . '%')
              ->orWhere('arrivo', 'like', '%' . $searchTerm . '%');
        });
    }

    /**
     * Scope per crociere disponibili (con prezzi)
     */
    public function scopeAvailable($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('interior')
              ->orWhereNotNull('oceanview')
              ->orWhereNotNull('balcony')
              ->orWhereNotNull('minisuite')
              ->orWhereNotNull('suite');
        });
    }

    /**
     * Scope per crociere future
     */
    public function scopeFuture($query)
    {
        return $query->where('partenza', '>=', Carbon::now()->format('Y-m-d'));
    }

    /**
     * Metodo per verificare se è un duplicato
     */
    public function isDuplicate()
    {
        $query = static::where('ship', $this->ship)
                      ->where('line', $this->line)
                      ->where('cruise', $this->cruise);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        if ($this->partenza) {
            $query->whereDate('partenza', $this->partenza);
        }
        
        if ($this->arrivo) {
            $query->whereDate('arrivo', $this->arrivo);
        }

        return $query->exists();
    }

    /**
     * Metodo per ottenere crociere simili
     */
    public function getSimilarCruises($limit = 5)
    {
        return static::where('id', '!=', $this->id)
                    ->where(function($query) {
                        $query->where('line', $this->line)
                              ->orWhere('ship', $this->ship)
                              ->orWhere('cruise', 'like', '%' . substr($this->cruise, 0, 10) . '%');
                    })
                    ->available()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Metodo per formattare un prezzo
     */
    public static function formatPrice($price)
    {
        if (is_null($price) || $price <= 0) {
            return '-';
        }

        return '€' . number_format($price, 0, ',', '.');
    }

    /**
     * Metodo per ottenere statistiche sui prezzi
     */
    public static function getPriceStats()
    {
        return [
            'avg_interior' => static::whereNotNull('interior')->avg('interior'),
            'min_interior' => static::whereNotNull('interior')->min('interior'),
            'max_interior' => static::whereNotNull('interior')->max('interior'),
            'total_cruises' => static::count(),
            'available_cruises' => static::available()->count()
        ];
    }
}