<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ' . dirname($_SERVER['SCRIPT_NAME'], 2) . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Grupos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['SCRIPT_NAME'], 2); ?>/includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Administrar Grupos</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoGrupoModal">
                Nuevo Grupo
            </button>
        </div>

        <!-- Tabla de Grupos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="gruposTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Grupo -->
    <div class="modal fade" id="nuevoGrupoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="nuevoGrupoForm">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Grupo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarGrupo">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Grupo -->
    <div class="modal fade" id="editarGrupoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <form id="editarGrupoForm">
                                <input type="hidden" id="edit_id" name="id">
                                <div class="mb-3">
                                    <label for="edit_nombre" class="form-label">Nombre del Grupo</label>
                                    <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Estudiantes en el Grupo</h6>
                                    <span class="badge bg-primary" id="totalEstudiantes">0 estudiantes</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="estudiantesGrupoTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Apellidos</th>
                                                    <th>Documento</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        // Función para obtener la ruta base
        function getBasePath() {
            const path = window.location.pathname;
            return path.substring(0, path.indexOf('/pages'));
        }

        // Inicializar DataTable
        var table = $('#gruposTable').DataTable({
            ajax: {
                url: getBasePath() + '/includes/grupos/listar.php',
                dataSrc: ''
            },
            columns: [
                { data: 'id' },
                { data: 'nombre' },
                { data: 'created_at' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-warning editar-grupo" data-id="${row.id}">Editar</button>
                            <button class="btn btn-sm btn-danger eliminar-grupo" data-id="${row.id}">Eliminar</button>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });

        // Manejar creación de grupo
        $('#guardarGrupo').click(function() {
            const nombre = $('#nombre').val().trim();
            
            if (!nombre) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor ingrese el nombre del grupo'
                });
                return;
            }

            // Mostrar indicador de carga
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

            $.ajax({
                url: getBasePath() + '/includes/grupos/crear.php',
                type: 'POST',
                data: { nombre: nombre },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message
                        });
                        $('#nuevoGrupoModal').modal('hide');
                        $('#nuevoGrupoForm')[0].reset();
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al crear el grupo'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).text('Guardar');
                }
            });
        });

        // Manejar clic en botón editar
        $('#gruposTable').on('click', '.editar-grupo', function() {
            const id = $(this).data('id');
            
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Cargando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Obtener datos del grupo y sus estudiantes
            Promise.all([
                $.get(getBasePath() + '/includes/grupos/obtener.php', { id: id }),
                $.get(getBasePath() + '/includes/grupos/obtener_estudiantes.php', { grupo_id: id })
            ]).then(([grupoResponse, estudiantesResponse]) => {
                Swal.close();
                
                if (grupoResponse.success) {
                    $('#edit_id').val(grupoResponse.grupo.id);
                    $('#edit_nombre').val(grupoResponse.grupo.nombre);
                    
                    // Actualizar contador de estudiantes
                    $('#totalEstudiantes').text(estudiantesResponse.total + ' estudiantes');
                    
                    // Limpiar y llenar la tabla de estudiantes
                    const tbody = $('#estudiantesGrupoTable tbody');
                    tbody.empty();
                    
                    if (estudiantesResponse.estudiantes.length > 0) {
                        estudiantesResponse.estudiantes.forEach(estudiante => {
                            tbody.append(`
                                <tr>
                                    <td>${estudiante.id}</td>
                                    <td>${estudiante.nombre}</td>
                                    <td>${estudiante.apellidos}</td>
                                    <td>${estudiante.documento}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger remover-estudiante" 
                                                data-id="${estudiante.id}" 
                                                data-nombre="${estudiante.nombre} ${estudiante.apellidos}">
                                            Remover del grupo
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="5" class="text-center">No hay estudiantes en este grupo</td></tr>');
                    }
                    
                    $('#editarGrupoModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: grupoResponse.message
                    });
                }
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar los datos del grupo'
                });
            });
        });

        // Manejar cambios en el nombre del grupo
        let timeoutId;
        $('#edit_nombre').on('input', function() {
            clearTimeout(timeoutId);
            const input = $(this);
            const nombre = input.val().trim();
            const id = $('#edit_id').val();
            
            // Esperar 500ms después de que el usuario deje de escribir
            timeoutId = setTimeout(() => {
                if (nombre && nombre !== input.data('original-value')) {
                    $.ajax({
                        url: getBasePath() + '/includes/grupos/editar.php',
                        type: 'POST',
                        data: {
                            id: id,
                            nombre: nombre
                        },
                        success: function(response) {
                            if (response.success) {
                                input.data('original-value', nombre);
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: 'Nombre del grupo actualizado',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                                input.val(input.data('original-value'));
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al actualizar el nombre del grupo'
                            });
                            input.val(input.data('original-value'));
                        }
                    });
                }
            }, 500);
        });

        // Cuando se abre el modal, guardar el valor original
        $('#editarGrupoModal').on('shown.bs.modal', function() {
            $('#edit_nombre').data('original-value', $('#edit_nombre').val());
        });

        // Manejar eliminación de grupo
        $('#gruposTable').on('click', '.eliminar-grupo', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: getBasePath() + '/includes/grupos/eliminar.php',
                        type: 'POST',
                        data: { id: id },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: response.message
                                });
                                table.ajax.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al eliminar el grupo'
                            });
                        }
                    });
                }
            });
        });

        // Manejar clic en remover estudiante
        $(document).on('click', '.remover-estudiante', function() {
            const estudianteId = $(this).data('id');
            const nombreEstudiante = $(this).data('nombre');
            const grupoId = $('#edit_id').val();
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas remover a ${nombreEstudiante} del grupo?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(getBasePath() + '/includes/grupos/remover_estudiante.php', {
                        estudiante_id: estudianteId,
                        grupo_id: grupoId
                    })
                    .done(function(response) {
                        if (response.success) {
                            // Actualizar la tabla de estudiantes
                            $.get(getBasePath() + '/includes/grupos/obtener_estudiantes.php', { 
                                grupo_id: grupoId 
                            })
                            .done(function(response) {
                                if (response.success) {
                                    $('#totalEstudiantes').text(response.total + ' estudiantes');
                                    const tbody = $('#estudiantesGrupoTable tbody');
                                    tbody.empty();
                                    
                                    if (response.estudiantes.length > 0) {
                                        response.estudiantes.forEach(estudiante => {
                                            tbody.append(`
                                                <tr>
                                                    <td>${estudiante.id}</td>
                                                    <td>${estudiante.nombre}</td>
                                                    <td>${estudiante.apellidos}</td>
                                                    <td>${estudiante.documento}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger remover-estudiante" 
                                                                data-id="${estudiante.id}" 
                                                                data-nombre="${estudiante.nombre} ${estudiante.apellidos}">
                                                            Remover del grupo
                                                        </button>
                                                    </td>
                                                </tr>
                                            `);
                                        });
                                    } else {
                                        tbody.append('<tr><td colspan="5" class="text-center">No hay estudiantes en este grupo</td></tr>');
                                    }
                                }
                            });
                            
                            Swal.fire(
                                '¡Removido!',
                                'El estudiante ha sido removido del grupo.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error',
                                'No se pudo remover al estudiante del grupo.',
                                'error'
                            );
                        }
                    })
                    .fail(function() {
                        Swal.fire(
                            'Error',
                            'Error al procesar la solicitud.',
                            'error'
                        );
                    });
                }
            });
        });
    });
    </script>
</body>
</html> 