<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verifica si se reciben los parámetros necesarios
if (isset($_GET['estudiante_id'], $_GET['anio_academico_id'])) {
    $estudianteId = intval($_GET['estudiante_id']);
    $anioAcademicoId = intval($_GET['anio_academico_id']);

    // Consulta para obtener las notas del estudiante
    $sql = "SELECT m.nombre AS materia, n.calificacion, n.lapso, n.id AS nota_id
            FROM notas n
            INNER JOIN materias m ON n.materia_id = m.id
            WHERE n.estudiante_id = :estudianteId AND m.anio_academico_id = :anioAcademicoId
            ORDER BY n.lapso ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':estudianteId' => $estudianteId,
        ':anioAcademicoId' => $anioAcademicoId,
    ]);

    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener las materias del año académico del estudiante
    $sqlMaterias = "SELECT m.id, m.nombre
                    FROM materias m
                    WHERE m.anio_academico_id = :anioAcademicoId";
    $stmtMaterias = $pdo->prepare($sqlMaterias);
    $stmtMaterias->execute([':anioAcademicoId' => $anioAcademicoId]);
    $materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

    // Si se envía el formulario de registrar nota
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['materia_id'], $_POST['calificacion'], $_POST['lapso'])) {
        $materiaId = intval($_POST['materia_id']);
        $calificacion = floatval($_POST['calificacion']);
        $lapso = intval($_POST['lapso']);

        // Verificar si la nota ya existe para el estudiante en la misma materia y lapso
        $sqlCheckNota = "SELECT COUNT(*) FROM notas 
                         WHERE estudiante_id = :estudianteId 
                         AND materia_id = :materiaId 
                         AND lapso = :lapso";
        $stmtCheckNota = $pdo->prepare($sqlCheckNota);
        $stmtCheckNota->execute([
            ':estudianteId' => $estudianteId,
            ':materiaId' => $materiaId,
            ':lapso' => $lapso,
        ]);
        $exists = $stmtCheckNota->fetchColumn();

        if ($exists > 0) {
            // Si la nota ya existe, mostrar alerta y no registrar
            echo "<script>
                alert('❌ El estudiante ya tiene una nota registrada en esta materia y este lapso.');
                history.back();
            </script>";
            exit();
        }

        // Verifica que la materia esté relacionada con el mismo año académico
        $sqlCheckMateria = "SELECT 1 FROM materias WHERE id = :materiaId AND anio_academico_id = :anioAcademicoId";
        $stmtCheckMateria = $pdo->prepare($sqlCheckMateria);
        $stmtCheckMateria->execute([
            ':materiaId' => $materiaId,
            ':anioAcademicoId' => $anioAcademicoId,
        ]);

        if ($stmtCheckMateria->fetch() && ($lapso >= 1 && $lapso <= 3)) {
            // Registrar la nota si no existe duplicado
            $sqlInsertNota = "INSERT INTO notas (estudiante_id, materia_id, lapso, calificacion)
                              VALUES (:estudianteId, :materiaId, :lapso, :calificacion)";
            $stmtInsertNota = $pdo->prepare($sqlInsertNota);
            $stmtInsertNota->execute([
                ':estudianteId' => $estudianteId,
                ':materiaId' => $materiaId,
                ':lapso' => $lapso,
                ':calificacion' => $calificacion,
            ]);

            // Mensaje de éxito y recargar la página
            echo "<script>
                alert('✅ Nota registrada exitosamente.');
                window.location.href = 'ver_notas.php?estudiante_id=$estudianteId&anio_academico_id=$anioAcademicoId';
            </script>";
            exit();
        } else {
            echo "<script>alert('⚠️ La materia seleccionada no corresponde al año académico del estudiante.');</script>";
        }
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
    <title>Notas del Estudiante</title>
    <style>
        .new-note-container, .edit-note-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            z-index: 1000;
            width: 80%;
            max-width: 400px;
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

        .register-btn2 {
            background: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-left: 20px;
        }

        .register-btn2:hover {
            background: #45a049;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f44336;
            color: #fff;
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
            background: #e53935;
            transform: scale(1.1);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .new-note-container form, .edit-note-container form {
            display: flex;
            flex-direction: column;
        }

        .new-note-container input, .edit-note-container input,
        .new-note-container select, .edit-note-container select,
        .new-note-container button, .edit-note-container button {
            margin: 10px 0;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .new-note-container button, .edit-note-container button {
            background: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        .new-note-container button:hover, .edit-note-container button:hover {
            background: #45a049;
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
    </style>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
        <header>
            <h1>Notas del Estudiante</h1>
            <button class="register-btn" onclick="showForm()">Registrar Nota</button>
            <button onclick="imprimirNotas()" class="register-btn2">Imprimir Notas</button>
        </header>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Lapso</th>
                        <th>Calificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($notas)): ?>
                        <?php foreach ($notas as $nota): ?>
                            <tr>
                                <td><?= htmlspecialchars($nota['materia']); ?></td>
                                <td><?= htmlspecialchars($nota['lapso']); ?></td>
                                <td><?= htmlspecialchars($nota['calificacion']); ?></td>
                                <td><a href="editar_nota.php?estudiante_id=<?= $estudianteId ?>&anio_academico_id=<?= $anioAcademicoId ?>&nota_id=<?= $nota['nota_id']; ?>" class="action-btn">Editar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No hay notas registradas para este estudiante.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Formulario para registrar nueva nota -->
    <div class="overlay" onclick="hideForm()"></div>
    <div class="new-note-container" id="new-note-container">
        <button class="close-btn" onclick="hideForm()">×</button>
        <h2>Registrar Nueva Nota</h2>
        <form action="" method="POST">
            <label for="materia_id">Materia:</label>
            <select name="materia_id" id="materia_id" required>
                <option value="">Seleccionar materia</option>
                <?php foreach ($materias as $materia): ?>
                    <option value="<?= $materia['id']; ?>"><?= htmlspecialchars($materia['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="lapso">Lapso:</label>
            <select name="lapso" id="lapso" required>
                <option value="">Seleccionar lapso</option>
                <option value="1">1er Lapso</option>
                <option value="2">2do Lapso</option>
                <option value="3">3er Lapso</option>
            </select>
            <br>
            <label for="calificacion">Calificación:</label>
            <input type="number" name="calificacion" id="calificacion" step="0.1" min="0" max="20" required>
            <br>
            <button type="submit">Registrar</button>
        </form>
    </div>

    <!-- Formulario para editar una nota -->
    <?php if (isset($notaEdit)): ?>
    <div class="overlay" onclick="hideEditForm()"></div>
    <div class="edit-note-container" id="edit-note-container">
        <button class="close-btn" onclick="hideEditForm()">×</button>
        <h2>Editar Nota</h2>
        <form action="" method="POST">
            <label for="calificacion">Calificación:</label>
            <input type="number" name="calificacion" id="calificacion" step="0.1" min="0" max="20" value="<?= htmlspecialchars($notaEdit['calificacion']); ?>" required>
            <br>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
    <?php endif; ?>

    <script>
        function showForm() {
            const container = document.getElementById('new-note-container');
            const overlay = document.querySelector('.overlay');
            container.style.display = 'block';
            overlay.style.display = 'block';
            container.style.animation = 'fadeIn 0.3s ease';
        }

        function hideForm() {
            const container = document.getElementById('new-note-container');
            const overlay = document.querySelector('.overlay');
            container.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                container.style.display = 'none';
                overlay.style.display = 'none';
            }, 300);
        }

        function hideEditForm() {
            const container = document.getElementById('edit-note-container');
            const overlay = document.querySelector('.overlay');
            container.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                container.style.display = 'none';
                overlay.style.display = 'none';
            }, 300);
        }
    </script>
    <script>
    function imprimirNotas() {
        const contenido = document.querySelector('.table-container').innerHTML;  // Obtener el contenido de la tabla
        const ventanaImpresion = window.open('', '', 'width=800,height=600');  // Abrir una nueva ventana
        ventanaImpresion.document.write('<html><head><title>Imprimir Notas</title>');
        ventanaImpresion.document.write('<style>body { font-family: Arial, sans-serif; margin: 20px; } table { width: 100%; border-collapse: collapse; } th, td { padding: 8px; text-align: left; border: 1px solid #ddd; } th { background-color: #f2f2f2; }</style>');  // Estilos de impresión
        ventanaImpresion.document.write('</head><body>');
        ventanaImpresion.document.write('<h1>Notas del Estudiante</h1>');
        ventanaImpresion.document.write(contenido);  // Insertar la tabla de notas
        ventanaImpresion.document.write('</body></html>');
        ventanaImpresion.document.close();  // Cerrar el documento
        ventanaImpresion.print();  // Imprimir
    }
</script>

</body>
</html>
