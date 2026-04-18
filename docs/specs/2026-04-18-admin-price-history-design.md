# Admin Price History — Design Spec
**Data:** 2026-04-18  
**Branch:** Nuovo-sviluppo-Enxos

---

## Obiettivo

Pannello admin per visualizzare le variazioni storiche dei prezzi delle crociere. Permette di identificare rapidamente i movimenti di prezzo più significativi e di analizzare l'andamento nel tempo di una singola partenza.

---

## Routing

Aggiunto al gruppo `Route::middleware('auth')` esistente in `routes/web.php`:

```php
// Pagina principale
Route::get('/admin/price-history', [AdminPriceHistoryController::class, 'index'])->name('admin.price-history.index');

// API AJAX
Route::prefix('api/admin/price-history')->name('api.admin.price-history.')->group(function () {
    Route::get('/top-variations',  [AdminPriceHistoryController::class, 'topVariations'])->name('top-variations');
    Route::get('/search',          [AdminPriceHistoryController::class, 'search'])->name('search');
    Route::get('/{departureId}',   [AdminPriceHistoryController::class, 'departureHistory'])->name('departure');
});
```

---

## Controller: `AdminPriceHistoryController`

**File:** `app/Http/Controllers/AdminPriceHistoryController.php`

Usa i modelli esistenti: `PriceHistory`, `Departure`, `Product`. Nessun nuovo Model.

Controllo accesso su ogni metodo: `Auth::user()->role !== '1'` → redirect con errore (coerente con `AdminController`).

### Metodi

**`index()`**  
Restituisce la view `admin.price-history.index`.

**`topVariations(Request $request)`**  
- Parametro: `days` (default 30)
- Logica: per ogni coppia `(departure_id, category_code)` confronta il prezzo più recente con il prezzo più vicino a `now() - days`. Calcola `delta_eur` e `delta_pct`. Ordina per `ABS(delta_eur) DESC`. Prende i primi 10.
- Include: nome crociera (`products.name`), `dep_date`, `category_code`, prezzo attuale, prezzo riferimento, `delta_eur`, `delta_pct`.
- Caso limite: se non esistono coppie con almeno 2 rilevazioni nel periodo → array vuoto + flag `insufficient_data: true`.

**`search(Request $request)`**  
- Parametro: `q` (stringa, minimo 2 caratteri)
- Query `LIKE` su `products.name` o `departures.id`. Restituisce lista partenze (id, nome crociera, `dep_date`, `min_price`) ordinate per `dep_date ASC`. Max 20 risultati.

**`departureHistory(Request $request, string $departureId)`**  
- Parametri opzionali: `from`, `to` (date ISO)
- Restituisce tutti i record `price_history` per `departure_id`, filtrati per range date se forniti, ordinati per `recorded_at ASC`.
- Raggruppati per `category_code` per il grafico.
- Ogni record include `delta` calcolato vs record precedente della stessa categoria (null per il primo).
- Caso limite: partenza con una sola rilevazione → `delta: null` per quell'unico record.

---

## View: `admin/price-history/index.blade.php`

Estende `layouts.app`. Struttura coerente con `admin/analytics/index.blade.php`.

### Header
Titolo "Variazioni Prezzi Crociere" + bottone "Aggiorna" che ricarica la top 10.

### Sezione 1 — Top 10 Variazioni (card)
Tabella responsiva Bootstrap 4. Colonne:

| Crociera | Partenza | Categoria | Prezzo attuale | Prezzo 30gg fa | Δ € | Δ % |
|----------|----------|-----------|---------------|----------------|-----|-----|

- `delta_eur` negativo → testo verde (`text-success`)
- `delta_eur` positivo → testo rosso (`text-danger`)
- Click su una riga → popola la ricerca e carica automaticamente il dettaglio di quella partenza
- Stato vuoto: alert Bootstrap "Dati insufficienti — lo storico si arricchirà con le prossime importazioni del catalogo."

### Sezione 2 — Ricerca Crociera (card)
- Input testo (`q`) — cerca per nome crociera
- Datepicker `from` / `to` opzionali (input `type="date"`)
- Select stagione opzionale: Primavera (mar-mag), Estate (giu-ago), Autunno (set-nov), Inverno (dic-feb) — tradotto in range date lato JS prima della chiamata AJAX
- Bottone "Cerca"
- Lista risultati cliccabile sotto l'input (max 20 voci): nome + data partenza + min_price
- Nessun risultato: messaggio inline "Nessuna crociera trovata"

### Sezione 3 — Dettaglio Partenza (card, nascosta finché non selezionata)
Visibile dopo click su riga top 10 o risultato ricerca.

**Header card:** nome crociera + data partenza

**Grafico ApexCharts — Line Chart**
- Asse X: `recorded_at` (datetime)
- Asse Y: prezzo in €
- Una serie per `category_code`, ognuna con colore distinto
- Tooltip mostra data, categoria, prezzo, Δ vs precedente
- Partenza con singola rilevazione: punto singolo visibile

**Tabella storico**
Colonne: Data rilevazione | Categoria | Prezzo | Δ vs precedente | Fonte  
- Δ colorato (verde/rosso) come nella top 10
- Prima rilevazione per categoria: Δ = "—"

---

## Asset

**CSS:** `resources/views/admin/price-history/assets/css.blade.php` (include in `@section('styles')`)  
**JS:** `resources/views/admin/price-history/assets/js.blade.php` (include in `@section('scripts')`)

ApexCharts caricato via CDN nel file JS:
```html
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
```

Tutta la logica AJAX e rendering in vanilla JS con event delegation tramite `addEventListener` + data attributes (nessun `onclick` inline), coerente con le convenzioni del progetto.

---

## Gestione errori AJAX

- Durante ogni chiamata: spinner Bootstrap visibile
- Errore HTTP 500 / network: alert "Errore nel caricamento dati, riprova."
- Risposta vuota: messaggi specifici per contesto (vedi sopra)

---

## Accesso

Controllato su ogni metodo del controller: `Auth::user()->role !== '1'` → `redirect('/home')->with('error', 'Access denied')`.

---

## Fuori scope

- Notifiche automatiche a utenti per variazioni di prezzo (gestito da `PriceAlert`)
- Export CSV dello storico
- Confronto tra più partenze nella stessa vista
