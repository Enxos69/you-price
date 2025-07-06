@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Riepilogo Richieste</h1>
            <a href="{{ route('richieste.create') }}" class="btn btn-primary">
                Fai una Nuova Richiesta
            </a>
        </div>
        <table id="richiesteTable" class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo Richiesta</th>
                    <th>Stato Richiesta</th>
                    <th>Data Fine Validità</th>
                    <th>Budget</th>
                    <th>Rating</th>
                    <th>Note</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($richieste as $richiesta)
                    <tr>
                        <td>{{ $richiesta->id }}</td>
                        <td>{{ $richiesta->tipo->tipo_richiesta ?? 'N/A' }}</td>
                        <td>{{ $richiesta->stato->stato_richiesta ?? 'N/A' }}</td>
                        <td>{{ $richiesta->data_fine_validita }}</td>
                        <td>{{ $richiesta->budget }}</td>
                        <td>{{ $richiesta->rating ?? 'N/A' }}</td>
                        <td>{{ $richiesta->note ?? 'N/A' }}</td>
                        <td>
                            @if ($richiesta->id_richiesta_stato == 1)
                                <!-- Stato "inserita" -->

                                <a href="{{ route('richieste.show', $richiesta->id) }}" class="btn btn-info btn-sm"
                                    title="Dettaglio">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('richieste.edit', $richiesta->id) }}" class="btn btn-warning btn-sm"
                                    title="Modifica">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('richieste.destroy', $richiesta->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Sei sicuro di voler annullare questa richiesta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Annulla">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('richieste.show', $richiesta->id) }}" class="btn btn-info btn-sm"
                                    title="Dettaglio">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#richiesteTable').DataTable({
                // Traduzione in italiano
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Italian.json"
                },
            });
        });
    </script>
@endsection
