$(document).ready(function() {
    // Función para obtener la ruta base
    function getBasePath() {
        const path = window.location.pathname;
        const basePath = path.substring(0, path.lastIndexOf('/') + 1);
        return basePath;
    }

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#email').val();
        const password = $('#password').val();
        
        // Mostrar indicador de carga
        $('#loginButton').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
        
        $.ajax({
            url: getBasePath() + 'includes/login.php',
            type: 'POST',
            dataType: 'json',
            data: {
                email: email,
                password: password
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = getBasePath() + response.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al iniciar sesión'
                    });
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Hubo un problema al conectar con el servidor';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    console.error('Error parsing response:', xhr.responseText);
                }
                
                console.log('XHR Status:', xhr.status);
                console.log('XHR Response:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            },
            complete: function() {
                // Restaurar el botón
                $('#loginButton').prop('disabled', false).text('Iniciar Sesión');
            }
        });
    });
}); 