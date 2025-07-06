@extends('layouts.app')
<style>
    td {
        vertical-align: middle !important;
    }
</style>
@section('content')
    <div class="container">
        <h1>Gestione Utenti</h1>
        <table class="table table-striped table-hover align-middle text-center" id="users-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Email</th>
                    <th>Ruolo</th>
                    <th>Abilitato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(function() {
            $('#users-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.data') }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'surname',
                        name: 'surname'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'abilitato',
                        name: 'abilitato',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                // Traduzione in italiano
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Italian.json"
                },
                // Personalizzazione dell'aspetto 
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Tutti"]
                ],
            });
        });
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
        // Eventi per i pulsanti "Lock"
        $(document).on('click', '.lockButton', function() {
            var userId = $(this).data('id'); // Ottieni l'ID utente
            Swal.fire({
                title: 'Sei sicuro?',
                text: "L'utente sarà disabilitato!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sì, disabilita!',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('users.lock') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            userId: userId
                        },
                        success: function(response) {
                            Swal.fire('Disabilitato!',
                                'L\'utente è stato disabilitato con successo.', 'success');
                            $('#users-table').DataTable().ajax.reload(); // Ricarica la tabella
                        },
                        error: function(xhr) {
                            Swal.fire('Errore!', 'Si è verificato un errore.', 'error');
                        }
                    });
                }
            });
        });

        // Eventi per i pulsanti "Unlock"
        $(document).on('click', '.unlockButton', function() {
            var userId = $(this).data('id'); // Ottieni l'ID utente
            Swal.fire({
                title: 'Sei sicuro?',
                text: "L'utente sarà abilitato!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sì, abilita!',
                cancelButtonText: 'Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('users.unlock') }}",
                        type: 'POST',
                        async: false,
                        data: {
                            _token: '{{ csrf_token() }}',
                            userId: userId
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire('Abilitato!', 'L\'utente è stato abilitato con successo.',
                                'success');
                            $('#users-table').DataTable().ajax.reload(); // Ricarica la tabella
                        },
                        error: function(xhr) {
                            Swal.fire('Errore!', 'Si è verificato un errore.', 'error');
                        }
                    });
                }
            });
        });

        /* $('#users-table').DataTable().ajax.reload(null, false);
        setTimeout(() => {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }, 500); */
    </script>
@endsection
