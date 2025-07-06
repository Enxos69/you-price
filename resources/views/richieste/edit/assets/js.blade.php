<script>
    function update() {

        var formData = new FormData($("form#richiestaForm")[0]);
        // Effettua la richiesta AJAX

        $.ajax({
            url: "{{ route('richieste.update') }}",
            method: 'POST',
            data: formData,
            contentType: false,
            cache: false,
            processData: false
        }).done(function(data) {
            var obj = data;
            var response = '' + data.response;
            response = response.toUpperCase();
            switch (response) {
                case 'TRUE':
                    console.log('AJAX Success:', response);
                    Swal.fire({
                        title: 'Successo!',
                        text: 'Richiesta aggiornata con successo.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('richieste.index') }}';
                        }
                    });
                    break;
                case 'FALSE':
                    console.log('AJAX Error:', data.errors);
                    let errors = data.errors;
                    console.log('Error:', errors);
                    let errorMessage = 'Errore durante l\'aggiornamento:<br>';
                    $.each(errors, function(key, value) {
                        errorMessage += value + '<br>';
                    });
                    console.log('Error Message:', errorMessage);
                    Swal.fire({
                        title: 'Errore!',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
            }
        });
    }



    /* fetch('{{ route('richieste.store') }}', {
    method: 'POST',
    body: formData,
    headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
    'content')
    }
    })
    .then(response => response.json())
    .then(data => {
    if (data.errors) {
    // C'è stato un errore di validazione, lo mostriamo con SweetAlert
    let errorMessages = '';
    Object.keys(data.errors).forEach(function(key) {
    errorMessages += data.errors[key].join('<br>') + '<br>';
    });

    Swal.fire({
    icon: 'error',
    title: 'Errore di validazione',
    html: errorMessages, // Mostra i messaggi di errore
    });
    } else {
    // Se il form è stato inviato correttamente
    Swal.fire({
    icon: 'success',
    title: 'Successo',
    text: 'La richiesta è stata creata con successo!',
    }).then(function() {
    // Reindirizza alla pagina delle richieste
    window.location.href = '{{ route('richieste.index') }}';
    });
    }
    })
    .catch(error => {
    console.error('Errore:', error);
    Swal.fire({
    icon: 'error',
    title: 'Errore di invio',
    text: 'Si è verificato un errore durante l\'invio del form. Riprova.',
    });
    });

    }); */
</script>
