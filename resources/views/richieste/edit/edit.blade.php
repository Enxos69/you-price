@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Modifica la tua Richiesta</div>

                    <div class="card-body">
                        <form action="{{ route('richieste.store') }}" id="richiestaForm" method="POST"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            <input type="hidden" name="id" id="id"
                                value="{{ isset($richiesta->id) ? $richiesta->id : '' }}"> 
                            <input type="hidden" name="id_richiesta_stato" id="id_richiesta_stato"
                                value="{{ isset($richiesta->id_richiesta_stato) ? $richiesta->id_richiesta_stato : '' }}">
                            <div class="form-group">
                                <label for="id_richiesta_tipo">Tipo Richiesta</label>
                                <select name="id_richiesta_tipo" id="id_richiesta_tipo" class="form-control" required>
                                    @foreach (App\Models\RichiestaTipo::all() as $tipo)
                                        <option value="{{ $tipo->id }}"
                                            @if ($tipo->id == $richiesta->id_richiesta_tipo) selected @endif>{{ $tipo->tipo_richiesta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_fine_validita">Data Fine Validità</label>
                                <input type="date" name="data_fine_validita" id="data_fine_validita" class="form-control"
                                    required value="{{ $richiesta->data_fine_validita ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label for="budget">Budget</label>
                                <input type="number" step="0.01" name="budget" id="budget" class="form-control"
                                    required value="{{ $richiesta->budget ?? '' }}">
                            </div>

                            <div class="form-group">
                                <label for="note">Note</label>
                                <textarea name="note" id="note" class="form-control">{{ $richiesta->note ?? '' }}</textarea>
                            </div>
                            <input type="button" onclick="update()" class="btn btn-primary"value="Aggiorna">
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
    @include('richieste.edit.assets.js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
@endsection
