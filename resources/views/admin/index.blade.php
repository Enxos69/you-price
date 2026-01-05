<!-- resources/views/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">  
    Benvenuto, {{ Auth::user()->name }} ({{ Auth::user()->roles->name }})

    <!-- Main Content -->
    <div class="jumbotron text-center mt-5 pt-5">
        <h1>Benvenuto nel nostro sito, Amministratore!</h1>
        <p>Questa è la pagina iniziale.... in fase di sviluppo</p>
    </div>      
</div>
@endsection
