<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Estudiantes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Administrar Estudiantes</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoEstudiante">
                Nuevo Estudiante
            </button>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Buscar Estudiante</h5>
                <div class="input-group">
                    <input type="text" id="documento" class="form-control" placeholder="Número de documento">
                    <button class="btn btn-primary" onclick="buscarEstudiante()">Buscar</button>
                </div>
            </div>
        </div>

        <!-- Resultado de búsqueda -->
        <div id="resultadoBusqueda" class="card mb-4" style="display: none;">
            <div class="card-body">
                <h2 class="card-title mb-4">Información del Estudiante</h2>
                <div class="row">
                    <div class="col-md-8">
                        <div id="datosEstudiante"></div>
                        <button class="btn btn-primary mt-3" id="btnEditarEstudiante">Editar Estudiante</button>
                    </div>
                    <div class="col-md-4 text-center">
                        <img id="qrCode" src="" alt="Código QR" class="img-fluid mb-3" style="max-width: 300px; background-color: white;">
                        <br>
                        <button class="btn btn-success" onclick="descargarQRBusqueda()">Descargar QR</button>
                    </div>
                </div>

                <div class="mt-4">
                    <h3>Registro de Asistencias</h3>
                    <div class="table-responsive">
                        <table id="tablaAsistencias" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Profesor</th>
                                </tr>
                            </thead>
                            <tbody id="asistenciasBody">
                                <!-- Las asistencias se cargarán aquí -->
                            </tbody>
                        </table>
                        <div id="sinAsistencias" class="text-center p-3" style="display: none;">
                            <p>Ningún dato disponible en esta tabla</p>
                            <p>Mostrando registros del 0 al 0 de un total de 0 registros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tablaEstudiantes" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Documento</th>
                        <th>Grupo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $db = new Database();
                    $conn = $db->connect();
                    
                    $stmt = $conn->prepare("
                        SELECT e.*, g.nombre as grupo_nombre 
                        FROM estudiantes e 
                        LEFT JOIN grupos g ON e.grupo_id = g.id
                    ");
                    $stmt->execute();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['nombre']}</td>";
                        echo "<td>{$row['apellidos']}</td>";
                        echo "<td>{$row['documento']}</td>";
                        echo "<td>{$row['grupo_nombre']}</td>";
                        echo "<td>
                                <button class='btn btn-info btn-sm' onclick='verEstudiante({$row['id']})'>Ver QR</button>
                                <button class='btn btn-warning btn-sm' onclick='editarEstudiante({$row['id']})'>Editar</button>
                                <button class='btn btn-danger btn-sm' onclick='eliminarEstudiante({$row['id']})'>Eliminar</button>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nuevo Estudiante -->
    <div class="modal fade" id="modalNuevoEstudiante" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoEstudiante">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grupo</label>
                            <select class="form-control" name="grupo_id">
                                <option value="">Sin grupo</option>
                                <?php
                                $stmt = $conn->query("SELECT id, nombre FROM grupos ORDER BY nombre");
                                while ($grupo = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$grupo['id']}'>{$grupo['nombre']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="formNuevoEstudiante">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver QR -->
    <div class="modal fade" id="modalVerQR" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Código QR del Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrContainer"></div>
                    <button class="btn btn-primary mt-3" onclick="descargarQR()">Descargar QR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Definir la traducción directamente en lugar de cargarla de un archivo externo
        const dataTableEspanol = {
            "decimal": "",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar columna ascendente",
                "sortDescending": ": activar para ordenar columna descendente"
            }
        };

        $(document).ready(function() {
            $('#tablaEstudiantes').DataTable({
                language: dataTableEspanol
            });
        });

        function buscarEstudiante() {
            const documento = $('#documento').val();
            if (!documento) {
                Swal.fire('Error', 'Por favor ingrese un número de documento', 'error');
                return;
            }

            // Primero obtener la información del estudiante
            $.get('../../includes/estudiantes/buscar.php', { documento: documento })
                .done(function(response) {
                    if (response.success) {
                        const estudiante = response.estudiante;
                        
                        // Obtener el total de asistencias
                        $.get('../../includes/estudiantes/obtener_total_asistencias.php', { estudiante_id: estudiante.id })
                            .done(function(totalResponse) {
                                const total = totalResponse.total || 0;
                                
                                $('#datosEstudiante').html(`
                                    <div class="mb-3">
                                        <h4>Nombre: ${estudiante.nombre} ${estudiante.apellidos}</h4>
                                        <h4>Documento: ${estudiante.documento}</h4>
                                        <h4>Grupo: ${estudiante.grupo_nombre || 'Sin grupo'}</h4>
                                        <h4>Total Asistencias: ${total}</h4>
                                    </div>
                                `);
                                
                                // Mostrar el QR
                                if (estudiante.qr_code) {
                                    $('#qrCode').attr('src', estudiante.qr_code)
                                        .on('load', function() {
                                            $('#resultadoBusqueda').show();
                                        })
                                        .on('error', function() {
                                            console.error('Error al cargar el QR');
                                            Swal.fire('Error', 'Error al cargar el código QR', 'error');
                                        });

                                    // Cargar asistencias
                                    cargarAsistencias(estudiante.id);
                                } else {
                                    Swal.fire('Error', 'El estudiante no tiene un código QR generado', 'error');
                                }

                                // Configurar botón de editar
                                $('#btnEditarEstudiante').off('click').on('click', function() {
                                    editarEstudiante(estudiante.id);
                                });
                            })
                            .fail(function() {
                                console.error('Error al obtener total de asistencias');
                            });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        $('#resultadoBusqueda').hide();
                    }
                })
                .fail(function(jqXHR) {
                    console.error('Error en la búsqueda:', jqXHR.responseText);
                    Swal.fire('Error', 'Error al buscar estudiante', 'error');
                    $('#resultadoBusqueda').hide();
                });
        }

        function cargarAsistencias(estudiante_id) {
            $.get('../../includes/estudiantes/obtener_asistencias.php', { estudiante_id: estudiante_id })
                .done(function(response) {
                    const asistencias = response.data || [];
                    $('#totalAsistencias').text(asistencias.length);
                    
                    if (asistencias.length > 0) {
                        const rows = asistencias.map(a => `
                            <tr>
                                <td>${a.fecha_hora}</td>
                                <td>${a.profesor_nombre}</td>
                            </tr>
                        `).join('');
                        $('#asistenciasBody').html(rows);
                        $('#tablaAsistencias').show();
                        $('#sinAsistencias').hide();
                    } else {
                        $('#tablaAsistencias').hide();
                        $('#sinAsistencias').show();
                    }
                })
                .fail(function(jqXHR) {
                    console.error('Error al cargar asistencias:', jqXHR.responseText);
                    $('#tablaAsistencias').hide();
                    $('#sinAsistencias').show();
                });
        }

        function verEstudiante(id) {
            $.get('../../includes/estudiantes/obtener.php', { id: id })
                .done(function(response) {
                    if (response.success && response.estudiante.qr_code) {
                        $('#qrContainer').html(`<img src="${response.estudiante.qr_code}" class="img-fluid" style="max-width: 300px;">`);
                        $('#modalVerQR').modal('show');
                    } else {
                        Swal.fire('Error', response.message || 'El estudiante no tiene un código QR generado', 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error al obtener datos del estudiante', 'error');
                });
        }

        function editarEstudiante(id) {
            window.location.href = `ver_estudiante.php?id=${id}`;
        }

        function eliminarEstudiante(id) {
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
                    $.post('../../includes/estudiantes/eliminar.php', { id: id })
                        .done(function(response) {
                            if (response.success) {
                                Swal.fire('¡Eliminado!', response.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error', 'Error al eliminar estudiante', 'error');
                        });
                }
            });
        }

        $(document).ready(function() {
            // Manejar el envío del formulario
            $('#formNuevoEstudiante').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Validar campos requeridos
                const nombre = formData.get('nombre').trim();
                const apellidos = formData.get('apellidos').trim();
                const documento = formData.get('documento').trim();

                if (!nombre || !apellidos || !documento) {
                    Swal.fire('Error', 'Todos los campos son requeridos', 'error');
                    return;
                }

                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '../../includes/estudiantes/crear.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                showConfirmButton: true
                            }).then(() => {
                                $('#modalNuevoEstudiante').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error en la petición:', xhr);
                        let mensaje = 'Error al guardar estudiante';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensaje = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', mensaje, 'error');
                    }
                });
            });
        });

        function descargarQR() {
            const qrImage = $('#qrContainer img')[0];
            
            if (!qrImage.complete || !qrImage.naturalHeight) {
                Swal.fire('Error', 'La imagen QR no está disponible', 'error');
                return;
            }

            try {
                // Crear un canvas temporal
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Establecer un tamaño fijo para el QR
                const targetSize = 800;
                canvas.width = targetSize;
                canvas.height = targetSize;
                
                // Dibujar fondo blanco
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, targetSize, targetSize);
                
                // Calcular el tamaño y posición para centrar el QR
                const size = Math.min(qrImage.naturalWidth, qrImage.naturalHeight, targetSize);
                const x = (targetSize - size) / 2;
                const y = (targetSize - size) / 2;
                
                // Dibujar el QR
                ctx.drawImage(qrImage, x, y, size, size);
                
                // Convertir a JPG y descargar
                const jpgUrl = canvas.toDataURL('image/jpeg', 1.0);
                const link = document.createElement('a');
                link.href = jpgUrl;
                link.download = 'qr-estudiante.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error('Error al procesar la imagen:', error);
                Swal.fire('Error', 'Error al procesar la imagen para descarga', 'error');
            }
        }

        function descargarQRBusqueda() {
            const qrImage = document.getElementById('qrCode');
            
            if (!qrImage.complete || !qrImage.naturalHeight) {
                Swal.fire('Error', 'La imagen QR no está disponible', 'error');
                return;
            }

            try {
                // Crear un canvas temporal
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Establecer un tamaño fijo para el QR
                const targetSize = 800;
                canvas.width = targetSize;
                canvas.height = targetSize;
                
                // Dibujar fondo blanco sólido
                ctx.fillStyle = 'rgb(255, 255, 255)';
                ctx.fillRect(0, 0, targetSize, targetSize);
                
                // Calcular el tamaño y posición para centrar el QR
                const size = Math.min(qrImage.naturalWidth, qrImage.naturalHeight, targetSize);
                const x = (targetSize - size) / 2;
                const y = (targetSize - size) / 2;
                
                // Dibujar el QR
                ctx.drawImage(qrImage, x, y, size, size);
                
                // Asegurar que el QR sea negro sólido
                const imageData = ctx.getImageData(0, 0, targetSize, targetSize);
                const data = imageData.data;
                for (let i = 0; i < data.length; i += 4) {
                    // Si el pixel no es completamente blanco, hacerlo negro
                    if (data[i] < 255 || data[i + 1] < 255 || data[i + 2] < 255) {
                        data[i] = 0;     // R
                        data[i + 1] = 0; // G
                        data[i + 2] = 0; // B
                        data[i + 3] = 255; // Alpha
                    }
                }
                ctx.putImageData(imageData, 0, 0);
                
                // Convertir a JPG con máxima calidad
                const jpgUrl = canvas.toDataURL('image/jpeg', 1.0);
                const link = document.createElement('a');
                link.href = jpgUrl;
                link.download = 'qr-estudiante.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error('Error al procesar la imagen:', error);
                Swal.fire('Error', 'Error al procesar la imagen para descarga', 'error');
            }
        }
    </script>
</body>
</html> 