<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verificar si se proporcionó un ID válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de materia no proporcionado.");
}

$id = intval($_GET['id']);

// Obtener los datos de la materia específica
$sql = "SELECT id, nombre, anio_academico_id FROM materias WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$materia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    die("Materia no encontrada.");
}

// Obtener los años académicos para el formulario
$sqlAnios = "SELECT id, CONCAT(anio_inicio, ' - ', anio_fin) AS anio_academico FROM anios_academicos";
$stmtAnios = $pdo->query($sqlAnios);
$aniosAcademicos = $stmtAnios->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $anio_academico_id = intval($_POST['anio_academico']);

    if (!empty($nombre) && $anio_academico_id) {
        try {
            // Normalizar el nombre eliminando espacios y convirtiéndolo a minúsculas
            $nombre = strtolower(trim($nombre));

            // Verificar si la materia ya existe en otro registro con el mismo año académico
            $sqlCheck = "SELECT COUNT(*) FROM materias WHERE LOWER(TRIM(nombre)) = :nombre AND anio_academico_id = :anio_academico_id AND id != :id";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                'nombre' => $nombre,
                'anio_academico_id' => $anio_academico_id,
                'id' => $id
            ]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists > 0) {
                // Si la materia ya existe, mostrar alerta en JavaScript
                echo "<script>
                    alert('❌ Esta materia ya existe para este año académico.');
                    history.back();
                </script>";
                exit();
            }

            // Actualizar la materia
            $sqlUpdate = "UPDATE materias SET nombre = :nombre, anio_academico_id = :anio_academico_id WHERE id = :id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'nombre' => $_POST['nombre'], // Usamos el nombre original ingresado
                'anio_academico_id' => $anio_academico_id,
                'id' => $id
            ]);

            // Mostrar mensaje de éxito y recargar la página
            echo "<script>
                alert('✅ Materia actualizada exitosamente.');
                window.location.href = 'ver_materias.php';
            </script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>
                alert('⚠️ Error en la base de datos: " . addslashes($e->getMessage()) . "');
                history.back();
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('⚠️ Error: Todos los campos son obligatorios.');
            history.back();
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Editar Materia</title>
    <style>
        /* Estilos básicos para el formulario */
        .edit-anio-container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
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
            <h1>Editar Materia</h1>
            <button class="register-btn" onclick="window.location.href='ver_materias.php';">Atrás</button>
        </header>

        <div class="edit-anio-container">
            <form method="post">
                <label for="nombre">Nombre de la materia:</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($materia['nombre']); ?>" required>

                <label for="anio_academico">Año Académico:</label>
                <select id="anio_academico" name="anio_academico" required>
                    <option value="">Seleccione un año académico</option>
                    <?php foreach ($aniosAcademicos as $anio): ?>
                        <option value="<?= htmlspecialchars($anio['id']); ?>" <?= $anio['id'] == $materia['anio_academico_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($anio['anio_academico']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Guardar Cambios</button>
            </form>
        </div>
    </main>
</body>
</html>
