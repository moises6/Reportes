<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="container col-xxl-8 px-4 py-5">
        <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
            <div class="col-10 mx-auto col-lg-5">
                <h1 class="display-5 fw-bold lh-1 mb-3">Generar Reportes</h1>
                <h4 class="fw-normal">Seleccione sus preferencias para generar un reporte personalizado.</h4>
            </div>
            <div class="col-10 mx-auto col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="formulario-filtros">
                            <!-- Selección de filtro -->
                            <div class="mb-3">
                                <label for="filtro" class="form-label">Filtrar por:</label>
                                <select id="filtro" name="filtro" class="form-select" required>
                                    <option value="">Seleccionar Filtro...</option>
                                    <!-- Aquí se cargarán los filtros dinámicamente -->
                                </select>
                            </div>

                            <!-- Campo dinámico para mostrar las opciones de valores del filtro seleccionado -->
                            <div class="mb-3">
                                <label for="valor" class="form-label">Selecciona el valor:</label>
                                <select id="valor" name="valor" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                </select>
                            </div>

                            <!-- Formato del reporte -->
                            <div class="mb-3">
                                <label for="formato" class="form-label">Formato del Reporte:</label>
                                <select id="formato" name="formato" class="form-select" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                Buscar y Generar Reporte
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const backendRoutes = {
            obtenerFiltros: '../backend/src/reports/obtenerFiltros.php',
            generarReporte: '../backend/src/reports/reporte.php'
        };

        // Función para cargar los filtros desde el backend al cargar la página
        function cargarFiltros() {
            fetch(backendRoutes.obtenerFiltros)
                .then(response => response.json())
                .then(data => {
                    const filtroSelect = document.getElementById('filtro');
                    filtroSelect.innerHTML = '<option value="">Seleccionar Filtro...</option>'; // Limpiar opciones anteriores
                    data.forEach(filtro => {
                        const option = document.createElement('option');
                        option.value = filtro.nombre;
                        option.textContent = filtro.nombre;
                        filtroSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar filtros:', error);
                    mostrarMensaje('Error', 'No se pudieron cargar los filtros.', 'error');
                });
        }

        // Cargar los filtros al cargar la página
        window.onload = cargarFiltros;

        // Cargar los valores según el filtro seleccionado
        document.getElementById('filtro').addEventListener('change', function() {
            const filtro = this.value;

            // Limpiar el campo de selección de valores
            const valorSelect = document.getElementById('valor');
            valorSelect.innerHTML = '<option value="">Seleccionar...</option>';

            // Realizar la solicitud al backend para obtener los valores del filtro seleccionado
            fetch(backendRoutes.obtenerFiltros, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        filtro: filtro
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        mostrarMensaje('Error', data.error, 'error');
                    } else {
                        // Cargar los valores obtenidos en el select
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item[filtro];
                            option.textContent = item[filtro];
                            valorSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al cargar los valores del filtro:', error);
                    mostrarMensaje('Error', 'No se pudieron cargar los valores del filtro.', 'error');
                });
        });

        // Manejar la generación del reporte
        document.getElementById('formulario-filtros').addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar la recarga de la página

            const formData = new FormData(this);

            // Realizar la solicitud al backend para generar el reporte
            fetch(backendRoutes.generarReporte, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.reporte) {
                        document.getElementById('resultado').innerHTML = `
                            <div class="alert alert-success" role="alert">
                                <h4 class="alert-heading">¡Reporte generado con éxito!</h4>
                                <p>Se ha creado un nuevo reporte.</p>
                                <hr>
                                <p class="mb-0">Descargar Reporte: <a href="${data.reporte}" class="alert-link" download>Descargar</a></p>
                            </div>
                        `;
                    } else {
                        mostrarMensaje('Error', 'Ocurrió un error al generar el reporte.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error en la generación del reporte:', error);
                    mostrarMensaje('Error', 'Ocurrió un error al procesar su solicitud.', 'error');
                });
        });

        // Función para mostrar mensajes
        function mostrarMensaje(titulo, mensaje, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo === 'error' ? 'danger' : 'success'}`;
            alertDiv.innerHTML = `
                <div class="alert-box">
                    <span class="closebtn">&times;</span>
                    <strong>${titulo}</strong> ${mensaje}
                </div>
            `;
            document.body.prepend(alertDiv);

            const closeBtn = alertDiv.querySelector('.closebtn');
            closeBtn.onclick = function() {
                alertDiv.style.display = 'none';
            };

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>

</html>