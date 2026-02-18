{{-- resources/views/legal/cookie.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="legal-page">
    <div class="legal-hero">
        <div class="legal-hero-inner">
            <div class="legal-icon"><i class="fas fa-cookie-bite"></i></div>
            <h1>Cookie Policy</h1>
            <p>Ultimo aggiornamento: {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="legal-body">
        <div class="legal-toc">
            <h3><i class="fas fa-list"></i> Indice</h3>
            <ol>
                <li><a href="#cosa">Cosa sono i cookie</a></li>
                <li><a href="#tecnici">Cookie tecnici</a></li>
                <li><a href="#analitici">Cookie analitici</a></li>
                <li><a href="#terze-parti">Cookie di terze parti</a></li>
                <li><a href="#gestione">Come gestire i cookie</a></li>
                <li><a href="#aggiornamenti">Aggiornamenti</a></li>
            </ol>
        </div>

        <div class="legal-content">

            <section id="cosa">
                <h2>1. Cosa sono i cookie</h2>
                <p>I cookie sono piccoli file di testo che i siti web salvano sul tuo dispositivo durante la navigazione. Vengono utilizzati per far funzionare il sito correttamente, ricordare le tue preferenze e raccogliere informazioni statistiche aggregate sull'utilizzo del servizio.</p>
            </section>

            <section id="tecnici">
                <h2>2. Cookie tecnici</h2>
                <p>Questi cookie sono necessari al funzionamento del sito e non possono essere disabilitati. Non raccolgono informazioni personali identificabili.</p>
                <div class="legal-table-wrap">
                    <table class="legal-table">
                        <thead>
                            <tr><th>Nome</th><th>Scopo</th><th>Durata</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>XSRF-TOKEN</code></td><td>Protezione CSRF (sicurezza dei form)</td><td>Sessione</td></tr>
                            <tr><td><code>youprice_session</code></td><td>Gestione sessione utente</td><td>2 ore</td></tr>
                            <tr><td><code>remember_web_*</code></td><td>Funzione "Ricordami" al login</td><td>30 giorni</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="analitici">
                <h2>3. Cookie analitici</h2>
                <p>Utilizziamo cookie analitici per comprendere come gli utenti interagiscono con il sito, quali pagine vengono visitate più frequentemente e da dove provengono le visite. I dati raccolti sono aggregati e anonimi.</p>
                <div class="legal-table-wrap">
                    <table class="legal-table">
                        <thead>
                            <tr><th>Nome</th><th>Scopo</th><th>Durata</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>_ga</code></td><td>Distingue gli utenti unici (Google Analytics)</td><td>2 anni</td></tr>
                            <tr><td><code>_ga_*</code></td><td>Mantiene lo stato della sessione (Google Analytics)</td><td>2 anni</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="terze-parti">
                <h2>4. Cookie di terze parti</h2>
                <p>Il sito può includere contenuti o funzionalità di terze parti che potrebbero impostare propri cookie. Queste terze parti hanno le proprie politiche sulla privacy, sulle quali non abbiamo controllo:</p>
                <ul>
                    <li><strong>Google Analytics</strong> — analisi del traffico (<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>)</li>
                    <li><strong>Font Google</strong> — caricamento dei font (<a href="https://developers.google.com/fonts/faq/privacy" target="_blank" rel="noopener">Privacy FAQ</a>)</li>
                </ul>
            </section>

            <section id="gestione">
                <h2>5. Come gestire i cookie</h2>
                <p>Puoi controllare e/o eliminare i cookie tramite le impostazioni del tuo browser. Di seguito i link alle istruzioni per i browser più comuni:</p>
                <ul>
                    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/it/kb/Gestione%20dei%20cookie" target="_blank" rel="noopener">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Apple Safari</a></li>
                    <li><a href="https://support.microsoft.com/it-it/help/17442" target="_blank" rel="noopener">Microsoft Edge</a></li>
                </ul>
                <div class="legal-notice">
                    <i class="fas fa-info-circle"></i>
                    Disabilitare i cookie tecnici può compromettere il corretto funzionamento del sito, incluso il mantenimento della sessione di login.
                </div>
            </section>

            <section id="aggiornamenti">
                <h2>6. Aggiornamenti</h2>
                <p>Questa Cookie Policy può essere aggiornata periodicamente. In caso di modifiche sostanziali, ne daremo comunicazione tramite avviso in evidenza sul sito. Ti invitiamo a controllare questa pagina periodicamente.</p>
                <p>Per ulteriori informazioni sul trattamento dei dati personali consulta la nostra <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
            </section>

        </div>
    </div>
</div>
@endsection
