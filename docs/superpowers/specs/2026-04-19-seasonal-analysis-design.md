# Analisi Stagionale Prezzi — Design Spec

**Data:** 2026-04-19  
**Pagina:** `/admin/price-history` (sezione aggiuntiva)  
**Autore:** brainstorming session

---

## Contesto

Il pannello admin storico prezzi mostra già:
- Top 10 variazioni (ultimi 30 giorni)
- Ricerca crociera + dettaglio partenza con grafico evoluzione prezzi

L'obiettivo è aggiungere un pannello di **analisi stagionale** che compara le edizioni dello stesso itinerario nel tempo, permettendo di identificare:
1. Come varia il prezzo nelle settimane prima della partenza (confronto year-over-year)
2. Quale mese di partenza risulta storicamente più economico (stagionalità mensile)

---

## Comportamento

Il pannello `#seasonal-section` si apre **automaticamente insieme al dettaglio partenza** (`#detail-section`), sia quando si clicca su una riga Top 10 sia quando si seleziona un risultato di ricerca. Riceve `departure_id` e `cruise_name` dall'evento già gestito da `loadDepartureDetail()`.

---

## Architettura

### Backend — 2 nuovi metodi su `AdminPriceHistoryController`

#### `seasonalWeekly(Request $request)`
- **Route:** `GET /api/admin/price-history/seasonal/weekly`
- **Parametri:** `departure_id` (required), `category` (default: `IC`)
- **Logica:**
  1. Recupera `cruise_name`, `cruise_line_id`, `area_id`, `port_from_id`, `port_to_id` dalla departure selezionata (join departures → products)
  2. Trova tutte le departures dello stesso itinerario (`cruise_name` + `cruise_line_id` + `port_from_id` + `port_to_id`) che abbiano almeno 2 record in `price_history` per la categoria richiesta
  3. Per ogni departure, calcola `TIMESTAMPDIFF(WEEK, recorded_at, dep_date)` come asse X
  4. Aggrega per anno di partenza (`YEAR(dep_date)`) e settimane: `AVG(price)` dove più partenze dello stesso anno cadono alla stessa distanza
  5. Restituisce serie ordinate per anno, X decrescente (20 → 0)
- **Response:**
```json
{
  "itinerary": "Eastern Mediterranean 8 days from/to Istanbul",
  "category": "IC",
  "available_categories": ["IC", "BC", "SS", "GS"],
  "series": [
    { "name": "2024", "data": [{"x": 20, "y": 650}, {"x": 17, "y": 675}, ...] },
    { "name": "2025", "data": [...] },
    { "name": "2026", "data": [...] }
  ]
}
```

#### `seasonalMonthly(Request $request)`
- **Route:** `GET /api/admin/price-history/seasonal/monthly`
- **Parametri:** `departure_id` (required), `category` (default: `IC`)
- **Logica:**
  1. Stessa identificazione itinerario del metodo weekly
  2. Per ogni departure, prende l'**ultimo snapshot** disponibile (MAX recorded_at per categoria) come prezzo rappresentativo
  3. Raggruppa per `YEAR(dep_date)` e `MONTH(dep_date)`
  4. Calcola `AVG(price)` e `MIN(price)` per cella anno×mese
  5. Costruisce array di 12 elementi per anno (null per mesi senza dati)
- **Response:**
```json
{
  "itinerary": "Eastern Mediterranean 8 days from/to Istanbul",
  "category": "IC",
  "categories": ["Gen","Feb","Mar","Apr","Mag","Giu","Lug","Ago","Set","Ott","Nov","Dic"],
  "series": [
    { "name": "2024", "data": [null,null,null,null,null,769,801,813,null,null,null,null] },
    { "name": "2025", "data": [null,null,null,null,null,812,846,858,null,null,null,null] },
    { "name": "2026", "data": [null,null,null,null,null,861,789,813,813,null,null,null] }
  ]
}
```

### Frontend — estensione di `js.blade.php`

`loadDepartureDetail()` viene estesa per chiamare anche `loadSeasonalWeekly()` dopo aver ricevuto i dati del dettaglio.

**Flusso:**
```
loadDepartureDetail(departureId, cruiseName)
  ├── fetch /api/admin/price-history/{departureId}   (esistente)
  │     └── renderChart() + renderDetailTable()
  └── loadSeasonalWeekly(departureId, currentCategory)  (nuovo)
        └── renderSeasonalWeeklyChart()
```

**Tab 2 lazy load:** `loadSeasonalMonthly()` viene chiamata solo al click del tab "Stagionalità mensile", una sola volta (risultato cached nella variabile `seasonalMonthlyData`).

**Selettore categoria:** cambiando il `<select #seasonal-category>`, vengono ricaricati entrambi i dataset (reset cache mensile).

---

## UI — struttura HTML (da aggiungere in `index.blade.php`)

```html
<div id="seasonal-section" class="row mb-4 d-none">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-chart-bar mr-2"></i>
          Analisi Stagionale — <span id="seasonal-itinerary-name"></span>
        </h5>
        <div class="d-flex align-items-center">
          <label class="mr-2 mb-0 text-muted small">Categoria:</label>
          <select id="seasonal-category" class="form-control form-control-sm" style="width:auto">
            <option value="IC">IC — Interior Cabin</option>
            <option value="BC">BC — Balcony Cabin</option>
            <option value="SC">SC — Sea View Cabin</option>
            <option value="SS">SS — Sea Suite</option>
            <option value="GS">GS — Grand Suite</option>
          </select>
        </div>
      </div>
      <div class="card-body">
        <!-- Tab nav -->
        <ul class="nav nav-tabs mb-3" id="seasonal-tabs">
          <li class="nav-item">
            <a class="nav-link active" data-tab="weekly" href="#">
              <i class="fas fa-chart-line mr-1"></i>Evoluzione settimanale
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-tab="monthly" href="#">
              <i class="fas fa-calendar-alt mr-1"></i>Stagionalità mensile
            </a>
          </li>
        </ul>
        <!-- Chart container -->
        <div id="seasonal-chart"></div>
        <!-- Nota esplicativa -->
        <p id="seasonal-note" class="text-muted small mt-2 mb-0"></p>
      </div>
    </div>
  </div>
</div>
```

---

## Grafici ApexCharts

### Tab 1 — Evoluzione settimanale
- **Tipo:** `line`
- **Asse X:** numerico, label "Settimane prima della partenza", invertito (20 → 0), tick ogni unità presente nei dati
- **Asse Y:** valuta EUR
- **Serie:** una per anno (2024, 2025, 2026, …)
- **Tooltip:** "Anno {year} — {x} sett. prima: €{y}"
- **Nota sotto grafico:** "Prezzi medi tra le partenze dello stesso itinerario alla stessa distanza temporale"

### Tab 2 — Stagionalità mensile
- **Tipo:** `bar` con `columnWidth: 70%`
- **Asse X:** categorico (Gen–Dic), solo mesi con dati
- **Asse Y:** valuta EUR
- **Serie:** una per anno
- **Tooltip:** "Anno {year} — {month}: €{y} (media)"
- **Nota sotto grafico:** "Prezzo al momento dell'ultimo snapshot disponibile per ogni partenza"

---

## Route da aggiungere in `web.php`

```php
Route::prefix('api/admin/price-history')->name('api.admin.price-history.')->group(function () {
    // ... route esistenti ...
    Route::get('/seasonal/weekly',  [AdminPriceHistoryController::class, 'seasonalWeekly'])->name('seasonal.weekly');
    Route::get('/seasonal/monthly', [AdminPriceHistoryController::class, 'seasonalMonthly'])->name('seasonal.monthly');
});
```

---

## File modificati

| File | Tipo modifica |
|---|---|
| `app/Http/Controllers/AdminPriceHistoryController.php` | Aggiunta metodi `seasonalWeekly`, `seasonalMonthly` |
| `routes/web.php` | Aggiunta 2 route |
| `resources/views/admin/price-history/index.blade.php` | Aggiunta `#seasonal-section` HTML |
| `resources/views/admin/price-history/assets/js.blade.php` | Estensione `loadDepartureDetail`, tab logic, 2 render functions |

Nessuna modifica a CSS (usa stili Bootstrap 4 e card già presenti).

---

## Edge cases

- **Itinerario con una sola edizione:** il pannello si apre ma mostra un messaggio "Dati insufficienti per il confronto — solo 1 stagione disponibile"
- **Categoria non disponibile per quell'itinerario:** fallback automatico a IC se la categoria selezionata non ha dati
- **DXB (Gulf) vs IST (Med):** DXB non ha BC/SS/GS → il selettore mostra solo le categorie in `available_categories` dalla risposta `seasonalWeekly`; le opzioni non disponibili vengono rimosse/disabilitate nel DOM
- **Nessun dato:** messaggio placeholder, pannello non nascosto
