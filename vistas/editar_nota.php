<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verifica si se reciben los parámetros necesarios
if (isset($_GET['estudiante_id'], $_GET['anio_academico_id'], $_GET['nota_id'])) {
    $estudianteId = intval($_GET['estudiante_id']);
    $anioAcademicoId = intval($_GET['anio_academico_id']);
    $notaId = intval($_GET['nota_id']);

    // Consulta para obtener la nota a editar
    $sqlEditNota = "SELECT n.calificacion, n.materia_id, n.lapso
                    FROM notas n
                    WHERE n.id = :notaId AND n.estudiante_id = :estudianteId";
    $stmtEditNota = $pdo->prepare($sqlEditNota);
    $stmtEditNota->execute([
        ':notaId' => $notaId,
        ':estudianteId' => $estudianteId,
    ]);

    $notaEdit = $stmtEditNota->fetch(PDO::FETCH_ASSOC);

    // Obtener las materias para el formulario
    $sqlMaterias = "SELECT id, nombre FROM materias WHERE anio_academico_id = :anioAcademicoId";
    $stmtMaterias = $pdo->prepare($sqlMaterias);
    $stmtMaterias->execute([':anioAcademicoId' => $anioAcademicoId]);
    $materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

    // Si el formulario es enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calificacion'], $_POST['materia_id'], $_POST['lapso'])) {
        $calificacion = floatval($_POST['calificacion']);
        $materiaId = intval($_POST['materia_id']);
        $lapso = intval($_POST['lapso']);

        // Validar que el lapso esté dentro del rango correcto
        if ($lapso < 1 || $lapso > 3) {
            die('Lapso inválido.');
        }

        // Verificar si ya existe otra nota con la misma materia y lapso
        $sqlCheckNota = "SELECT COUNT(*) FROM notas 
                         WHERE estudiante_id = :estudianteId 
                         AND materia_id = :materiaId 
                         AND lapso = :lapso
                         AND id != :notaId"; // Excluir la nota que se está editando
        $stmtCheckNota = $pdo->prepare($sqlCheckNota);
        $stmtCheckNota->execute([
            ':estudianteId' => $estudianteId,
            ':materiaId' => $materiaId,
            ':lapso' => $lapso,
            ':notaId' => $notaId
        ]);
        $exists = $stmtCheckNota->fetchColumn();

        if ($exists > 0) {
            echo "<script>
                alert('❌ Ya existe una nota registrada en esta materia y lapso para este estudiante.');
                history.back();
            </script>";
            exit();
        }

        // Si no existe duplicado, proceder con la actualización
        $sqlUpdateNota = "UPDATE notas SET calificacion = :calificacion, materia_id = :materiaId, lapso = :lapso WHERE id = :notaId";
        $stmtUpdateNota = $pdo->prepare($sqlUpdateNota);
        $stmtUpdateNota->execute([
            ':calificacion' => $calificacion,
            ':materiaId' => $materiaId,
            ':lapso' => $lapso,
            ':notaId' => $notaId,
        ]);

        // Mostrar mensaje de éxito y redirigir
        echo "<script>
            alert('✅ Nota actualizada exitosamente.');
            window.location.href = 'ver_notas.php?estudiante_id=$estudianteId&anio_academico_id=$anioAcademicoId';
        </script>";
        exit();
    }
} else {
    die('Faltan parámetros requeridos.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Editar Nota</title>
    <style>
        /* Estilos básicos para el formulario */
        .edit-anio-container {
            width: 50%;
            margin: 20px auto;
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
            <h1>Editar Nota</h1>
            <button class="register-btn" onclick="history.back();">Atrás</button>
        </header>

        <div class="edit-anio-container">
            <form method="post" action="">
                <label for="materia_id">Materia:</label>
                <select name="materia_id" id="materia_id" required>
                    <option value="">Seleccionar materia</option>
                    <?php foreach ($materias as $materia): ?>
                        <option value="<?= $materia['id']; ?>" <?= $materia['id'] == $notaEdit['materia_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($materia['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="lapso">Lapso:</label>
                <select name="lapso" id="lapso" required>
                    <option value="1" <?= $notaEdit['lapso'] == 1 ? 'selected' : ''; ?>>1er Lapso</option>
                    <option value="2" <?= $notaEdit['lapso'] == 2 ? 'selected' : ''; ?>>2do Lapso</option>
                    <option value="3" <?= $notaEdit['lapso'] == 3 ? 'selected' : ''; ?>>3er Lapso</option>
                </select>

                <label for="calificacion">Calificación:</label>
                <input type="number" name="calificacion" id="calificacion" step="0.1" min="0" max="20" value="<?= htmlspecialchars($notaEdit['calificacion']); ?>" required>

                <button type="submit">Guardar Cambios</button>
            </form>
        </div>
    </main>
</body>
</html>
