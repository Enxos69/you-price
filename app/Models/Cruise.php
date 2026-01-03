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
     * Sanitizza una stringa rimuovendo caratteri non UTF-8 validi
     */
    private function sanitizeString($value)
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        // Converte in stringa se non lo è già
        $value = (string) $value;

        // Rimuove caratteri non UTF-8 validi
        $cleaned = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

        // Rimuove caratteri di controllo tranne newline, tab e carriage return
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $cleaned);

        // Sostituisce caratteri problematici comuni
        $replacements = [
            'ü' => 'ue',
            'ö' => 'oe',
            'ä' => 'ae',
            'ß' => 'ss',
            'Ü' => 'Ue',
            'Ö' => 'Oe',
            'Ä' => 'Ae',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ç' => 'c',
            'ñ' => 'n',
            'ø' => 'o',
            'å' => 'a',
        ];

        $cleaned = strtr($cleaned, $replacements);

        // Rimuove caratteri Windows-1252 problematici usando codici hex
        $cleaned = preg_replace('/[\x80-\x9F]/', '', $cleaned);

        // Sostituisce alcuni caratteri specifici problematici
        $cleaned = str_replace(["\u2018", "\u2019", "\u201C", "\u201D", "\u2013", "\u2014", "\u2026"], ["'", "'", '"', '"', '-', '-', '...'], $cleaned);

        // Rimuove eventuali caratteri ancora problematici
        $cleaned = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $cleaned);

        return trim($cleaned);
    }

    /**
     * Mutators per sanitizzare automaticamente i campi stringa
     */
    public function setShipAttribute($value)
    {
        $this->attributes['ship'] = $this->sanitizeString($value);
    }

    public function setCruiseAttribute($value)
    {
        $this->attributes['cruise'] = $this->sanitizeString($value);
    }

    public function setLineAttribute($value)
    {
        $this->attributes['line'] = $this->sanitizeString($value);
    }

    public function setFromAttribute($value)
    {
        $this->attributes['from'] = $this->sanitizeString($value);
    }

    public function setToAttribute($value)
    {
        $this->attributes['to'] = $this->sanitizeString($value);
    }

    public function setDetailsAttribute($value)
    {
        $this->attributes['details'] = $this->sanitizeString($value);
    }

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
        return $query->where(function ($q) use ($searchTerm) {
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
        return $query->where(function ($q) {
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
            ->where(function ($query) {
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

    /**
     * Get users who favorited this cruise.
     * Relazione many-to-many con User attraverso user_favorites.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'user_favorites')
            ->withTimestamps()
            ->withPivot('note');
    }

    /**
     * Get price alerts for this cruise.
     * Relazione one-to-many con PriceAlert.
     */
    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class);
    }

    /**
     * Get view records for this cruise.
     * Relazione one-to-many con UserCruiseView.
     */
    public function views()
    {
        return $this->hasMany(UserCruiseView::class);
    }

    /**
     * Get user favorites for this cruise.
     * Relazione one-to-many con UserFavorite.
     */
    public function favorites()
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Check if this cruise is favorited by a specific user.
     *
     * @param  int  $userId
     * @return bool
     */
    public function isFavoritedBy($userId)
    {
        return UserFavorite::isFavorite($userId, $this->id);
    }

    /**
     * Get total number of users who favorited this cruise.
     *
     * @return int
     */
    public function getFavoritesCount()
    {
        return $this->favorites()->count();
    }

    /**
     * Get total view count for this cruise.
     *
     * @return int
     */
    public function getTotalViews()
    {
        return $this->views()->sum('view_count');
    }

    /**
     * Get number of unique users who viewed this cruise.
     *
     * @return int
     */
    public function getUniqueViewersCount()
    {
        return $this->views()->count();
    }

    /**
     * Get the lowest available price for this cruise.
     * Checks all cabin types and returns the minimum.
     *
     * @return float|null
     */
    public function getLowestPrice()
    {
        $prices = array_filter([
            $this->interior,
            $this->oceanview,
            $this->balcony,
            $this->minisuite,
            $this->suite
        ], function ($price) {
            return !empty($price);
        });

        if (empty($prices)) {
            return null;
        }

        // Convert prices to numeric values
        $numericPrices = array_map(function ($price) {
            return $this->cleanPrice($price);
        }, $prices);

        $numericPrices = array_filter($numericPrices, function ($price) {
            return $price !== null;
        });

        return !empty($numericPrices) ? min($numericPrices) : null;
    }

    /**
     * Get price for a specific cabin type.
     *
     * @param  string  $cabinType
     * @return float|null
     */
    public function getCabinPrice($cabinType)
    {
        if (!in_array($cabinType, ['interior', 'oceanview', 'balcony', 'minisuite', 'suite'])) {
            return null;
        }

        $price = $this->{$cabinType};

        if (!$price) {
            return null;
        }

        return $this->cleanPrice($price);
    }

    /**
     * Clean and parse price string to float.
     *
     * @param  string  $price
     * @return float|null
     */
    private function cleanPrice($price)
    {
        if (empty($price)) {
            return null;
        }

        // Remove all non-numeric characters except comma and dot
        $cleaned = preg_replace('/[^\d,.]/', '', $price);

        // Handle European (1.234,56) and American (1,234.56) formats
        if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
            // Both present: remove thousands separator
            if (strpos($cleaned, ',') < strrpos($cleaned, '.')) {
                // American format: 1,234.56
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                // European format: 1.234,56
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            }
        } elseif (strpos($cleaned, ',') !== false) {
            // Only comma: European decimal separator
            $cleaned = str_replace(',', '.', $cleaned);
        }

        $result = floatval($cleaned);
        return $result > 0 ? $result : null;
    }

    /**
     * Get all available prices for this cruise.
     *
     * @return array
     */
    public function getAllPrices()
    {
        return [
            'interior' => $this->getCabinPrice('interior'),
            'oceanview' => $this->getCabinPrice('oceanview'),
            'balcony' => $this->getCabinPrice('balcony'),
            'minisuite' => $this->getCabinPrice('minisuite'),
            'suite' => $this->getCabinPrice('suite'),
            'lowest' => $this->getLowestPrice()
        ];
    }

    /**
     * Get active price alerts for this cruise.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePriceAlerts()
    {
        return $this->priceAlerts()
            ->active()
            ->with('user')
            ->get();
    }

    /**
     * Check if this cruise has availability.
     * (Simple logic - can be enhanced based on business rules)
     *
     * @return array
     */
    public function getAvailabilityStatus()
    {
        $lowestPrice = $this->getLowestPrice();

        if (!$lowestPrice) {
            return [
                'status' => 'not_available',
                'label' => 'Non disponibile',
                'class' => 'danger',
                'badge_class' => 'bg-danger'
            ];
        }

        // Example logic: if price is very low, assume limited availability
        if ($lowestPrice < 500) {
            return [
                'status' => 'limited',
                'label' => 'Posti limitati',
                'class' => 'warning',
                'badge_class' => 'bg-warning text-dark'
            ];
        }

        return [
            'status' => 'available',
            'label' => 'Disponibile',
            'class' => 'success',
            'badge_class' => 'bg-success'
        ];
    }

    /**
     * Get formatted duration string.
     *
     * @return string
     */
    public function getFormattedDuration()
    {
        if ($this->duration) {
            return $this->duration;
        }

        if ($this->night) {
            return $this->night . ' ' . ($this->night == 1 ? 'notte' : 'notti');
        }

        return 'N/D';
    }

    /**
     * Get formatted itinerary string.
     *
     * @return string
     */
    public function getFormattedItinerary()
    {
        if ($this->from && $this->to) {
            return $this->from . ' - ' . $this->to;
        }

        if ($this->details) {
            return $this->details;
        }

        return 'Itinerario da definire';
    }

    /**
     * Get formatted departure date.
     *
     * @param  string  $format
     * @return string|null
     */
    public function getFormattedDepartureDate($format = 'd M Y')
    {
        if (!$this->partenza) {
            return null;
        }

        return $this->partenza->format($format);
    }

    /**
     * Get formatted arrival date.
     *
     * @param  string  $format
     * @return string|null
     */
    public function getFormattedArrivalDate($format = 'd M Y')
    {
        if (!$this->arrivo) {
            return null;
        }

        return $this->arrivo->format($format);
    }

    /**
     * Scope: Filter by departure date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDepartureBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('partenza', [$startDate, $endDate]);
    }

   
    /**
     * Scope: Filter by destination (from or to).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $destination
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDestination($query, $destination)
    {
        return $query->where(function ($q) use ($destination) {
            $q->where('from', 'LIKE', "%{$destination}%")
                ->orWhere('to', 'LIKE', "%{$destination}%")
                ->orWhere('details', 'LIKE', "%{$destination}%");
        });
    }

    /**
     * Scope: Order by lowest price.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByLowestPrice($query, $direction = 'asc')
    {
        // This is a simplified version - for better performance,
        // consider adding a computed column for lowest_price
        return $query->orderBy('interior', $direction);
    }
}
