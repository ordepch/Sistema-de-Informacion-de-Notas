<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verificar si el id está presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de año académico no proporcionado.");
}

$id = intval($_GET['id']);

// Obtener los detalles del año académico
$sql = "SELECT * FROM anios_academicos WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$anioAcademico = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anioAcademico) {
    die("Año académico no encontrado.");
}

// Procesar la actualización cuando se envíe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $anio_inicio = $_POST['anio_inicio'];
    $anio_fin = $_POST['anio_fin'];
    $lapsos = $_POST['lapsos'];
    $anio = $_POST['anio'];
    $seccion = $_POST['seccion'];

    try {
        // Verificar si ya existe otro año académico con los mismos datos
        $sql_check = "SELECT COUNT(*) FROM anios_academicos 
                      WHERE anio_inicio = :anio_inicio 
                      AND anio_fin = :anio_fin 
                      AND lapsos = :lapsos 
                      AND anio = :anio 
                      AND seccion = :seccion
                      AND id != :id"; // Excluir el mismo ID que se está editando
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([
            ':anio_inicio' => $anio_inicio,
            ':anio_fin' => $anio_fin,
            ':lapsos' => $lapsos,
            ':anio' => $anio,
            ':seccion' => $seccion,
            ':id' => $id
        ]);
        $exists = $stmt_check->fetchColumn();

        if ($exists > 0) {
            echo "<script>
                alert('❌ Este año académico ya existe con los mismos datos.');
                history.back();
            </script>";
            exit();
        }

        // Si no existe duplicado, proceder con la actualización
        $sql_update = "UPDATE anios_academicos 
                       SET anio_inicio = :anio_inicio, anio_fin = :anio_fin, lapsos = :lapsos, anio = :anio, seccion = :seccion
                       WHERE id = :id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            ':anio_inicio' => $anio_inicio,
            ':anio_fin' => $anio_fin,
            ':lapsos' => $lapsos,
            ':anio' => $anio,
            ':seccion' => $seccion,
            ':id' => $id
        ]);

        // Mostrar mensaje de éxito y redirigir
        echo "<script>
            alert('✅ Año académico actualizado correctamente.');
            window.location.href = 'ver_anio_academico.php';
        </script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>
            alert('⚠️ Error en la base de datos: " . addslashes($e->getMessage()) . "');
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
    <title>Editar Año Académico</title>
    <style>
        /* Estilos básicos para el formulario */
        .edit-anio-container {
            width: 50%;
            margin: 40px auto;
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
            <h1>Editar Año Académico</h1>
            <button class="register-btn" onclick="window.location.href='ver_anio_academico.php';">Atrás</button>
        </header>

        <div class="edit-anio-container">
            <form method="post">
                <label for="anio_inicio">Año de Inicio:</label>
                <input type="number" id="anio_inicio" name="anio_inicio" value="<?= htmlspecialchars($anioAcademico['anio_inicio']); ?>" required min="2005" max="<?= date('Y'); ?>" step="1">

                <label for="anio_fin">Año de Fin:</label>
                <input type="number" id="anio_fin" name="anio_fin" value="<?= htmlspecialchars($anioAcademico['anio_fin']); ?>" readonly>

                <label for="anio">Año:</label>
                <select id="anio" name="anio" required>
                    <?php 
                    $anios = ['1er', '2do', '3er', '4to', '5to'];
                    foreach ($anios as $opcion) {
                        echo "<option value='$opcion' " . ($anioAcademico['anio'] == $opcion ? 'selected' : '') . ">$opcion</option>";
                    }
                    ?>
                </select>

                <label for="lapsos">Lapso:</label>
                <select id="lapsos" name="lapsos" required>
                    <option value="1" <?= $anioAcademico['lapsos'] == '1' ? 'selected' : ''; ?>>Lapso 1</option>
                    <option value="2" <?= $anioAcademico['lapsos'] == '2' ? 'selected' : ''; ?>>Lapso 2</option>
                    <option value="3" <?= $anioAcademico['lapsos'] == '3' ? 'selected' : ''; ?>>Lapso 3</option>
                </select>

                <label for="seccion">Sección:</label>
                <select id="seccion" name="seccion" required>
                    <?php 
                    $secciones = ['A', 'B', 'C', 'D'];
                    foreach ($secciones as $opcion) {
                        echo "<option value='$opcion' " . ($anioAcademico['seccion'] == $opcion ? 'selected' : '') . ">$opcion</option>";
                    }
                    ?>
                </select>

                <button type="submit">Actualizar</button>
            </form>
        </div>
    </main>
</body>
</html>
