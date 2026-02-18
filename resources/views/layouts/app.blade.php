<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Custom CSS -->
    <link href="{{ config('app.asset_url') }}/css/custom.css" rel="stylesheet">

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">


    <!-- Styles -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Includi jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Includi DataTables CSS e JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

</head>

<body class="@yield('body-class')">
    <!-- Includi la Navbar -->
    @include('partials.navbar')

    <div id="app">
        <main class="py-4">
            @yield('content') <!-- Questo è per le viste tradizionali -->
        </main>
        @include('partials.footer')
    </div>
    <!-- Include scripts section -->
    @yield('scripts')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- aggiungo sweet alarm -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Active nav item highlight -->
    <script>
        (function () {
            var currentPath = window.location.pathname;
            document.querySelectorAll('.navbar .nav-link').forEach(function (link) {
                var href = link.getAttribute('href');
                if (href && href !== '#' && currentPath.startsWith(href) && href !== '/') {
                    link.closest('.nav-item') && link.closest('.nav-item').classList.add('active');
                }
            });
        })();
    </script>

    <!-- Glass navbar: scurisce uscendo dall'hero + toggle logo bianco/colorato -->
    <script>
        (function () {
            var navbar = document.querySelector('.navbar-glass');
            if (!navbar) return;

            var logo     = navbar.querySelector('.navbar-brand img');
            var hero     = document.querySelector('.hero');
            var threshold = hero ? hero.offsetHeight - navbar.offsetHeight : 80;

            function onScroll() {
                if (window.scrollY > threshold) {
                    navbar.classList.add('navbar-glass--scrolled');
                    if (logo) logo.classList.add('logo-white');
                } else {
                    navbar.classList.remove('navbar-glass--scrolled');
                    if (logo) logo.classList.remove('logo-white');
                }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>

    <!-- Glass navbar: si scurisce scrollando fuori dall'hero -->
    <script>
        (function () {
            var navbar = document.querySelector('.navbar-glass');
            if (!navbar) return;

            var hero = document.querySelector('.hero');
            function onScroll() {
                var threshold = hero ? (hero.offsetHeight - 80) : 300;
                if (window.scrollY > threshold) {
                    navbar.classList.add('navbar-glass--scrolled');
                } else {
                    navbar.classList.remove('navbar-glass--scrolled');
                }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll(); // controlla subito al caricamento
        })();
    </script>

    <!-- Livewire Scripts