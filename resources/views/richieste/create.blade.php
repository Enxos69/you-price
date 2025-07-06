@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Fai una nuova Richiesta</div>

                    <div class="card-body">
                        <form action="{{ route('richieste.store') }}" id="richiestaForm" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="form-group">
                                <label for="id_richiesta_tipo">Tipo Richiesta</label>
                                <select name="id_richiesta_tipo" id="id_richiesta_tipo" class="form-control" required>
                                    @foreach (App\Models\RichiestaTipo::all() as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->tipo_richiesta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_fine_validita">Data Fine Validità</label>
                                <input type="date" name="data_fine_validita" id="data_fine_validita" class="form-control"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="budget">Budget</label>
                                <input type="number" step="0.01" name="budget" id="budget" class="form-control"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="note">Note</label>
                                <textarea name="note" id="note" class="form-control"></textarea>
                            </div>
                            <input type="button" onclick="create()" class="btn btn-primary"value="Salva">
                            <a href="{{ route('richieste.index') }}" class="btn btn-secondary">Annulla</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>     
@endsection
@section('scripts')
    @parent
    @include('richieste.assets.js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
@endsection
 