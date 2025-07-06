<script>
    
    function editUser() {

        var formData = new FormData($("form#edit-user-form")[0]);
        // Visualizza i contenuti di FormData
       /*  formData.forEach((value, key) => {
            console.log(`${key}: ${value}`);
        }); */
        $.ajax({
            url: "{{ route('users.update') }}",
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
                        text: 'Utente aggiornato con successo.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('users.index') }}';
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
</script>
