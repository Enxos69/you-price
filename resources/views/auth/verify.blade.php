@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-envelope-open-text me-2"></i>
                        Verifica il tuo indirizzo Email
                    </div>

                    <div class="card-body">
                        {{-- Messaggio registrazione completata --}}
                        @if (session('registered'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Ottimo!</strong> {{ session('registered') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Messaggio warning da login --}}
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attenzione!</strong> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Messaggio reinvio email --}}
                        @if (session('resent'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-paper-plane me-2"></i>
                                <strong>Email inviata!</strong> Controlla la tua casella di posta.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="verification-instructions">
                            <p class="mb-3">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Prima di procedere, controlla la tua email per il link di verifica.
                            </p>

                            <p class="mb-3">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <strong>Non hai ricevuto l'email?</strong>
                            </p>

                            <div class="d-flex gap-2 align-items-center">
                                <span>Clicca qui per richiederne un'altra:</span>
                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-redo me-1"></i>
                                        Reinvia Email
                                    </button>
                                </form>
                            </div>

                            <hr class="my-4">

                            <div class="text-muted small">
                                <p class="mb-2">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Suggerimenti:</strong>
                                </p>
                                <ul class="mb-0">
                                    <li>Controlla anche la cartella SPAM o Posta Indesiderata</li>
                                    <li>Verifica di aver inserito l'email corretta durante la registrazione</li>
                                    <li>Il link di verifica è valido per 60 minuti</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .verification-instructions {
            padding: 1rem;
        }

        .verification-instructions ul {
            list-style-type: none;
            padding-left: 1.5rem;
        }

        .verification-instructions ul li:before {
            content: "→";
            margin-right: 0.5rem;
            color: #84bc00;
            font-weight: bold;
        }

        .card-header {
            background: linear-gradient(135deg, #006170 0%, #84bc00 100%);
            color: white;
            font-weight: 600;
        }
    </style>
@endsection
