<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 14px; margin: 0; padding: 0; }
        .header { background: #1a7a8a; color: #fff; padding: 28px 24px; text-align: center; }
        .content { padding: 28px 24px; }
        .highlight { background: #f0faf9; border-left: 4px solid #1a7a8a; padding: 14px 16px; border-radius: 0 4px 4px 0; margin: 16px 0; }
        .price-box { background: #f7fff7; border: 2px solid #4caf50; border-radius: 6px; padding: 18px 20px; margin: 20px 0; text-align: center; }
        .price-current { font-size: 32px; font-weight: 700; color: #4caf50; }
        .price-target { font-size: 14px; color: #666; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td.label { color: #666; width: 45%; padding: 7px 0; font-size: 13px; }
        td.value { padding: 7px 0; font-weight: 600; font-size: 13px; }
        .cta { text-align: center; margin: 28px 0 16px; }
        .btn { background: #1a7a8a; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 4px; font-size: 15px; font-weight: 600; display: inline-block; }
        .footer { padding: 16px 24px; font-size: 12px; color: #999; border-top: 1px solid #eee; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h2 style="margin:0 0 6px">Obiettivo raggiunto! ✓</h2>
    <p style="margin:0;opacity:.85">Il prezzo che stavi aspettando è finalmente disponibile</p>
</div>

<div class="content">
    <p>Ciao <strong>{{ $alert->user->name }}</strong>,</p>

    @php
        $departure = $alert->departure;
        $product   = $departure?->product;
        $ship      = $product?->ship;
        $discount  = $alert->getDiscountPercentage();
    @endphp

    @if($alert->alert_type === 'percentage_discount')
    <p>
        L'alert che avevi impostato per uno <strong>sconto del {{ $alert->percentage_threshold }}%</strong>
        sulla crociera <strong>{{ $product?->cruise_name }}</strong> è stato raggiunto.
    </p>
    @else
    <p>
        Il prezzo della crociera <strong>{{ $product?->cruise_name }}</strong>
        è sceso al di sotto del tuo obiettivo di <strong>€ {{ number_format($alert->target_price, 0, ',', '.') }}</strong>.
    </p>
    @endif

    <div class="price-box">
        <div class="price-current">€ {{ number_format($alert->current_price, 0, ',', '.') }}</div>
        <div class="price-target">
            Prezzo attuale &mdash; obiettivo: € {{ number_format($alert->target_price, 0, ',', '.') }}
            @if($discount > 0)
                &mdash; <span style="color:#4caf50;font-weight:600">{{ $discount }}% di risparmio</span>
            @endif
        </div>
    </div>

    <div class="highlight">
        <strong>Dettagli della partenza</strong>
    </div>

    <table>
        @if($product?->cruise_name)
        <tr>
            <td class="label">Crociera</td>
            <td class="value">{{ $product->cruise_name }}</td>
        </tr>
        @endif
        @if($ship?->name)
        <tr>
            <td class="label">Nave</td>
            <td class="value">{{ $ship->name }}</td>
        </tr>
        @endif
        @if($departure?->dep_date)
        <tr>
            <td class="label">Data di partenza</td>
            <td class="value">{{ \Carbon\Carbon::parse($departure->dep_date)->format('d/m/Y') }}</td>
        </tr>
        @endif
        @if($departure?->duration)
        <tr>
            <td class="label">Durata</td>
            <td class="value">{{ $departure->duration }} notti</td>
        </tr>
        @endif
        <tr>
            <td class="label">Categoria cabina</td>
            <td class="value">{{ $alert->category_code }}</td>
        </tr>
    </table>

    <p style="margin-top:20px;font-size:13px;color:#666;">
        I prezzi delle crociere possono variare rapidamente. Ti consigliamo di verificare la disponibilità il prima possibile.
    </p>

    <div class="cta">
        <a href="{{ url('/') }}" class="btn">Vai a You-Price</a>
    </div>

    <p>A presto,<br><strong>Il team di You-Price</strong></p>
</div>

<div class="footer">
    Hai ricevuto questa email perché hai attivato un alert prezzi su You-Price.<br>
    Puoi gestire i tuoi alert dalla tua <a href="{{ url('/alert-prezzi') }}" style="color:#1a7a8a">area personale</a>.
</div>

</body>
</html>
