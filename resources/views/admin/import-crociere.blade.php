@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Importa Crociere</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <form action="{{ route('cruises.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="csv_file">Seleziona file CSV</label>
            <input type="file" class="form-control" name="csv_file" required>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Importa</button>
    </form>
</div>
@endsection
