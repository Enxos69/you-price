<!-- resources/views/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">  
    Benvenuto, {{ Auth::user()->name }} ({{ Auth::user()->roles->name }})

    <!-- Main Content -->
    <div class="jumbotron text-center mt-5 pt-5">
        <h1>Benvenuto nel nostro sito, Utente!</h1>
        <p>Questa è la pagina iniziale. Accedi o registrati per continuare.</p>
    </div>      
</div>
@endsection
