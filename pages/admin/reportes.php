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
    <title>Reportes de Asistencia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- DateRangePicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Panel de Administración</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Inicio</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['SCRIPT_NAME'], 2); ?>/includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Reportes de Asistencia</h2>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filtrosForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="grupo" class="form-label">Grupo</label>
                                <select class="form-select" id="grupo" name="grupo">
                                    <option value="">Todos los grupos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fechas" class="form-label">Rango de Fechas</label>
                                <input type="text" class="form-control" id="fechas" name="fechas">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="profesor" class="form-label">Profesor</label>
                                <select class="form-select" id="profesor" name="profesor">
                                    <option value="">Todos los profesores</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Generar Reporte</button>
                </form>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reporteTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Documento</th>
                                <th>Grupo</th>
                                <th>Profesor</th>
                                <th>Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <!-- Moment.js y DateRangePicker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Función para obtener la ruta base
        function getBasePath() {
            const path = window.location.pathname;
            return path.substring(0, path.indexOf('/pages'));
        }

        // Inicializar DateRangePicker
        $('#fechas').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        });

        // Cargar grupos
        $.get(getBasePath() + '/includes/reportes/get_grupos.php', function(data) {
            data.forEach(function(grupo) {
                $('#grupo').append(`<option value="${grupo.id}">${grupo.nombre}</option>`);
            });
        });

        // Cargar profesores
        $.get(getBasePath() + '/includes/reportes/get_profesores.php', function(data) {
            data.forEach(function(profesor) {
                $('#profesor').append(`<option value="${profesor.id}">${profesor.nombre} ${profesor.apellidos}</option>`);
            });
        });

        // Variable para almacenar la instancia de DataTable
        var table;

        // Función para inicializar o reinicializar la tabla
        function initializeTable(columns) {
            if ($.fn.DataTable.isDataTable('#reporteTable')) {
                $('#reporteTable').DataTable().destroy();
                $('#reporteTable thead, #reporteTable tbody').empty();
            }

            // Crear los encabezados de la tabla dinámicamente
            let headerHtml = '<tr>';
            columns.forEach(function(column) {
                headerHtml += `<th>${column.title}</th>`;
            });
            headerHtml += '</tr>';
            $('#reporteTable thead').html(headerHtml);

            table = $('#reporteTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Copiar',
                        className: 'btn btn-primary',
                        title: function() {
                            var fecha = moment().format('DD-MM-YYYY_HH-mm');
                            return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                        }
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-primary',
                        exportOptions: {
                            columns: ':visible'
                        },
                        title: function() {
                            var fecha = moment().format('DD-MM-YYYY_HH-mm');
                            return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                        },
                        filename: function() {
                            var fecha = moment().format('DD-MM-YYYY_HH-mm');
                            return 'Reporte_' + $('#grupo option:selected').text().replace(/ /g, '_') + '_' + fecha;
                        },
                        charset: 'utf-8',
                        bom: true,
                        customize: function(csv) {
                            return '\ufeff' + csv;
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-primary',
                        exportOptions: {
                            columns: ':visible'
                        },
                        title: function() {
                            var fecha = moment().format('DD-MM-YYYY_HH-mm');
                            return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                        },
                        filename: function() {
                            var fecha = moment().format('DD-MM-YYYY_HH-mm');
                            return 'Reporte_' + $('#grupo option:selected').text().replace(/ /g, '_') + '_' + fecha;
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c[r^="C"]', sheet).each(function() {
                                if($(this).text() === 'No asistió') {
                                    $(this).attr('s', '2');
                                } else if($(this).text() === 'Si asistió') {
                                    $(this).attr('s', '3');
                                }
                            });
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir',
                        className: 'btn btn-primary',
                        exportOptions: {
                            columns: ':visible'
                        },
                        title: function() {
                            var fecha = moment().format('DD-MM-YYYY_HH-mm');
                            return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                        },
                        customize: function(win) {
                            $(win.document.body)
                                .css('font-size', '10pt')
                                .find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                            
                            $(win.document.body).find('td:contains("No asistió")')
                                .css('color', 'red');
                            $(win.document.body).find('td:contains("Si asistió")')
                                .css('color', 'green');
                        }
                    }
                ],
                language: {
                    decimal: "",
                    emptyTable: "No hay datos disponibles",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar la columna ascendente",
                        sortDescending: ": activar para ordenar la columna descendente"
                    }
                },
                columns: columns,
                data: [],
                createdRow: function(row, data, dataIndex) {
                    $('td', row).each(function() {
                        if($(this).text() === 'No asistió') {
                            $(this).css('color', 'red');
                        } else if($(this).text() === 'Si asistió') {
                            $(this).css('color', 'green');
                        }
                    });
                }
            });
        }

        // Inicializar tabla con columnas por defecto
        initializeTable([
            { title: 'Estudiante', data: 'estudiante' },
            { title: 'Documento', data: 'documento' }
        ]);

        // Manejar envío del formulario
        $('#filtrosForm').on('submit', function(e) {
            e.preventDefault();
            
            var grupo = $('#grupo').val();
            var profesor = $('#profesor').val();
            var fechas = $('#fechas').val();

            if (!grupo) {
                alert('Por favor seleccione un grupo');
                return;
            }

            $.ajax({
                url: '../../includes/reportes/get_asistencias.php',
                method: 'POST',
                data: {
                    grupo: grupo,
                    profesor: profesor,
                    fechas: fechas
                },
                success: function(response) {
                    if (response.error) {
                        // Si hay error, mostrar mensaje y limpiar la tabla
                        alert(response.message);
                        if ($.fn.DataTable.isDataTable('#reporteTable')) {
                            $('#reporteTable').DataTable().clear().destroy();
                        }
                        // Limpiar el contenido de la tabla
                        $('#reporteTable').empty();
                        // Ocultar los botones de exportación
                        $('.dt-buttons').hide();
                        return;
                    }

                    // Si hay datos, inicializar la tabla
                    if ($.fn.DataTable.isDataTable('#reporteTable')) {
                        $('#reporteTable').DataTable().destroy();
                    }

                    // Crear los encabezados de la tabla dinámicamente
                    let headerHtml = '<thead><tr>';
                    response.columns.forEach(function(column) {
                        headerHtml += `<th>${column.title}</th>`;
                    });
                    headerHtml += '</tr></thead><tbody></tbody>';
                    $('#reporteTable').html(headerHtml);

                    table = $('#reporteTable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            {
                                extend: 'copy',
                                text: 'Copiar',
                                className: 'btn btn-primary',
                                title: function() {
                                    var fecha = moment().format('DD-MM-YYYY_HH-mm');
                                    return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                                }
                            },
                            {
                                extend: 'csv',
                                text: 'CSV',
                                className: 'btn btn-primary',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                title: function() {
                                    var fecha = moment().format('DD-MM-YYYY_HH-mm');
                                    return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                                },
                                filename: function() {
                                    var fecha = moment().format('DD-MM-YYYY_HH-mm');
                                    return 'Reporte_' + $('#grupo option:selected').text().replace(/ /g, '_') + '_' + fecha;
                                },
                                charset: 'utf-8',
                                bom: true,
                                customize: function(csv) {
                                    return '\ufeff' + csv;
                                }
                            },
                            {
                                extend: 'excel',
                                text: 'Excel',
                                className: 'btn btn-primary',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                title: function() {
                                    var fecha = moment().format('DD-MM-YYYY_HH-mm');
                                    return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                                },
                                filename: function() {
                                    var fecha = moment().format('DD-MM-YYYY_HH-mm');
                                    return 'Reporte_' + $('#grupo option:selected').text().replace(/ /g, '_') + '_' + fecha;
                                },
                                customize: function(xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    $('row c[r^="C"]', sheet).each(function() {
                                        if($(this).text() === 'No asistió') {
                                            $(this).attr('s', '2');
                                        } else if($(this).text() === 'Si asistió') {
                                            $(this).attr('s', '3');
                                        }
                                    });
                                }
                            },
                            {
                                extend: 'print',
                                text: 'Imprimir',
                                className: 'btn btn-primary',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                title: function() {
                                    var fecha = moment().format('DD-MM-YYYY_HH-mm');
                                    return 'Reporte - ' + $('#grupo option:selected').text() + ' - ' + fecha;
                                },
                                customize: function(win) {
                                    $(win.document.body)
                                        .css('font-size', '10pt')
                                        .find('table')
                                        .addClass('compact')
                                        .css('font-size', 'inherit');
                                    
                                    $(win.document.body).find('td:contains("No asistió")')
                                        .css('color', 'red');
                                    $(win.document.body).find('td:contains("Si asistió")')
                                        .css('color', 'green');
                                }
                            }
                        ],
                        language: {
                            decimal: "",
                            emptyTable: "No hay datos disponibles",
                            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                            infoEmpty: "Mostrando 0 a 0 de 0 registros",
                            infoFiltered: "(filtrado de _MAX_ registros totales)",
                            infoPostFix: "",
                            thousands: ",",
                            lengthMenu: "Mostrar _MENU_ registros",
                            loadingRecords: "Cargando...",
                            processing: "Procesando...",
                            search: "Buscar:",
                            zeroRecords: "No se encontraron registros coincidentes",
                            paginate: {
                                first: "Primero",
                                last: "Último",
                                next: "Siguiente",
                                previous: "Anterior"
                            },
                            aria: {
                                sortAscending: ": activar para ordenar la columna ascendente",
                                sortDescending: ": activar para ordenar la columna descendente"
                            }
                        },
                        columns: response.columns,
                        data: response.data,
                        createdRow: function(row, data, dataIndex) {
                            $('td', row).each(function() {
                                if($(this).text() === 'No asistió') {
                                    $(this).css('color', 'red');
                                } else if($(this).text() === 'Si asistió') {
                                    $(this).css('color', 'green');
                                }
                            });
                        }
                    });

                    // Mostrar los botones de exportación solo si hay datos
                    $('.dt-buttons').show();
                },
                error: function(xhr, status, error) {
                    alert('Error al generar el reporte: ' + error);
                    // Limpiar la tabla y ocultar botones en caso de error
                    if ($.fn.DataTable.isDataTable('#reporteTable')) {
                        $('#reporteTable').DataTable().clear().destroy();
                    }
                    $('#reporteTable').empty();
                    $('.dt-buttons').hide();
                }
            });
        });
    });
    </script>
</body>
</html> 