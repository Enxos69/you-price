@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Modifica Utente</div>

                    <div class="card-body">
                        <form action="{{ route('users.update') }}" method="POST" enctype="multipart/form-data"
                            id="edit-user-form">
                            @csrf
                            <div class="form-group">
                                <label for="name">Nome</label>
                                <input type="text" name="name" class="form-control" id="name"
                                    value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="surname">Cognome</label>
                                <input type="text" name="surname" class="form-control" id="surname"
                                    value="{{ old('surname', $user->surname) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control" id="email"
                                    value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="role">Ruolo</label>
                                <select name="role" class="form-control" id="role" required>
                                    <option value="1" {{ $user->role == 1 ? 'selected' : '' }}>Amministratore</option>
                                    <option value="2" {{ $user->role == 2 ? 'selected' : '' }}>Utente</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="abilitato">Abilitato</label>
                                <select name="abilitato" class="form-control" id="abilitato" required>
                                    <option value="1" {{ $user->abilitato == 1 ? 'selected' : '' }}>Abilitato</option>
                                    <option value="0" {{ $user->abilitato == 0 ? 'selected' : '' }}>Disabilitato
                                    </option>
                                </select>
                            </div>
                            <input type="hidden" name="id" id="id"
                            value="{{ isset($user->id) ? $user->id : '' }}">
                            {{--  <button type="submit" class="btn btn-primary">Aggiorna</button> --}}

                            <input type="button" onclick="editUser()" class="btn btn-primary" value="Aggiorna">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    @include('admin.users.assets.js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
@endsection
