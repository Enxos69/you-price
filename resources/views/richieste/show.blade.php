@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Dettaglio Richiesta del {{ \Carbon\Carbon::parse($richiesta->created_at)->format('d/m/Y') }}</h1>
        <p><strong>Tipo Richiesta:</strong> {{ $richiesta->tipo->tipo_richiesta }}</p>
        <p><strong>Stato Richiesta:</strong> {{ $richiesta->stato->stato_richiesta }}</p>
        <p><strong>Data Fine Validità:</strong> {{ \Carbon\Carbon::parse($richiesta->data_fine_validita)->format('d/m/Y') }}</p>
        <p><strong>Budget:</strong> {{ $richiesta->budget }}</p>
        <p><strong>Rating:</strong> {{ $richiesta->rating ?? 'N/A' }}</p>
        <p><strong>Note:</strong> {{ $richiesta->note ?? 'N/A' }}</p>

        <!-- Container per l'immagine del tachimetro e la lancetta -->
        <div style="position: relative; width: 255px; height: 120px; margin-bottom: 20px">
            <img src="{{ asset('/img/tachimetro.png') }}" alt="Tachimetro" style="width: 100%; height: auto; display: block;">
            <div id="pointer" style="position: absolute; width: 0; height: 0; border-left: 5px solid transparent; 
            border-right: 5px solid transparent; border-bottom: 125px solid black; transform-origin: bottom center; top: 0; left: 50%; transform: rotate(0deg);"></div>
        </div>

        @if ($richiesta->id_richiesta_stato == 1)
            <!-- Stato "inserita" -->
            <a href="{{ route('richieste.edit', $richiesta->id) }}" class="btn btn-warning">Modifica</a>
            <form action="{{ route('richieste.destroy', $richiesta->id) }}" method="POST" style="display:inline;"
                onsubmit="return confirm('Sei sicuro di voler annullare questa richiesta?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" title="Annulla">
                    Annulla
                </button>
            </form>
            <a href="{{ route('richieste.index') }}" class="btn btn-secondary">Indietro</a>
        @else
            <a href="{{ route('richieste.index') }}" class="btn btn-secondary">Indietro</a>
        @endif
    </div>

    <script>
        window.onload = function () {
            const dataValue = {{ $richiesta->rating ?? 0 }}; // Sostituisci con il valore dinamico (da 0 a 100)
            const angle = (dataValue / 100) * 180; // Converti il valore percentuale in un angolo tra 0 e 180 gradi

            // Aggiungi la lancetta
            const pointer = document.getElementById('pointer');
            const rotation = angle;

            // Aggiorna la trasformazione della lancetta
            pointer.style.transform = `rotate(${rotation - 90}deg)`; // Ruota la lancetta per indicare il valore
        };
    </script>
@endsection
