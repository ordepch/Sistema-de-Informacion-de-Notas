<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verificar si el id está presente en la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para obtener los detalles del estudiante
    $sql = "SELECT * FROM estudiantes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra el estudiante
    if (!$estudiante) {
        die('Estudiante no encontrado.');
    }
} else {
    die('No se ha especificado un id válido.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $estado = $_POST['estado'];
    $genero = $_POST['genero'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $parentesco = $_POST['parentesco_con_representante'];
    $direccion = $_POST['direccion'];
    $anio_academico_id = $_POST['anio_academico_id'];
    $representante_id = $_POST['representante_id'];

    // Actualizar el estudiante en la base de datos
    $sql = "UPDATE estudiantes 
            SET nombres = :nombres, apellidos = :apellidos, cedula = :cedula, estado = :estado, genero = :genero, 
                fecha_nacimiento = :fecha_nacimiento, parentesco_con_representante = :parentesco_con_representante, 
                direccion = :direccion, anio_academico_id = :anio_academico_id, representante_id = :representante_id
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':cedula' => $cedula,
        ':estado' => $estado,
        ':genero' => $genero,
        ':fecha_nacimiento' => $fecha_nacimiento,
        ':parentesco_con_representante' => $parentesco,
        ':direccion' => $direccion,
        ':anio_academico_id' => $anio_academico_id,
        ':representante_id' => $representante_id,
        ':id' => $id
    ]);

    // Redirige con mensaje de éxito
    echo "<script>
            alert('¡Estudiante actualizado exitosamente!');
            window.location.href = 'ver_estudiantes.php';
          </script>";
    exit;
}

// Obtener los representantes
$sql_representantes = "SELECT id, CONCAT(nombres, ' ', apellidos) AS representante_nombre FROM representantes";
$stmt_representantes = $pdo->prepare($sql_representantes);
$stmt_representantes->execute();
$representantes = $stmt_representantes->fetchAll(PDO::FETCH_ASSOC);

// Obtener los años académicos
$sql_anios_academicos = "SELECT id, CONCAT(anio, ' Año, Sección ', seccion, ', Año academico ', anio_inicio, '-', anio_fin, ' (Lapsos: ', lapsos, ')') AS anio_academico FROM anios_academicos";
$stmt_anios_academicos = $pdo->prepare($sql_anios_academicos);
$stmt_anios_academicos->execute();
$anios_academicos = $stmt_anios_academicos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Editar Estudiante</title>
    <style>
        /* Estilos básicos para el formulario */
        .edit-anio-container {
            width: 50%;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .edit-anio-container input,
        .edit-anio-container select,
        .edit-anio-container button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .edit-anio-container input {
            width: 95.5%;
        }

        .edit-anio-container button {
            background: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        .edit-anio-container button:hover {
            background: #45a049;
        }
        .register-btn {
            background: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .register-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
            <header>
                <h1>Editar Estudiante</h1>
                <button class="register-btn" onclick="window.location.href='ver_estudiantes.php';">Atrás</button>
            </header>
            <div class="edit-anio-container">
                <form action="editar_estudiante.php?id=<?= $estudiante['id']; ?>" method="post">
                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($estudiante['nombres']); ?>" required oninput="validarSoloLetras(event)">

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($estudiante['apellidos']); ?>" required oninput="validarSoloLetras(event)">

                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($estudiante['cedula']); ?>" required oninput="validarCedula(event)">

                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="activo" <?= $estudiante['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?= $estudiante['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>

                    <label for="genero">Género:</label>
                    <select id="genero" name="genero" required>
                        <option value="masculino" <?= $estudiante['genero'] === 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="femenino" <?= $estudiante['genero'] === 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                        <option value="otro" <?= $estudiante['genero'] === 'otro' ? 'selected' : ''; ?>>Otro</option>
                    </select>

                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $estudiante['fecha_nacimiento']; ?>" required>

                    <label for="parentesco_con_representante">Parentezco con Representante:</label>
                    <input type="text" id="parentesco_con_representante" name="parentesco_con_representante" value="<?= htmlspecialchars($estudiante['parentesco_con_representante']); ?>" required oninput="validarSoloLetras(event)">

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($estudiante['direccion']); ?>" required>

                    <label for="anio_academico_id">Año Académico:</label>
                    <select id="anio_academico_id" name="anio_academico_id" required>
                        <?php foreach ($anios_academicos as $anio_academico): ?>
                            <option value="<?= $anio_academico['id']; ?>" <?= $estudiante['anio_academico_id'] === $anio_academico['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($anio_academico['anio_academico']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="representante_id">Representante:</label>
                    <select id="representante_id" name="representante_id" required>
                        <?php foreach ($representantes as $representante): ?>
                            <option value="<?= $representante['id']; ?>" <?= $estudiante['representante_id'] === $representante['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($representante['representante_nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Actualizar</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Función para validar que solo se ingresen letras
        function validarSoloLetras(event) {
            let valor = event.target.value;
            valor = valor.replace(/[^a-zA-Z\s]/g, ''); // Eliminar caracteres no permitidos
            event.target.value = valor;
        }

        // Validación para la cédula (solo números del 0-9 y longitud entre 7 y 8)
        function validarCedula(event) {
            let cedula = event.target.value;
            cedula = cedula.replace(/[^0-9]/g, ''); // Eliminar cualquier cosa que no sea un número
            if (cedula.length > 8) {
                cedula = cedula.slice(0, 8); // Limitar a 8 dígitos si es mayor
            }
            event.target.value = cedula; // Asigna el valor actualizado
        }
    </script>
</body>
</html>
