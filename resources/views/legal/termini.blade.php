{{-- resources/views/legal/termini.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="legal-page">
    <div class="legal-hero">
        <div class="legal-hero-inner">
            <div class="legal-icon"><i class="fas fa-file-contract"></i></div>
            <h1>Termini di Servizio</h1>
            <p>Ultimo aggiornamento: {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="legal-body">
        <div class="legal-toc">
            <h3><i class="fas fa-list"></i> Indice</h3>
            <ol>
                <li><a href="#servizio">Il servizio</a></li>
                <li><a href="#account">Registrazione e account</a></li>
                <li><a href="#utilizzo">Utilizzo corretto</a></li>
                <li><a href="#preventivi">Preventivi e prenotazioni</a></li>
                <li><a href="#proprieta">Proprietà intellettuale</a></li>
                <li><a href="#responsabilita">Limitazione di responsabilità</a></li>
                <li><a href="#modifiche">Modifiche al servizio</a></li>
                <li><a href="#legge">Legge applicabile</a></li>
            </ol>
        </div>

        <div class="legal-content">

            <section id="servizio">
                <h2>1. Il servizio</h2>
                <p>You-Price (<strong>you-price.it</strong>) è una piattaforma di ricerca e confronto crociere che consente agli utenti di effettuare ricerche sulla base del proprio budget e ricevere preventivi personalizzati da parte degli operatori del settore.</p>
                <p>You-Price <strong>non è un'agenzia di viaggi</strong> e non effettua prenotazioni dirette. Il servizio si limita a mettere in contatto gli utenti con le offerte disponibili e a facilitare la raccolta di preventivi.</p>
            </section>

            <section id="account">
                <h2>2. Registrazione e account</h2>
                <p>Per accedere a funzionalità avanzate (dashboard, preferiti, alert prezzi, ricezione preventivi via email) è necessario creare un account. Registrandoti dichiari di:</p>
                <ul>
                    <li>Avere almeno 18 anni di età.</li>
                    <li>Fornire informazioni veritiere, accurate e complete.</li>
                    <li>Mantenere aggiornate le informazioni del tuo profilo.</li>
                    <li>Essere l'unico responsabile della riservatezza delle tue credenziali di accesso.</li>
                </ul>
                <p>You-Price si riserva il diritto di sospendere o cancellare account che violino questi Termini.</p>
            </section>

            <section id="utilizzo">
                <h2>3. Utilizzo corretto</h2>
                <p>Utilizzando You-Price ti impegni a non:</p>
                <ul>
                    <li>Inserire dati falsi, fuorvianti o non aggiornati nelle ricerche o nel profilo.</li>
                    <li>Utilizzare il servizio per scopi illegali o non autorizzati.</li>
                    <li>Tentare di accedere a sezioni del sito non destinate al pubblico.</li>
                    <li>Effettuare richieste di preventivo in modo sistematico o automatizzato (scraping, bot).</li>
                    <li>Interferire con il corretto funzionamento del sito o dei sistemi informatici di You-Price.</li>
                </ul>
            </section>

            <section id="preventivi">
                <h2>4. Preventivi e prenotazioni</h2>
                <p>I preventivi ricevuti tramite You-Price sono forniti a titolo indicativo dagli operatori del settore. You-Price non garantisce la disponibilità, l'accuratezza o la definitività dei prezzi indicati.</p>
                <div class="legal-notice">
                    <i class="fas fa-info-circle"></i>
                    La conferma e la conclusione di un contratto di viaggio avviene esclusivamente tra l'utente e l'operatore/agenzia di viaggio di riferimento, al di fuori dalla piattaforma You-Price.
                </div>
                <p>You-Price non è responsabile per eventuali discrepanze tra i prezzi indicativi mostrati sul sito e quelli definitivi proposti dagli operatori.</p>
            </section>

            <section id="proprieta">
                <h2>5. Proprietà intellettuale</h2>
                <p>Tutti i contenuti presenti su you-price.it — inclusi testi, grafica, logo, codice sorgente e struttura del servizio — sono di proprietà esclusiva di You-Price e sono protetti dalla normativa vigente in materia di proprietà intellettuale.</p>
                <p>È vietata qualsiasi riproduzione, distribuzione o utilizzo commerciale dei contenuti senza esplicita autorizzazione scritta.</p>
            </section>

            <section id="responsabilita">
                <h2>6. Limitazione di responsabilità</h2>
                <p>You-Price fornisce il servizio "così come è" e non offre garanzie di alcun tipo, esplicite o implicite, riguardo a completezza, accuratezza o idoneità a scopi specifici.</p>
                <p>Nella misura massima consentita dalla legge applicabile, You-Price non sarà responsabile per danni diretti, indiretti, incidentali o consequenziali derivanti dall'utilizzo o dall'impossibilità di utilizzare il servizio.</p>
            </section>

            <section id="modifiche">
                <h2>7. Modifiche al servizio e ai Termini</h2>
                <p>You-Price si riserva il diritto di modificare, sospendere o interrompere il servizio in qualsiasi momento, con o senza preavviso.</p>
                <p>Modifiche sostanziali ai presenti Termini saranno comunicate agli utenti registrati via email o tramite avviso in evidenza sul sito. L'utilizzo continuato del servizio dopo la comunicazione delle modifiche costituisce accettazione dei nuovi Termini.</p>
            </section>

            <section id="legge">
                <h2>8. Legge applicabile e foro competente</h2>
                <p>I presenti Termini sono regolati dalla legge italiana. Per qualsiasi controversia relativa all'utilizzo del servizio è competente in via esclusiva il Foro del luogo di residenza o domicilio dell'utente, se situato in Italia, ai sensi del Codice del Consumo (D.Lgs. 206/2005).</p>
                <p>Per eventuali reclami o richieste di informazioni: <strong><a href="mailto:info@you-price.it">info@you-price.it</a></strong></p>
            </section>

        </div>
    </div>
</div>
@endsection
