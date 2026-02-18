{{-- resources/views/legal/privacy.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="legal-page">
    <div class="legal-hero">
        <div class="legal-hero-inner">
            <div class="legal-icon"><i class="fas fa-shield-alt"></i></div>
            <h1>Privacy Policy</h1>
            <p>Ultimo aggiornamento: {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="legal-body">
        <div class="legal-toc">
            <h3><i class="fas fa-list"></i> Indice</h3>
            <ol>
                <li><a href="#titolare">Titolare del trattamento</a></li>
                <li><a href="#dati">Dati raccolti</a></li>
                <li><a href="#finalita">Finalità del trattamento</a></li>
                <li><a href="#base">Base giuridica</a></li>
                <li><a href="#conservazione">Conservazione dei dati</a></li>
                <li><a href="#diritti">I tuoi diritti</a></li>
                <li><a href="#cookie">Cookie</a></li>
                <li><a href="#contatti">Contatti</a></li>
            </ol>
        </div>

        <div class="legal-content">

            <section id="titolare">
                <h2>1. Titolare del trattamento</h2>
                <p>Il Titolare del trattamento dei dati personali raccolti tramite il sito <strong>you-price.it</strong> è la società titolare del servizio You-Price (di seguito "Titolare"). Per qualsiasi comunicazione relativa alla privacy puoi scrivere all'indirizzo email indicato nella sezione Contatti.</p>
            </section>

            <section id="dati">
                <h2>2. Dati raccolti</h2>
                <p>You-Price raccoglie le seguenti categorie di dati personali:</p>
                <ul>
                    <li><strong>Dati di registrazione:</strong> nome, cognome, indirizzo email, password cifrata.</li>
                    <li><strong>Dati di navigazione:</strong> indirizzo IP, tipo di browser, pagine visitate, data e ora di accesso.</li>
                    <li><strong>Dati di utilizzo:</strong> criteri di ricerca inseriti (destinazione, date, budget), crociere salvate nei preferiti, alert prezzi impostati.</li>
                    <li><strong>Dati di comunicazione:</strong> contenuto delle richieste di preventivo inviate tramite il servizio.</li>
                </ul>
            </section>

            <section id="finalita">
                <h2>3. Finalità del trattamento</h2>
                <p>I dati vengono trattati per le seguenti finalità:</p>
                <ul>
                    <li>Registrazione e gestione dell'account utente.</li>
                    <li>Erogazione del servizio di ricerca e confronto crociere.</li>
                    <li>Invio di preventivi personalizzati e notifiche su alert prezzi.</li>
                    <li>Miglioramento del servizio tramite analisi statistiche aggregate e anonimizzate.</li>
                    <li>Adempimento di obblighi legali.</li>
                    <li>Invio di comunicazioni commerciali, previo consenso esplicito.</li>
                </ul>
            </section>

            <section id="base">
                <h2>4. Base giuridica</h2>
                <p>Il trattamento dei dati si fonda sulle seguenti basi giuridiche ai sensi del Regolamento UE 2016/679 (GDPR):</p>
                <ul>
                    <li><strong>Esecuzione di un contratto</strong> (art. 6, par. 1, lett. b): per la fornitura del servizio.</li>
                    <li><strong>Obbligo legale</strong> (art. 6, par. 1, lett. c): per adempimenti normativi.</li>
                    <li><strong>Legittimo interesse</strong> (art. 6, par. 1, lett. f): per la sicurezza e il miglioramento del servizio.</li>
                    <li><strong>Consenso</strong> (art. 6, par. 1, lett. a): per le comunicazioni di marketing.</li>
                </ul>
            </section>

            <section id="conservazione">
                <h2>5. Conservazione dei dati</h2>
                <p>I dati personali sono conservati per il tempo strettamente necessario alle finalità per cui sono stati raccolti:</p>
                <ul>
                    <li>Dati dell'account: per tutta la durata del rapporto contrattuale e fino a 12 mesi dalla cancellazione dell'account.</li>
                    <li>Dati di navigazione: massimo 12 mesi.</li>
                    <li>Dati per obblighi fiscali/legali: secondo i termini di legge (di norma 10 anni).</li>
                </ul>
            </section>

            <section id="diritti">
                <h2>6. I tuoi diritti</h2>
                <p>In qualità di interessato, hai il diritto di:</p>
                <ul>
                    <li><strong>Accesso</strong> ai tuoi dati personali (art. 15 GDPR).</li>
                    <li><strong>Rettifica</strong> dei dati inesatti o incompleti (art. 16 GDPR).</li>
                    <li><strong>Cancellazione</strong> ("diritto all'oblio", art. 17 GDPR).</li>
                    <li><strong>Limitazione</strong> del trattamento (art. 18 GDPR).</li>
                    <li><strong>Portabilità</strong> dei dati (art. 20 GDPR).</li>
                    <li><strong>Opposizione</strong> al trattamento (art. 21 GDPR).</li>
                    <li>Proporre <strong>reclamo</strong> al Garante per la Protezione dei Dati Personali (<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener">www.garanteprivacy.it</a>).</li>
                </ul>
                <p>Per esercitare i tuoi diritti scrivi all'indirizzo indicato nella sezione Contatti.</p>
            </section>

            <section id="cookie">
                <h2>7. Cookie</h2>
                <p>Il sito utilizza cookie tecnici necessari al funzionamento e cookie analitici per migliorare il servizio. Per informazioni dettagliate consulta la nostra <a href="{{ route('cookie') }}">Cookie Policy</a>.</p>
            </section>

            <section id="contatti">
                <h2>8. Contatti</h2>
                <p>Per qualsiasi richiesta relativa al trattamento dei tuoi dati personali puoi contattarci all'indirizzo: <strong><a href="mailto:privacy@you-price.it">privacy@you-price.it</a></strong></p>
            </section>

        </div>
    </div>
</div>
@endsection
