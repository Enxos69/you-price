@extends('layouts.app')

@section('title', 'I Miei Alert Prezzi - You Price')

@section('content')
@php
  $macroLabels = ['IS' => 'Cabina Interna', 'OS' => 'Cabina Esterna', 'BK' => 'Balcone', 'MS' => 'Mini Suite', 'SU' => 'Suite'];
  $macroIcons  = ['IS' => 'fa-moon', 'OS' => 'fa-sun', 'BK' => 'fa-door-open', 'MS' => 'fa-star-half-alt', 'SU' => 'fa-crown'];

  $totalGroups   = $alertGroups->count();
  $totalActive   = $alertGroups->sum(fn($g) => $g->where('is_active', true)->count());
  $totalReached  = $alertGroups->sum(fn($g) => $g->filter(fn($a) => $a->isPriceReached())->count());
@endphp
<style>
/* ── ALERTS PAGE ─────────────────────────────────────────────────── */
.al-hero { background: linear-gradient(135deg, #0d4f5c 0%, #1a7a8a 100%); color:#fff; padding:48px 0 32px; }
.al-hero__title { font-size:26px; font-weight:700; margin-bottom:4px; }
.al-hero__sub   { font-size:14px; opacity:.8; margin:0; }
.al-stat        { background:rgba(255,255,255,.12); border-radius:10px; padding:14px 20px; text-align:center; }
.al-stat__val   { font-size:24px; font-weight:700; line-height:1; }
.al-stat__lbl   { font-size:11px; opacity:.8; text-transform:uppercase; letter-spacing:.5px; margin-top:2px; }

.al-toolbar     { background:#fff; border-bottom:1px solid #e8ecef; padding:12px 0; }
.al-filter-btn  { border-radius:20px; font-size:13px; padding:4px 14px; }
.al-filter-btn.active { background:#1a7a8a; color:#fff; border-color:#1a7a8a; }

/* Card gruppo */
.al-card        { border-radius:12px; border:none; box-shadow:0 2px 10px rgba(0,0,0,.07); transition:box-shadow .2s; }
.al-card:hover  { box-shadow:0 6px 20px rgba(0,0,0,.11); }
.al-card.is-expired { opacity:.75; }

.al-card__head  { padding:14px 16px 10px; border-bottom:1px solid #f0f0f0; }
.al-card__ship  { font-size:15px; font-weight:700; color:#1a2b3c; margin:0 0 1px; }
.al-card__cruise { font-size:12px; color:#888; margin:0 0 8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.al-card__meta  { padding:8px 16px; background:#fafafa; border-bottom:1px solid #f0f0f0; font-size:12px; color:#777; display:flex; gap:12px; flex-wrap:wrap; }
.al-card__summary { display:flex; gap:6px; flex-wrap:wrap; }

/* Badge */
.al-badge       { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 9px; font-size:11px; font-weight:600; white-space:nowrap; }
.al-badge--active   { background:#e3f5f7; color:#1a7a8a; }
.al-badge--reached  { background:#e8f5e9; color:#2e7d32; }
.al-badge--inactive { background:#f5f5f5; color:#999; }
.al-badge--notified { background:#fff8e1; color:#f57f17; }
.al-badge--expired  { background:#fce4e4; color:#c62828; }
.al-badge--partial  { background:#fff3e0; color:#e65100; }

/* Righe alert */
.al-rows        { padding:0 8px; }
.al-row         { display:flex; align-items:center; gap:8px; padding:9px 8px; border-bottom:1px solid #f5f5f5; transition:background .15s; }
.al-row:last-child { border-bottom:none; }
.al-row:hover   { background:#f9fbfc; }
.al-row.is-inactive { opacity:.55; }

.al-row__cat    { display:flex; align-items:center; gap:6px; min-width:0; flex:1; }
.al-row__dot    { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.al-row__dot--active   { background:#1a7a8a; }
.al-row__dot--reached  { background:#4caf50; }
.al-row__dot--inactive { background:#ccc; }
.al-row__dot--expired  { background:#e57373; }
.al-row__lbl    { font-size:13px; font-weight:600; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

.al-row__prices { display:flex; align-items:center; gap:4px; font-size:12px; flex-shrink:0; }
.al-row__target { font-weight:700; color:#1a7a8a; }
.al-row__sep    { color:#bbb; font-size:10px; }
.al-row__current { font-weight:700; }
.al-row__current--ok   { color:#4caf50; }
.al-row__current--wait { color:#999; }

.al-row__bar    { width:48px; flex-shrink:0; }
.al-row__bar .progress { height:4px; border-radius:2px; }

.al-row__acts   { display:flex; gap:2px; flex-shrink:0; }
.al-btn-icon    { background:none; border:none; border-radius:6px; padding:4px 6px; cursor:pointer; font-size:12px; line-height:1; color:#aaa; transition:background .15s, color .15s; }
.al-btn-icon:hover { background:#f0f0f0; }
.al-btn-icon--toggle        { color:#f9a825; }
.al-btn-icon--toggle:hover  { background:#fff8e1; color:#e65100; }
.al-btn-icon--toggle.off    { color:#4caf50; }
.al-btn-icon--toggle.off:hover { background:#e8f5e9; }
.al-btn-icon--delete        { color:#e53935; }
.al-btn-icon--delete:hover  { background:#fce4e4; }

/* Footer card */
.al-card__foot  { padding:10px 16px 12px; border-top:1px solid #f0f0f0; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.al-grp-acts    { margin-left:auto; display:flex; gap:6px; }

/* Empty */
.al-empty       { background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:60px 24px; text-align:center; }
.al-empty i     { color:#c8d8dc; }
.al-empty h4    { color:#555; margin:16px 0 8px; }
.al-empty p     { color:#999; margin-bottom:24px; }
</style>

{{-- ═══ HERO ═══════════════════════════════════════════════════════════════ --}}
<div class="al-hero">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-start justify-content-between flex-wrap mb-4">
      <div>
        <h1 class="al-hero__title"><i class="fas fa-bell mr-2"></i>I Miei Alert Prezzi</h1>
        <p class="al-hero__sub">Monitora i prezzi e ricevi notifiche quando scendono</p>
      </div>
      <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm mt-2">
        <i class="fas fa-arrow-left mr-1"></i>Dashboard
      </a>
    </div>
    <div class="row" style="max-width:480px;">
      <div class="col-4">
        <div class="al-stat">
          <div class="al-stat__val">{{ $totalGroups }}</div>
          <div class="al-stat__lbl">Crociere</div>
        </div>
      </div>
      <div class="col-4">
        <div class="al-stat">
          <div class="al-stat__val">{{ $totalActive }}</div>
          <div class="al-stat__lbl">Attivi</div>
        </div>
      </div>
      <div class="col-4">
        <div class="al-stat">
          <div class="al-stat__val">{{ $totalReached }}</div>
          <div class="al-stat__lbl">Raggiunti</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ═══ TOOLBAR ══════════════════════════════════════════════════════════════ --}}
@if($alertGroups->isNotEmpty())
<div class="al-toolbar">
  <div class="container-fluid px-4 d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
    <div class="d-flex flex-wrap" style="gap:6px;">
      <button class="btn btn-outline-secondary al-filter-btn active" data-filter="all">Tutti</button>
      <button class="btn btn-outline-secondary al-filter-btn" data-filter="active">Con alert attivi</button>
      <button class="btn btn-outline-secondary al-filter-btn" data-filter="reached">Raggiunti</button>
      <button class="btn btn-outline-secondary al-filter-btn" data-filter="inactive">Solo inattivi</button>
      <button class="btn btn-outline-secondary al-filter-btn" data-filter="expired">Partite</button>
    </div>
    <div class="d-flex" style="gap:6px;">
      <button class="btn btn-outline-warning btn-sm" id="al-delete-expired">
        <i class="fas fa-calendar-times mr-1"></i>Elimina scaduti
      </button>
      <button class="btn btn-outline-danger btn-sm" id="al-delete-inactive">
        <i class="fas fa-trash mr-1"></i>Elimina inattivi
      </button>
    </div>
  </div>
</div>
@endif

{{-- ═══ CONTENT ══════════════════════════════════════════════════════════════ --}}
<div class="container-fluid px-4 mt-4 mb-5">

  @if($alertGroups->isEmpty())
  <div class="al-empty">
    <i class="fas fa-bell-slash fa-4x"></i>
    <h4>Nessun alert configurato</h4>
    <p>Cerca una crociera e imposta un alert per essere avvisato quando il prezzo scende.</p>
    <a href="{{ route('crociere.index') }}" class="btn btn-primary">
      <i class="fas fa-search mr-2"></i>Cerca crociere
    </a>
  </div>
  @else

  <div class="row" id="al-cards-grid">
    @foreach($alertGroups as $departureId => $groupAlerts)
    @php
      $departure   = $groupAlerts->first()->departure;
      $product     = $departure->product;
      $isExpired   = $departure->dep_date->isPast();
      $activeCount = $groupAlerts->where('is_active', true)->count();
      $reachedCount = $groupAlerts->filter(fn($a) => !$isExpired && $a->isPriceReached())->count();
      $totalCount  = $groupAlerts->count();
      $hasActive   = $activeCount > 0;
      $hasReached  = $reachedCount > 0;
      $allInactive = !$isExpired && $activeCount === 0;
    @endphp
    <div class="col-lg-6 mb-4 al-grp-col"
         data-departure-id="{{ $departureId }}"
         data-has-active="{{ $hasActive ? '1' : '0' }}"
         data-has-reached="{{ $hasReached ? '1' : '0' }}"
         data-expired="{{ $isExpired ? '1' : '0' }}">
      <div class="card al-card h-100 {{ $isExpired ? 'is-expired' : '' }}">

        {{-- Header --}}
        <div class="al-card__head">
          <div class="d-flex justify-content-between align-items-start">
            <div style="min-width:0; flex:1; margin-right:10px;">
              <p class="al-card__ship">{{ $product->ship->name ?? 'N/D' }}</p>
              <p class="al-card__cruise">{{ $product->cruise_name ?? '' }}</p>
            </div>
            <div class="al-card__summary flex-shrink-0">
              @if($isExpired)
                <span class="al-badge al-badge--expired"><i class="fas fa-calendar-times"></i>Partita</span>
              @else
                @if($hasReached)
                  <span class="al-badge al-badge--reached"><i class="fas fa-check-circle"></i>{{ $reachedCount }}/{{ $totalCount }} raggiunti</span>
                @endif
                @if($hasActive && $reachedCount < $totalCount)
                  <span class="al-badge al-badge--active"><i class="fas fa-satellite-dish"></i>{{ $activeCount - $reachedCount > 0 ? ($activeCount - $reachedCount).' ' : '' }}attivi</span>
                @endif
                @if($allInactive)
                  <span class="al-badge al-badge--inactive"><i class="fas fa-pause"></i>Inattivi</span>
                @endif
              @endif
            </div>
          </div>
        </div>

        {{-- Meta --}}
        <div class="al-card__meta">
          <span><i class="fas fa-calendar-alt mr-1"></i>{{ $departure->dep_date->format('d M Y') }}</span>
          @if($product->portFrom)
            <span><i class="fas fa-anchor mr-1"></i>{{ $product->portFrom->name }}</span>
          @endif
          <span><i class="fas fa-ship mr-1"></i>{{ $product->cruiseLine->name ?? '' }}</span>
        </div>

        {{-- Righe alert --}}
        <div class="al-rows">
          @foreach($groupAlerts->sortBy(fn($a) => array_search($a->category_code, ['IS','OS','BK','MS','SU'])) as $alert)
          @php
            $isReached  = !$isExpired && $alert->isPriceReached();
            $hasPrice   = !is_null($alert->current_price);
            $macroCode  = $alert->category_code;
            $macroLabel = $macroLabels[$macroCode] ?? $macroCode;
            $macroIcon  = $macroIcons[$macroCode]  ?? 'fa-bed';
            $progress   = $alert->getProgressPercentage();

            if ($isExpired)    $dotClass = 'al-row__dot--expired';
            elseif ($isReached) $dotClass = 'al-row__dot--reached';
            elseif ($alert->is_active) $dotClass = 'al-row__dot--active';
            else               $dotClass = 'al-row__dot--inactive';
          @endphp
          <div class="al-row {{ !$alert->is_active ? 'is-inactive' : '' }}"
               data-alert-id="{{ $alert->id }}"
               data-active="{{ $alert->is_active ? '1' : '0' }}"
               data-reached="{{ $isReached ? '1' : '0' }}">

            {{-- Categoria --}}
            <div class="al-row__cat">
              <span class="al-row__dot {{ $dotClass }}"></span>
              <i class="fas {{ $macroIcon }}" style="color:#1a7a8a; font-size:11px; flex-shrink:0;"></i>
              <span class="al-row__lbl">{{ $macroLabel }}</span>
            </div>

            {{-- Prezzi --}}
            <div class="al-row__prices">
              <span class="al-row__target">€&thinsp;{{ number_format($alert->target_price, 0, ',', '.') }}</span>
              @if($hasPrice)
                <span class="al-row__sep">→</span>
                <span class="al-row__current {{ $isReached ? 'al-row__current--ok' : 'al-row__current--wait' }}">
                  €&thinsp;{{ number_format($alert->current_price, 0, ',', '.') }}
                </span>
              @else
                <span class="al-row__sep">·</span>
                <span style="font-size:11px; color:#bbb;">N/D</span>
              @endif
            </div>

            {{-- Progress bar --}}
            @if($hasPrice && !$isExpired)
            <div class="al-row__bar">
              <div class="progress">
                <div class="progress-bar {{ $isReached ? 'bg-success' : '' }}"
                     role="progressbar"
                     style="width:{{ $progress }}%;{{ $isReached ? '' : 'background:#1a7a8a;' }}">
                </div>
              </div>
            </div>
            @endif

            {{-- Azioni per riga --}}
            <div class="al-row__acts">
              @if(!$isExpired)
              <button class="al-btn-icon al-btn-icon--toggle al-row-toggle {{ !$alert->is_active ? 'off' : '' }}"
                      data-alert-id="{{ $alert->id }}"
                      title="{{ $alert->is_active ? 'Disattiva' : 'Attiva' }}">
                <i class="fas fa-power-off"></i>
              </button>
              @endif
              <button class="al-btn-icon al-btn-icon--delete al-row-delete"
                      data-alert-id="{{ $alert->id }}"
                      title="Elimina questo alert">
                <i class="fas fa-trash"></i>
              </button>
            </div>

          </div>
          @endforeach
        </div>

        {{-- Footer --}}
        <div class="al-card__foot">
          <a href="{{ route('crociere.show', $departure->id) }}"
             class="btn btn-sm btn-outline-primary" style="font-size:12px;">
            <i class="fas fa-eye mr-1"></i>Vedi crociera
          </a>
          <div class="al-grp-acts">
            @if(!$isExpired)
            <button class="btn btn-sm {{ $hasActive ? 'btn-outline-warning' : 'btn-outline-success' }} al-toggle-all"
                    data-departure-id="{{ $departureId }}"
                    style="font-size:12px;">
              <i class="fas fa-power-off mr-1"></i>
              <span class="al-btn-txt">{{ $hasActive ? 'Disattiva tutti' : 'Attiva tutti' }}</span>
            </button>
            @endif
            <button class="btn btn-sm btn-outline-danger al-delete-all"
                    data-departure-id="{{ $departureId }}"
                    style="font-size:12px;">
              <i class="fas fa-trash mr-1"></i>Elimina tutti
            </button>
          </div>
        </div>

      </div>
    </div>
    @endforeach
  </div>

  @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function api(url, method) {
    return fetch(url, {
      method: method,
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(function(r) { return r.json(); });
  }

  // ── Ricalcola stato card dopo modifiche alle righe ──────────────────────
  function refreshCard(colEl) {
    var rows      = colEl.querySelectorAll('.al-row');
    var active    = 0, reached = 0;
    rows.forEach(function(r) {
      if (r.dataset.active  === '1') active++;
      if (r.dataset.reached === '1') reached++;
    });
    var total   = rows.length;
    var expired = colEl.dataset.expired === '1';

    colEl.dataset.hasActive  = active > 0  ? '1' : '0';
    colEl.dataset.hasReached = reached > 0 ? '1' : '0';

    if (expired) return; // non aggiornare badge per partenze passate

    // Summary badges
    var summary = colEl.querySelector('.al-card__summary');
    var html = '';
    if (reached > 0) {
      html += '<span class="al-badge al-badge--reached"><i class="fas fa-check-circle"></i>' + reached + '/' + total + ' raggiunti</span> ';
    }
    var activeNonReached = active - reached;
    if (activeNonReached > 0) {
      html += '<span class="al-badge al-badge--active"><i class="fas fa-satellite-dish"></i>' + (activeNonReached > 0 ? activeNonReached + ' ' : '') + 'attivi</span>';
    }
    if (active === 0 && reached === 0) {
      html = '<span class="al-badge al-badge--inactive"><i class="fas fa-pause"></i>Inattivi</span>';
    }
    summary.innerHTML = html;

    // Toggle-all button
    var toggleBtn = colEl.querySelector('.al-toggle-all');
    if (toggleBtn) {
      var txt = toggleBtn.querySelector('.al-btn-txt');
      if (active > 0) {
        txt.textContent = 'Disattiva tutti';
        toggleBtn.className = 'btn btn-sm btn-outline-warning al-toggle-all';
      } else {
        txt.textContent = 'Attiva tutti';
        toggleBtn.className = 'btn btn-sm btn-outline-success al-toggle-all';
      }
    }
  }

  // ── Dot di stato per una riga ──────────────────────────────────────────
  function rowDotClass(isActive, isReached, isExpired) {
    if (isExpired)  return 'al-row__dot--expired';
    if (isReached)  return 'al-row__dot--reached';
    if (isActive)   return 'al-row__dot--active';
    return 'al-row__dot--inactive';
  }

  function checkEmpty() {
    if (!document.querySelector('.al-grp-col')) { location.reload(); }
  }

  // ── Event delegation sul grid ──────────────────────────────────────────
  var grid = document.getElementById('al-cards-grid');
  if (!grid) return;

  grid.addEventListener('click', function(e) {
    var rowDelete  = e.target.closest('.al-row-delete');
    var rowToggle  = e.target.closest('.al-row-toggle');
    var grpDelete  = e.target.closest('.al-delete-all');
    var grpToggle  = e.target.closest('.al-toggle-all');

    // ── Elimina singola riga ────────────────────────────────────────────
    if (rowDelete) {
      if (!confirm('Eliminare questo alert?')) return;
      var alertId = rowDelete.dataset.alertId;
      api('/alert-prezzi/' + alertId, 'DELETE').then(function(data) {
        if (!data.success) return;
        var row   = rowDelete.closest('.al-row');
        var colEl = rowDelete.closest('.al-grp-col');
        row.remove();
        if (!colEl.querySelector('.al-row')) {
          colEl.remove(); checkEmpty();
        } else {
          refreshCard(colEl);
        }
      });
    }

    // ── Toggle singola riga ─────────────────────────────────────────────
    if (rowToggle) {
      var alertId = rowToggle.dataset.alertId;
      api('/alert-prezzi/' + alertId + '/toggle', 'POST').then(function(data) {
        if (!data.success) return;
        var row    = rowToggle.closest('.al-row');
        var colEl  = rowToggle.closest('.al-grp-col');
        var isNow  = data.is_active;
        var dot    = row.querySelector('.al-row__dot');

        row.dataset.active = isNow ? '1' : '0';
        row.classList.toggle('is-inactive', !isNow);
        rowToggle.classList.toggle('off', !isNow);
        rowToggle.title = isNow ? 'Disattiva' : 'Attiva';

        dot.className = 'al-row__dot ' + rowDotClass(isNow, row.dataset.reached === '1', colEl.dataset.expired === '1');
        refreshCard(colEl);
      });
    }

    // ── Elimina tutti per partenza ──────────────────────────────────────
    if (grpDelete) {
      if (!confirm('Eliminare tutti gli alert per questa crociera?')) return;
      var departureId = grpDelete.dataset.departureId;
      api('/api/alerts/departure/' + departureId, 'DELETE').then(function(data) {
        if (!data.success) return;
        var colEl = grpDelete.closest('.al-grp-col');
        colEl.style.transition = 'opacity .3s, transform .3s';
        colEl.style.opacity = '0';
        colEl.style.transform = 'scale(.97)';
        setTimeout(function() { colEl.remove(); checkEmpty(); }, 300);
      });
    }

    // ── Toggle tutti per partenza ───────────────────────────────────────
    if (grpToggle) {
      var departureId = grpToggle.dataset.departureId;
      api('/api/alerts/departure/' + departureId + '/toggle-all', 'POST').then(function(data) {
        if (!data.success) return;
        var isNow = data.is_active;
        var colEl = grpToggle.closest('.al-grp-col');
        colEl.dataset.hasActive = isNow ? '1' : '0';

        colEl.querySelectorAll('.al-row').forEach(function(row) {
          var expired  = colEl.dataset.expired === '1';
          var isReached = row.dataset.reached === '1';
          row.dataset.active = isNow ? '1' : '0';
          row.classList.toggle('is-inactive', !isNow);
          var dot = row.querySelector('.al-row__dot');
          if (dot) dot.className = 'al-row__dot ' + rowDotClass(isNow, isReached, expired);
          var btn = row.querySelector('.al-row-toggle');
          if (btn) {
            btn.classList.toggle('off', !isNow);
            btn.title = isNow ? 'Disattiva' : 'Attiva';
          }
        });
        refreshCard(colEl);
      });
    }
  });

  // ── Filtri toolbar ──────────────────────────────────────────────────────
  document.querySelectorAll('.al-filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.al-filter-btn').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
      var filter = this.dataset.filter;
      document.querySelectorAll('.al-grp-col').forEach(function(col) {
        var show = true;
        if (filter === 'active')   show = col.dataset.hasActive  === '1' && col.dataset.expired === '0';
        if (filter === 'reached')  show = col.dataset.hasReached === '1';
        if (filter === 'inactive') show = col.dataset.hasActive  === '0' && col.dataset.expired === '0';
        if (filter === 'expired')  show = col.dataset.expired    === '1';
        col.style.display = show ? '' : 'none';
      });
    });
  });

  // ── Elimina scaduti ─────────────────────────────────────────────────────
  var btnExpired = document.getElementById('al-delete-expired');
  if (btnExpired) {
    btnExpired.addEventListener('click', function() {
      if (!confirm('Eliminare tutti gli alert per crociere già partite?')) return;
      api('/api/alerts/expired', 'DELETE').then(function(data) {
        if (data.success) { location.reload(); }
      });
    });
  }

  // ── Elimina inattivi ────────────────────────────────────────────────────
  var btnInactive = document.getElementById('al-delete-inactive');
  if (btnInactive) {
    btnInactive.addEventListener('click', function() {
      if (!confirm('Eliminare tutti gli alert disattivati?')) return;
      api('/api/alerts/inactive', 'DELETE').then(function(data) {
        if (data.success) { location.reload(); }
      });
    });
  }

  // ── Animazione progress bar ─────────────────────────────────────────────
  document.querySelectorAll('.progress-bar').forEach(function(bar) {
    var w = bar.style.width;
    bar.style.width = '0';
    setTimeout(function() { bar.style.transition = 'width 1s ease'; bar.style.width = w; }, 120);
  });

  // ── Animazione card ─────────────────────────────────────────────────────
  document.querySelectorAll('.al-card').forEach(function(card, i) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(14px)';
    setTimeout(function() {
      card.style.transition = 'opacity .3s ease, transform .3s ease, box-shadow .2s';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, i * 70);
  });
});
</script>
@endsection
