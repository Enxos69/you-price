<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

class SearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_range',
        'budget',
        'participants',
        'port_start',
        'port_end',
        'total_matches',
        'total_alternatives',
        'satisfaction_score',
        'optimization_score',
        'avg_price_found',
        'avg_savings',
        'companies_found',
        'avg_duration',
        'ip_address',
        'user_agent',
        'device_type',
        'operating_system',
        'browser',
        'browser_version',
        'platform',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'screen_resolution',
        'country',
        'region',
        'city',
        'timezone',
        'latitude',
        'longitude',
        'isp',
        'connection_type',
        'session_id',
        'referrer',
        'language',
        'search_suggestions',
        'search_duration_ms',
        'search_successful',
        'error_message',
        'is_guest',
        'search_date',
        'additional_data'
    ];

    protected $casts = [
        'search_suggestions' => 'array',
        'additional_data' => 'array',
        'search_date' => 'datetime',
        'budget' => 'decimal:2',
        'satisfaction_score' => 'decimal:2',
        'optimization_score' => 'decimal:2',
        'avg_price_found' => 'decimal:2',
        'avg_savings' => 'decimal:2',
        'avg_duration' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'search_successful' => 'boolean',
        'is_guest' => 'boolean',
    ];

    /**
     * Relazione con l'utente
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crea un nuovo log di ricerca con rilevamento automatico delle informazioni
     */
    public static function createFromRequest($searchData, $searchResults, $searchDuration = null, $error = null)
    {
        $agent = new Agent();
        $request = request();

        // Informazioni base della ricerca
        $logData = [
            'user_id' => Auth::id(),
            'is_guest' => !Auth::check(),
            'session_id' => Session::getId(),
            'search_date' => Carbon::now(),
            'search_successful' => $error === null,
            'error_message' => $error,
            'search_duration_ms' => $searchDuration,
        ];

        // Parametri di ricerca
        $logData = array_merge($logData, [
            'date_range' => $searchData['date_range'] ?? null,
            'budget' => $searchData['budget'] ?? null,
            'participants' => $searchData['participants'] ?? null,
            'port_start' => $searchData['port_start'] ?? null,
            'port_end' => $searchData['port_end'] ?? null,
        ]);

        // Risultati della ricerca (solo se ricerca riuscita)
        if ($error === null && $searchResults) {
            $statistics = $searchResults['statistiche'] ?? [];
            $logData = array_merge($logData, [
                'total_matches' => count($searchResults['matches'] ?? []),
                'total_alternatives' => count($searchResults['alternative'] ?? []),
                'satisfaction_score' => $searchResults['soddisfazione_attuale'] ?? 0,
                'optimization_score' => $searchResults['soddisfazione_ottimale'] ?? 0,
                'avg_price_found' => $statistics['prezzo_medio_trovato'] ?? null,
                'avg_savings' => $statistics['risparmio_medio'] ?? null,
                'companies_found' => $statistics['compagnie_diverse'] ?? 0,
                'avg_duration' => $statistics['durata_media'] ?? null,
                'search_suggestions' => $searchResults['consigli'] ?? [],
            ]);
        }

        // Informazioni del dispositivo e browser
        $logData = array_merge($logData, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => self::getDeviceType($agent),
            'operating_system' => $agent->platform(),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'platform' => $agent->platform() . ' ' . $agent->version($agent->platform()),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_desktop' => $agent->isDesktop(),
            'referrer' => $request->header('referer'),
            'language' => $request->getPreferredLanguage(['it', 'en', 'de', 'fr', 'es']),
        ]);

        // Informazioni geografiche (se disponibili)
        $geoData = self::getGeoLocationData($request->ip());
        if ($geoData) {
            $logData = array_merge($logData, $geoData);
        }

        return self::create($logData);
    }

    /**
     * Determina il tipo di dispositivo
     */
    private static function getDeviceType(Agent $agent)
    {
        if ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isDesktop()) {
            return 'desktop';
        }
        return 'unknown';
    }

    /**
     * Ottiene informazioni di geolocalizzazione dall'IP
     * Nota: Implementazione base - per produzione considera servizi come ipinfo.io o MaxMind
     */
    private static function getGeoLocationData($ip)
    {
        // Skip per IP locali
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return [
                'country' => 'Local',
                'region' => 'Local',
                'city' => 'Local',
                'timezone' => config('app.timezone'),
                'isp' => 'Local Network'
            ];
        }

        // Esempio di implementazione con un servizio gratuito
        // In produzione, considera servizi più affidabili come MaxMind GeoIP2
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,timezone,isp,lat,lon,query");
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'city' => $data['city'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'isp' => $data['isp'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Log l'errore ma non bloccare la creazione del log
            Log::warning('Errore geolocalizzazione IP: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Scope per filtrare per utenti registrati
     */
    public function scopeRegisteredUsers($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope per filtrare per utenti ospiti
     */
    public function scopeGuestUsers($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope per filtrare per tipo di dispositivo
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope per filtrare per ricerche di successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('search_successful', true);
    }

    /**
     * Scope per filtrare per periodo
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('search_date', [$startDate, $endDate]);
    }

    /**
     * Accessor per ottenere il nome del dispositivo formattato
     */
    public function getDeviceInfoAttribute()
    {
        $parts = [];
        
        if ($this->device_type) {
            $parts[] = ucfirst($this->device_type);
        }
        
        if ($this->operating_system) {
            $parts[] = $this->operating_system;
        }
        
        if ($this->browser) {
            $browser = $this->browser;
            if ($this->browser_version) {
                $browser .= ' ' . $this->browser_version;
            }
            $parts[] = $browser;
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Accessor per ottenere informazioni geografiche formattate
     */
    public function getLocationInfoAttribute()
    {
        $parts = [];
        
        if ($this->city) $parts[] = $this->city;
        if ($this->region) $parts[] = $this->region;
        if ($this->country) $parts[] = $this->country;
        
        return implode(', ', $parts) ?: 'Sconosciuta';
    }

    /**
     * Accessor per ottenere il tipo di utente
     */
    public function getUserTypeAttribute()
    {
        return $this->is_guest ? 'Ospite' : 'Registrato';
    }

    /**
     * Metodi statici per statistiche
     */
    public static function getTopCountries($limit = 10)
    {
        return self::selectRaw('country, COUNT(*) as searches')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('searches', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getDeviceStatistics()
    {
        return self::selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->get();
    }

    public static function getBrowserStatistics()
    {
        return self::selectRaw('browser, COUNT(*) as count')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }

    public static function getSearchTrends($days = 30)
    {
        return self::selectRaw('DATE(search_date) as date, COUNT(*) as searches')
            ->where('search_date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public static function getAverageSearchMetrics()
    {
        return self::selectRaw('
            AVG(satisfaction_score) as avg_satisfaction,
            AVG(optimization_score) as avg_optimization,
            AVG(total_matches) as avg_matches,
            AVG(total_alternatives) as avg_alternatives,
            AVG(search_duration_ms) as avg_duration,
            AVG(budget) as avg_budget,
            AVG(participants) as avg_participants
        ')->first();
    }
}