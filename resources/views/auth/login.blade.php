@extends('layouts.app')
@section('body-class', 'auth-layout')
@section('content')
    <!-- Messaggio Registrazione Completata -->
    @if (session('registered'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Registrazione Completata!',
                html: `
                    <div style="text-align: left; padding: 1rem;">
                        <p style="margin-bottom: 1rem;"><i class="fas fa-check-circle" style="color: #28a745; font-size: 1.2rem;"></i> {{ session('registered') }}</p>
                        <hr style="margin: 1rem 0;">
                        <p style="margin-bottom: 0.5rem; font-weight: 600;"><i class="fas fa-info-circle" style="color: #0dcaf0;"></i> Prossimi passi:</p>
                        <ol style="margin-left: 1.5rem; text-align: left;">
                            <li style="margin-bottom: 0.5rem;">Controlla la tua casella email</li>
                            <li style="margin-bottom: 0.5rem;">Clicca sul link di verifica</li>
                            <li style="margin-bottom: 0.5rem;">Torna qui per effettuare il login</li>
                        </ol>
                        <p style="margin-top: 1rem; font-size: 0.9rem; color: #6c757d;">
                            <i class="fas fa-lightbulb"></i> <strong>Suggerimento:</strong> Controlla anche la cartella SPAM
                        </p>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'Ho capito',
                confirmButtonColor: '#84bc00',
                width: '600px'
            });
        </script>
    @endif

    <!-- Messaggio Email Verificata con successo -->
    @if (session('verified'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Email Verificata!',
                text: "{{ session('verified') }}",
                icon: 'success',
                confirmButtonText: 'Accedi ora',
                confirmButtonColor: '#28a745'
            });
        </script>
    @endif

    <!-- Messaggio Warning - Email da verificare -->
    @if (session('warning'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Email da Verificare',
                html: `
                    <div style="text-align: left; padding: 1rem;">
                        <p style="margin-bottom: 1rem;"><i class="fas fa-exclamation-triangle" style="color: #ffc107; font-size: 1.2rem;"></i> {{ session('warning') }}</p>
                        <hr style="margin: 1rem 0;">
                        <p style="margin-bottom: 0.5rem; font-weight: 600;"><i class="fas fa-envelope"></i> Cosa devi fare:</p>
                        <ol style="margin-left: 1.5rem; text-align: left;">
                            <li style="margin-bottom: 0.5rem;">Controlla la tua email</li>
                            <li style="margin-bottom: 0.5rem;">Clicca sul link di verifica</li>
                            <li style="margin-bottom: 0.5rem;">Torna qui per il login</li>
                        </ol>
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-radius: 8px;">
                            <p style="margin: 0; font-size: 0.9rem;">
                                <i class="fas fa-redo"></i> <strong>Non hai ricevuto l'email?</strong><br>
                                <a href="{{ route('verification.notice') }}" style="color: #0d6efd; text-decoration: underline;">
                                    Clicca qui per reinviare
                                </a>
                            </p>
                        </div>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Ho capito',
                confirmButtonColor: '#ffc107',
                width: '600px'
            });
        </script>
    @endif

    <!-- Messaggio Email già verificata -->
    @if (session('status'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Attenzione',
                text: "{!! session('status') !!}",
                icon: 'info',
                confirmButtonText: 'Ok'
            });
        </script>
    @endif

    <!-- Messaggio Errore -->
    @if (session('error'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Errore',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        </script>
    @endif

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('messages.login') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="row mb-3">
                                <label for="email"
                                    class="col-md-4 col-form-label text-md-end">{{ __('messages.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password"
                                    class="col-md-4 col-form-label text-md-end">{{ __('messages.password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required autocomplete="current-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                            {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('messages.remember_me') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('messages.login') }}
                                    </button>

                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('messages.forgot_password') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection