<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 14px; margin: 0; padding: 0; }
        .header { background: #003580; color: #fff; padding: 28px 24px; text-align: center; }
        .content { padding: 28px 24px; }
        .highlight { background: #f0f4ff; border-left: 4px solid #003580; padding: 14px 16px; border-radius: 0 4px 4px 0; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        td.label { color: #666; width: 40%; padding: 7px 0; font-size: 13px; }
        td.value { padding: 7px 0; font-weight: 600; font-size: 13px; }
        .footer { padding: 16px 24px; font-size: 12px; color: #999; border-top: 1px solid #eee; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0 0 6px">Abbiamo ricevuto la tua richiesta! ✓</h2>
        <p style="margin:0;opacity:.85">Il nostro team ti risponderà nel più breve tempo possibile</p>
    </div>

    <div class="content">
        <p>Ciao <strong>{{ $quoteRequest->user->name }}</strong>,</p>
        <p>
            Grazie per aver richiesto una quotazione personalizzata su <strong>You-Price</strong>.
            Abbiamo preso in carico la tua richiesta e la nostra squadra la analizzerà per trovare l'offerta perfetta per te.
        </p>

        <div class="highlight">
            <strong>Riepilogo della tua richiesta</strong>
        </div>

        <table>
            <tr>
                <td class="label">Periodo di viaggio</td>
                <td class="value">{{ $quoteRequest->date_range }}</td>
            </tr>
            <tr>
                <td class="label">Budget totale</td>
                <td class="value">€ {{ number_format($quoteRequest->budget, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Partecipanti</td>
                <td class="value">{{ $quoteRequest->participants }} {{ $quoteRequest->participants == 1 ? 'persona' : 'persone' }}</td>
            </tr>
            @if($quoteRequest->port_start)
            <tr>
                <td class="label">Porto di imbarco preferito</td>
                <td class="value">{{ $quoteRequest->port_start }}</td>
            </tr>
            @endif
            @if($quoteRequest->notes)
            <tr>
                <td class="label">Le tue note</td>
                <td class="value">{{ $quoteRequest->notes }}</td>
            </tr>
            @endif
        </table>

        <p style="margin-top:24px">
            Se hai bisogno di aggiornare la richiesta o hai domande urgenti, puoi scriverci a
            <a href="mailto:info@you-priced.com">info@you-priced.com</a>.
        </p>

        <p>A presto,<br><strong>Il team di You-Price</strong></p>
    </div>

    <div class="footer">
        Hai ricevuto questa email perché sei registrato su You-Price. Non rispondere a questa email.
    </div>
</body>
</html>
