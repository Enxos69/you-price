<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
        .header { background: #003580; color: #fff; padding: 20px; text-align: center; }
        .content { padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f0f4ff; text-align: left; padding: 10px 12px; font-size: 12px; text-transform: uppercase; color: #555; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        .badge { display: inline-block; background: #003580; color: #fff; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .footer { padding: 16px 24px; font-size: 12px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0">Nuova Richiesta di Quotazione Personalizzata</h2>
        <p style="margin:6px 0 0">Richiesta #{{ $quoteRequest->id }} — {{ $quoteRequest->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="content">
        <h3>Dati dell'utente</h3>
        <table>
            <tr><th>Nome</th><td>{{ $quoteRequest->user->name }} {{ $quoteRequest->user->surname ?? '' }}</td></tr>
            <tr><th>Email</th><td><a href="mailto:{{ $quoteRequest->user->email }}">{{ $quoteRequest->user->email }}</a></td></tr>
            @if($quoteRequest->phone)
            <tr><th>Telefono</th><td>{{ $quoteRequest->phone }}</td></tr>
            @endif
            <tr><th>Registrato dal</th><td>{{ $quoteRequest->user->created_at->format('d/m/Y') }}</td></tr>
        </table>

        <h3 style="margin-top:24px">Dettagli della richiesta</h3>
        <table>
            <tr><th>Periodo di viaggio</th><td>{{ $quoteRequest->date_range }}</td></tr>
            <tr><th>Budget totale</th><td><strong>€ {{ number_format($quoteRequest->budget, 0, ',', '.') }}</strong></td></tr>
            <tr><th>Numero partecipanti</th><td>{{ $quoteRequest->participants }}</td></tr>
            <tr><th>Budget per persona</th><td>€ {{ number_format($quoteRequest->budget / $quoteRequest->participants, 0, ',', '.') }}</td></tr>
            @if($quoteRequest->port_start)
            <tr><th>Porto di imbarco</th><td>{{ $quoteRequest->port_start }}</td></tr>
            @endif
            @if($quoteRequest->notes)
            <tr><th>Note aggiuntive</th><td>{{ $quoteRequest->notes }}</td></tr>
            @endif
            <tr><th>Stato</th><td><span class="badge">{{ ucfirst($quoteRequest->status) }}</span></td></tr>
        </table>
    </div>

    <div class="footer">
        Questa email è stata generata automaticamente da You-Price. Non rispondere a questo messaggio.
    </div>
</body>
</html>
