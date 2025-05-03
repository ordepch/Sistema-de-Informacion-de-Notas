<?php<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verificar si se ingresó una cédula en la búsqueda
$cedulaBuscar = isset($_GET['cedula']) ? trim($_GET['cedula']) : '';

// Consulta para obtener los estudiantes
$sql = "SELECT estudiantes.id, estudiantes.nombres, estudiantes.apellidos, estudiantes.cedula, estudiantes.estado, 
               estudiantes.genero, estudiantes.fecha_nacimiento, estudiantes.edad, estudiantes.parentesco_con_representante, 
               estudiantes.direccion, CONCAT(anios_academicos.anio_inicio, ' - ', anios_academicos.anio_fin) AS anio_academico, 
               CONCAT(representantes.nombres, ' ', representantes.apellidos) AS representante
        FROM estudiantes
        JOIN anios_academicos ON estudiantes.anio_academico_id = anios_academicos.id
        JOIN representantes ON estudiantes.representante_id = representantes.id";

// Filtrar por cédula si se ingresó un valor
if (!empty($cedulaBuscar)) {
    $sql .= " WHERE estudiantes.cedula = :cedula";
}

$stmt = $pdo->prepare($sql);

if (!empty($cedulaBuscar)) {
    $stmt->bindParam(':cedula', $cedulaBuscar, PDO::PARAM_STR);
}

$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener años académicos
$sqlAnios = "SELECT id, CONCAT(anio_inicio, ' - ', anio_fin) AS anio_academico FROM anios_academicos";
$stmtAnios = $pdo->query($sqlAnios);
$aniosAcademicos = $stmtAnios->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener representantes
$sqlRepresentantes = "SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM representantes";
$stmtRepresentantes = $pdo->query($sqlRepresentantes);
$representantes = $stmtRepresentantes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Ver Estudiantes</title>
    <style>
        /* Estilos mejorados para reducir la altura del formulario */
        .new-estudiante-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            z-index: 1000;
            width: 80%;
            max-width: 350px;
            animation: fadeIn 0.3s ease;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .new-estudiante-container form {
            display: flex;
            flex-direction: column;
        }

        /* Reduce el margen y padding de los campos para hacer el formulario más pequeño */
        .new-estudiante-container input,
        .new-estudiante-container select,
        .new-estudiante-container button {
            margin: 6px 0;
            padding: 8px;
            font-size: 0.9rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .new-estudiante-container button {
            background: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        .new-estudiante-container button:hover {
            background: #45a049;
        }

        /* Reduce la altura del botón y su margen */
        .register-btn {
            background: #4caf50;
            color: #fff;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .register-btn:hover {
            background: #45a049;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4d4d;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            font-size: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s ease-in-out, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .close-btn:hover {
            background: #ff1a1a;
            transform: scale(1.1);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .close-btn:active {
            transform: scale(0.9);
        }

        .title2 {
            text-align: center;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .divcontainer {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Reduce la altura del formulario */
        .inputselect {
            width: 200px;
            padding: 6px;
        }

        .register {
            margin-left: 0;
            width: 100%;
        }

        /* Ocultar el segundo paso inicialmente */
        #step-2 {
            display: none;
        }

        /* Botón "Siguiente" deshabilitado inicialmente */
        #next-step-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .action-btn {
    text-decoration: none;
    padding: 5px 10px;
    margin: 0 5px;
    border-radius: 5px;
    color: #fff;
    background-color: #007bff;
    display: inline-block;
}
.search-form {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    padding: 8px;
    border-radius: 25px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    max-width: 350px;
    width: 100%;
    margin-left: 520px;
    margin-top: -50px;
}

.search-form input {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px;
    font-size: 1rem;
    border-radius: 20px;
    background: #f5f5f5;
}

.search-form button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 1rem;
}

.search-form button:hover {
    background: #0056b3;
}

.search-form i {
    font-size: 1rem;
}

    </style>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
        <header>
            <h1>Estudiantes</h1>
            <button class="register-btn" onclick="showForm()">Registrar estudiante nuevo</button>
            <form method="GET" class="search-form">
            <input type="text" id="search-cedula" name="cedula" 
    placeholder="Buscar por cédula..." 
    value="<?= htmlspecialchars($cedulaBuscar); ?>" 
    pattern="\d{7,8}" 
    title="La cédula debe contener solo números y tener entre 7 y 8 dígitos" 
    required>
                <button type="submit"><i class="fas fa-search"></i> Buscar</button>
            </form>
        </header>
        <script>
            document.getElementById("search-cedula").addEventListener("input", function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 8); // Solo números, máx. 8 caracteres
});

        </script>
        <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Cédula</th>
                <th>Estado</th>
                <th>Género</th>
                <th>Fecha de Nacimiento</th>
                <th>Edad</th>
                <th>Parentesco</th>
                <th>Dirección</th>
                <th>Año Académico</th>
                <th>Representante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($estudiantes)): ?>
                <?php foreach ($estudiantes as $estudiante): ?>
                    <?php
                    // Determinar si "Ver Notas Académicas" debe estar habilitado
                    $puedeVerNotas = $estudiante['estado'] === 'activo' || $estudiante['estado'] === 'inactivo';
                    $esQuintoAnio = strpos($estudiante['anio_academico'], '5to') !== false;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($estudiante['nombres']); ?></td>
                        <td><?= htmlspecialchars($estudiante['apellidos']); ?></td>
                        <td><?= htmlspecialchars($estudiante['cedula']); ?></td>
                        <td><?= htmlspecialchars($estudiante['estado']); ?></td>
                        <td><?= htmlspecialchars($estudiante['genero']); ?></td>
                        <td><?= htmlspecialchars($estudiante['fecha_nacimiento']); ?></td>
                        <td><?= htmlspecialchars($estudiante['edad']); ?></td>
                        <td><?= htmlspecialchars($estudiante['parentesco_con_representante']); ?></td>
                        <td><?= htmlspecialchars($estudiante['direccion']); ?></td>
                        <td><?= htmlspecialchars($estudiante['anio_academico']); ?></td>
                        <td><?= htmlspecialchars($estudiante['representante']); ?></td>
                        <td>
    <a href="editar_estudiante.php?id=<?= urlencode($estudiante['id']); ?>" class="action-btn">Editar</a>
    
<!-- Botón Ver Notas Académicas -->
<a href="ver_notas_academicas.php?id=<?= htmlspecialchars($estudiante['id']); ?>" class="action-btn">Ver Notas Académicas</a>

</td>


                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12">No hay estudiantes registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    </main>

    <div class="overlay" onclick="hideForm()"></div>

    <div class="new-estudiante-container" id="new-estudiante-container">
        <button class="close-btn" onclick="hideForm()">&times;</button>
        <h2>Registrar Nuevo Estudiante</h2>
        <form id="student-form" action="../funcion/registrar_estudiante.php" method="post">
            
            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" required>

            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>

            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required>

            <label for="estado">Estado:</label>
            <select id="estado" name="estado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>

            <label for="genero">Género:</label>
            <select id="genero" name="genero" required>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
            </select>

            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required oninput="calcularEdad()">

            <!-- Campo oculto para almacenar la edad automáticamente -->
            <input type="hidden" id="edad" name="edad">

            <label for="representante">Representante:</label>
            <select id="representante" name="representante" required>
                <option value="">Seleccione un representante</option>
                <?php
                foreach ($representantes as $representante) {
                    echo '<option value="' . htmlspecialchars($representante['id']) . '">' . htmlspecialchars($representante['nombre_completo']) . '</option>';
                }
                ?>
            </select>

            <label for="parentesco">Parentezco con Representante:</label>
            <input type="text" id="parentesco" name="parentesco">

            <label for="anio_academico">Año Académico:</label>
                    <select id="anio_academico" name="anio_academico" required>
                        <option value="">Seleccione un año académico</option>
                        <?php
                        foreach ($aniosAcademicos as $anio) {
                            echo '<option value="' . htmlspecialchars($anio['id']) . '">' . htmlspecialchars($anio['anio_academico']) . '</option>';
                        }
                        ?>
                    </select>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion">

            <button type="submit">Registrar</button>
        </form>
    </div>

    <script>
        function showForm() {
            document.getElementById('new-estudiante-container').style.display = 'block';
            document.querySelector('.overlay').style.display = 'block';
        }

        function hideForm() {
            document.getElementById('new-estudiante-container').style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }

        function calcularEdad() {
            let fechaNacimiento = document.getElementById("fecha_nacimiento").value;
            let edadInput = document.getElementById("edad");

            if (fechaNacimiento) {
                let hoy = new Date();
                let nacimiento = new Date(fechaNacimiento);
                let edad = hoy.getFullYear() - nacimiento.getFullYear();
                let mes = hoy.getMonth() - nacimiento.getMonth();
                let dia = hoy.getDate() - nacimiento.getDate();

                if (mes < 0 || (mes === 0 && dia < 0)) {
                    edad--;
                }

                edadInput.value = edad >= 0 ? edad : 0; // Asegura que la edad no sea negativa
            }
        }

        document.getElementById("student-form").addEventListener("submit", function(event) {
            calcularEdad(); // Calcula la edad antes de enviar el formulario
        });

        // Validación para la cédula (solo números del 0-9 y longitud entre 7 y 8)
        document.getElementById('cedula').addEventListener('input', function(event) {
            let cedula = event.target.value;

            // Permitir solo números del 0 al 9
            cedula = cedula.replace(/[^0-9]/g, '');

            // Restringir la longitud máxima a 8 caracteres
            if (cedula.length > 8) {
            cedula = cedula.slice(0, 8);
            }

            event.target.value = cedula;
        });

    // Validación para la cédula (solo números del 0-9 y longitud entre 7 y 9)
    document.getElementById('cedula').addEventListener('input', function(event) {
        let cedula = event.target.value;

        // Permitir solo números del 0 al 9
        cedula = cedula.replace(/[^0-9]/g, ''); // Eliminar cualquier cosa que no sea un número

        // Verifica que la longitud esté entre 7 y 9
        if (cedula.length > 9) {
            cedula = cedula.slice(0, 9); // Limita a 9 dígitos si es mayor
        }

        event.target.value = cedula; // Asigna el valor actualizado
    });

    // Validación al enviar el formulario (verificar longitud de cédula)
    document.getElementById('student-form').addEventListener('submit', function(event) {
        let cedula = document.getElementById('cedula').value;

        // Verifica si la cédula tiene entre 7 y 9 dígitos
        if (cedula.length < 7 || cedula.length > 9) {
            event.preventDefault(); // Evita el envío del formulario
            alert("La cédula debe tener entre 7 y 9 dígitos."); // Muestra el mensaje emergente
        }
    });

                // Función para validar solo letras (A-Z, a-z) en nombres, apellidos y parentesco
                function validarSoloLetras(event) {
                    let valor = event.target.value;
                    
                    // Reemplaza cualquier carácter que no sea letra o espacio
                    valor = valor.replace(/[^a-zA-Z\s]/g, ''); 
                    
                    event.target.value = valor;
                }

                // Aplicar la validación a los campos de texto
                document.getElementById('nombres').addEventListener('input', validarSoloLetras);
                document.getElementById('apellidos').addEventListener('input', validarSoloLetras);
                document.getElementById('parentesco').addEventListener('input', validarSoloLetras);

                // Validación al enviar el formulario (verificar que los campos obligatorios no estén vacíos)
                document.getElementById('student-form').addEventListener('submit', function(event) {
                    let nombres = document.getElementById('nombres').value;
                    let apellidos = document.getElementById('apellidos').value;

                    // Verificar si los campos obligatorios están vacíos
                    if (!nombres || !apellidos) {
                        event.preventDefault(); // Evita el envío si los campos son vacíos
                        alert("Por favor, ingrese todos los campos obligatorios."); // Muestra el mensaje de error
                    }
                });
    </script>
</body>
</html>
