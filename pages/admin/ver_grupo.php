<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: grupos.php');
    exit;
}

$grupo_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->connect();

// Obtener información del grupo
try {
    $stmt = $conn->prepare("SELECT * FROM grupos WHERE id = ?");
    $stmt->execute([$grupo_id]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$grupo) {
        header('Location: grupos.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: grupos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Grupo - <?php echo htmlspecialchars($grupo['nombre']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="grupos.php">Volver a Grupos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">Grupo: <?php echo htmlspecialchars($grupo['nombre']); ?></h2>
                        <p><strong>ID:</strong> <?php echo $grupo['id']; ?></p>
                        <p><strong>Fecha de Creación:</strong> <?php echo $grupo['created_at']; ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarGrupoModal">
                            Editar Grupo
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Estudiantes en el Grupo</h3>
                        <table id="tablaEstudiantes" class="table table-striped">
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

    <!-- Modal Editar Grupo -->
    <div class="modal fade" id="editarGrupoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarGrupo">
                        <div class="mb-3">
                            <label for="nombreEdit" class="form-label">Nombre del Grupo</label>
                            <input type="text" class="form-control" id="nombreEdit" 
                                   value="<?php echo htmlspecialchars($grupo['nombre']); ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEdicion">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            const tabla = $('#tablaEstudiantes').DataTable({
                ajax: {
                    url: '../../includes/grupos/obtener_estudiantes.php',
                    data: { grupo_id: <?php echo $grupo_id; ?> },
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    { data: 'nombre' },
                    { data: 'apellidos' },
                    { data: 'documento' },
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-info" onclick="verEstudiante(${data.id})">Ver</button>
                                <button class="btn btn-sm btn-danger" onclick="removerEstudiante(${data.id})">Remover</button>
                            `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                }
            });

            // Guardar edición del grupo
            $('#btnGuardarEdicion').click(function() {
                const nombre = $('#nombreEdit').val();
                if (!nombre) {
                    Swal.fire('Error', 'El nombre del grupo es requerido', 'error');
                    return;
                }

                $.post('../../includes/grupos/editar.php', {
                    id: <?php echo $grupo_id; ?>,
                    nombre: nombre
                }, function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Grupo actualizado correctamente',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });
        });

        function verEstudiante(id) {
            window.location.href = `ver_estudiante.php?id=${id}`;
        }

        function removerEstudiante(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "El estudiante será removido del grupo",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('../../includes/grupos/remover_estudiante.php', {
                        estudiante_id: id,
                        grupo_id: <?php echo $grupo_id; ?>
                    }, function(response) {
                        if (response.success) {
                            Swal.fire('¡Removido!', 'El estudiante ha sido removido del grupo', 'success');
                            $('#tablaEstudiantes').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html> 