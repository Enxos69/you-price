<!-- resources/views/index.blade.php -->
@extends('layouts.app')

@section('content')
<style>
    /* Reset per questa pagina */
    #app {
        padding: 0 !important;
    }
    
    main.py-4 {
        padding: 0 !important;
    }

    :root {
        --primary-green: #006B54;
        --light-green: #00A676;
        --accent-lime: #B8D332;
        --dark-text: #2C3E50;
        --light-gray: #F8F9FA;
        --white: #FFFFFF;
    }

    /* Hero Section */
    .hero {
        margin-top: 70px;
        min-height: 85vh;
        background: linear-gradient(180deg, rgba(0, 107, 84, 0.2) 10%, rgba(0, 166, 118, 0.8) 50%), 
                    url('{{ config('app.asset_url') ? config('app.asset_url') . '/img/hero.jpg' : asset('assets/img/hero.jpg') }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        color: var(--white);
        position: relative;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 30% 50%, rgba(184, 211, 50, 0.1) 0%, transparent 50%);
    }

    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 30px;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .hero h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        animation: fadeInUp 0.8s ease-out;
    }

    .hero .tagline {
        font-size: 1.5rem;
        margin-bottom: 15px;
        font-weight: 300;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .hero .subtitle {
        font-size: 1.2rem;
        margin-bottom: 40px;
        opacity: 0.95;
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }

    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }

    .btn-hero {
        padding: 16px 40px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .btn-hero.btn-primary-custom {
        background: var(--accent-lime);
        color: var(--dark-text);
        box-shadow: 0 4px 15px rgba(184, 211, 50, 0.4);
    }

    .btn-hero.btn-primary-custom:hover {
        background: #A5C028;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(184, 211, 50, 0.6);
        text-decoration: none;
        color: var(--dark-text);
    }

    .btn-hero.btn-secondary-custom {
        background: transparent;
        color: var(--white);
        border: 2px solid var(--white);
    }

    .btn-hero.btn-secondary-custom:hover {
        background: var(--white);
        color: var(--primary-green);
        transform: translateY(-3px);
        text-decoration: none;
    }

    /* Trust/Features Section */
    .trust-section {
        padding: 80px 30px;
        background: var(--white);
        border-bottom: 1px solid #e0e0e0;
    }

    .trust-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
    }

    .trust-item {
        text-align: center;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .trust-item:hover {
        transform: translateY(-5px);
    }

    .trust-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, rgba(0, 107, 84, 0.1), rgba(184, 211, 50, 0.1));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--primary-green);
    }

    .trust-item h3 {
        font-size: 1.3rem;
        color: var(--primary-green);
        margin-bottom: 10px;
        font-weight: 600;
    }

    .trust-item p {
        color: #666;
        line-height: 1.6;
    }

    /* How it works section */
    .how-it-works {
        padding: 100px 30px;
        background: var(--white);
    }

    .section-title {
        text-align: center;
        font-size: 2.8rem;
        color: var(--primary-green);
        margin-bottom: 20px;
        font-weight: 700;
    }

    .section-subtitle {
        text-align: center;
        font-size: 1.3rem;
        color: #666;
        margin-bottom: 60px;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    .steps {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
    }

    .step {
        text-align: center;
        padding: 30px;
        border-radius: 20px;
        transition: all 0.3s ease;
        position: relative;
    }

    .step:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .step-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--primary-green), var(--light-green));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--white);
        position: relative;
    }

    .step-number {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 35px;
        height: 35px;
        background: var(--accent-lime);
        color: var(--dark-text);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .step h3 {
        font-size: 1.4rem;
        margin-bottom: 15px;
        color: var(--primary-green);
    }

    .step p {
        color: #666;
        line-height: 1.8;
    }

    /* Revolution section */
    .revolution {
        padding: 100px 30px;
        background: var(--light-gray);
    }

    .revolution-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .revolution-text h2 {
        font-size: 2.5rem;
        color: var(--primary-green);
        margin-bottom: 30px;
        font-weight: 700;
    }

    .revolution-text p {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 20px;
        line-height: 1.8;
    }

    .highlight-box {
        background: var(--white);
        padding: 30px;
        border-radius: 15px;
        border-left: 5px solid var(--accent-lime);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .highlight-box h3 {
        color: var(--primary-green);
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .revolution-image {
        position: relative;
    }

    .revolution-image img {
        width: 100%;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    /* Benefits comparison */
    .benefits {
        padding: 100px 30px;
        background: var(--white);
    }

    .comparison-table {
        max-width: 1000px;
        margin: 0 auto;
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .comparison-header {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        background: var(--primary-green);
        color: var(--white);
        font-weight: 600;
        font-size: 1.2rem;
    }

    .comparison-header > div {
        padding: 25px;
        text-align: center;
    }

    .comparison-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        border-bottom: 1px solid #e0e0e0;
    }

    .comparison-row:last-child {
        border-bottom: none;
    }

    .comparison-row > div {
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .comparison-row > div:first-child {
        justify-content: flex-start;
        font-weight: 500;
    }

    .feature-icon {
        color: var(--light-green);
        font-size: 1.5rem;
    }

    .no-feature {
        color: #ccc;
        font-size: 1.5rem;
    }

    .highlight-column {
        background: linear-gradient(180deg, rgba(184, 211, 50, 0.1), transparent);
    }

    /* CTA Section */
    .cta-section {
        padding: 100px 30px;
        background: linear-gradient(135deg, var(--primary-green), var(--light-green));
        color: var(--white);
        text-align: center;
    }

    .cta-section h2 {
        font-size: 2.8rem;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .cta-section p {
        font-size: 1.3rem;
        margin-bottom: 40px;
        opacity: 0.95;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 968px) {
        .hero h1 {
            font-size: 2.5rem;
        }

        .hero .tagline {
            font-size: 1.2rem;
        }

        .revolution-content {
            grid-template-columns: 1fr;
        }

        .comparison-header,
        .comparison-row {
            grid-template-columns: 1fr;
        }

        .comparison-header > div:first-child,
        .comparison-row > div:first-child {
            display: none;
        }

        .section-title {
            font-size: 2.2rem;
        }
    }

    @media (max-width: 768px) {
        .hero {
            min-height: 70vh;
        }

        .hero h1 {
            font-size: 2rem;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }

        .btn-hero {
            width: 100%;
            max-width: 300px;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Sei tu a decidere quanto spendere</h1>
        <p class="tagline">Il primo servizio dove <strong>TU</strong> stabilisci il prezzo</p>
        <p class="subtitle">Dimentica i prezzi fissi: inserisci il tuo budget e lascia che sia il mercato a trovare la soluzione perfetta per te</p>
        
        <div class="cta-buttons">
            <a href="{{ route('crociere.index') }}" class="btn-hero btn-primary-custom">
                <i class="fas fa-search"></i>
                Inizia la tua Ricerca
            </a>
            <a href="#come-funziona" class="btn-hero btn-secondary-custom">
                <i class="fas fa-play-circle"></i>
                Scopri Come Funziona
            </a>
        </div>
    </div>
</section>

<!-- Trust/Features Section -->
<section class="trust-section">
    <div class="trust-content">
        <div class="trust-item">
            <div class="trust-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <h3>Tu Decidi il Prezzo</h3>
            <p>Non più prezzi imposti: sei tu a stabilire quanto vuoi spendere</p>
        </div>
        
        <div class="trust-item">
            <div class="trust-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>100% Trasparente</h3>
            <p>Nessun costo nascosto, tutto chiaro fin dall'inizio</p>
        </div>
        
        <div class="trust-item">
            <div class="trust-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h3>Veloce e Semplice</h3>
            <p>Risultati in tempo reale, senza attese inutili</p>
        </div>
        
        <div class="trust-item">
            <div class="trust-icon">
                <i class="fas fa-globe"></i>
            </div>
            <h3>Tutti i Provider</h3>
            <p>Confrontiamo le offerte di centinaia di compagnie per te</p>
        </div>
    </div>
</section>

<!-- How it Works Section -->
<section class="how-it-works" id="come-funziona">
    <h2 class="section-title">Come Funziona</h2>
    <p class="section-subtitle">Il processo è semplice e rivoluzionario. Tu decidi quanto vuoi spendere, noi troviamo le migliori offerte per te.</p>
    
    <div class="steps">
        <div class="step">
            <div class="step-icon">
                <i class="fas fa-sliders-h"></i>
                <span class="step-number">1</span>
            </div>
            <h3>Imposta il tuo Budget</h3>
            <p>Scegli la destinazione, le date e soprattutto: stabilisci tu quanto vuoi spendere per la tua crociera</p>
        </div>

        <div class="step">
            <div class="step-icon">
                <i class="fas fa-network-wired"></i>
                <span class="step-number">2</span>
            </div>
            <h3>Cerchiamo per Te</h3>
            <p>Il nostro sistema contatta automaticamente tutti i provider e negozia in tempo reale le migliori offerte</p>
        </div>

        <div class="step">
            <div class="step-icon">
                <i class="fas fa-list-check"></i>
                <span class="step-number">3</span>
            </div>
            <h3>Ricevi le Proposte</h3>
            <p>Visualizza subito i risultati disponibili. Se sei registrato, ricevi anche offerte personalizzate via email</p>
        </div>

        <div class="step">
            <div class="step-icon">
                <i class="fas fa-thumbs-up"></i>
                <span class="step-number">4</span>
            </div>
            <h3>Scegli e Parti</h3>
            <p>Confronta le opzioni, scegli quella perfetta per te e preparati a vivere la tua esperienza da sogno</p>
        </div>
    </div>
</section>

<!-- Revolution Section -->
<section class="revolution">
    <div class="revolution-content">
        <div class="revolution-text">
            <h2>La Rivoluzione del Prezzo</h2>
            <p>Per troppo tempo il prezzo dei viaggi è stato imposto dall'alto. <strong>You-Price</strong> ribalta questo paradigma: sei tu a stabilire quanto vale per te una crociera.</p>
            
            <p>Non più "prendi o lascia". Ora il mercato lavora per te, cercando di soddisfare le tue esigenze e il tuo budget.</p>
            
            <div class="highlight-box">
                <h3><i class="fas fa-lightbulb" style="color: var(--accent-lime); margin-right: 10px;"></i>Il Concetto</h3>
                <p>Immagina di entrare in un negozio dove invece di guardare i cartellini dei prezzi, sei tu a dire quanto vuoi spendere. Questo è You-Price: <strong>il potere contrattuale torna nelle tue mani</strong>.</p>
            </div>
        </div>

        <div class="revolution-image">
            <img src="{{ config('app.asset_url') ? config('app.asset_url') . '/img/concept.png' : asset('img/concept.png')}}" alt="You Price Concept - Tu decidi il budget">
        </div>
    </div>
</section>

<!-- Benefits Comparison Section -->
<section class="benefits">
    <h2 class="section-title">Perché Registrarsi?</h2>
    <p class="section-subtitle">Tutti possono cercare, ma gli utenti registrati ottengono molto di più</p>
    
    <div class="comparison-table">
        <div class="comparison-header">
            <div>Funzionalità</div>
            <div>Ospite</div>
            <div class="highlight-column" style="background: var(--accent-lime); color: var(--dark-text);">
                <i class="fas fa-star" style="margin-right: 5px;"></i>Utente Registrato
            </div>
        </div>

        <div class="comparison-row">
            <div>Cerca crociere per budget</div>
            <div><i class="fas fa-check-circle feature-icon"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>

        <div class="comparison-row">
            <div>Visualizza risultati base</div>
            <div><i class="fas fa-check-circle feature-icon"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>

        <div class="comparison-row">
            <div>Dettagli completi delle crociere</div>
            <div><i class="fas fa-times-circle no-feature"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>

        <div class="comparison-row">
            <div>Offerte personalizzate via email</div>
            <div><i class="fas fa-times-circle no-feature"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>

        <div class="comparison-row">
            <div>Alert sui prezzi</div>
            <div><i class="fas fa-times-circle no-feature"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>

        <div class="comparison-row">
            <div>Dashboard personalizzata</div>
            <div><i class="fas fa-times-circle no-feature"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>

        <div class="comparison-row">
            <div>Storico ricerche</div>
            <div><i class="fas fa-times-circle no-feature"></i></div>
            <div class="highlight-column"><i class="fas fa-check-circle feature-icon"></i></div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section class="cta-section">
    <h2>Pronto a Rivoluzionare i Tuoi Viaggi?</h2>
    <p>Unisciti a You-Price e inizia a viaggiare alle tue condizioni</p>
    
    <div class="cta-buttons">
        @guest
            <a href="{{ route('register') }}" class="btn-hero btn-primary-custom" style="background: white; color: var(--primary-green);">
                <i class="fas fa-user-plus"></i>
                Registrati Gratis
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn-hero btn-primary-custom" style="background: white; color: var(--primary-green);">
                <i class="fas fa-tachometer-alt"></i>
                La Mia Dashboard
            </a>
        @endguest
        <a href="{{ route('crociere.index') }}" class="btn-hero btn-secondary-custom">
            <i class="fas fa-ship"></i>
            Cerca Subito una Crociera
        </a>
    </div>
</section>

@endsection

@section('scripts')
<script>
    // Smooth scrolling per i link interni
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animazione elementi al scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.step, .highlight-box, .comparison-table, .trust-item').forEach(el => {
        observer.observe(el);
    });
</script>
@endsection
