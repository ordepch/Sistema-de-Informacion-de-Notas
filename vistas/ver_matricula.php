<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verifica si se reciben los parámetros necesarios
if (isset($_GET['id'], $_GET['lapsos'], $_GET['anio'])) {
    $anioAcademicoId = intval($_GET['id']);
    $lapsos = intval($_GET['lapsos']);
    $anio = htmlspecialchars($_GET['anio']); // Usamos 'anio' en lugar de 'grado'

    // Consulta para obtener los estudiantes asociados
    $sql = "SELECT e.id, e.nombres, e.apellidos, e.cedula, e.estado, e.genero, e.fecha_nacimiento, e.edad
            FROM estudiantes e
            INNER JOIN anios_academicos aa ON e.anio_academico_id = aa.id
            WHERE e.anio_academico_id = :anioAcademicoId AND aa.lapsos = :lapsos AND aa.anio = :anio";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':anioAcademicoId' => $anioAcademicoId,
        ':lapsos' => $lapsos,
        ':anio' => $anio,  // Usamos el parámetro 'anio' en lugar de 'grado'
    ]);

    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    die('Faltan parámetros requeridos.');
}
?>

<style>
    .action-btn {
        text-decoration: none;
        padding: 5px 10px;
        margin: 0 5px;
        border-radius: 5px;
        color: #fff;
        background-color: #007bff;
        display: inline-block;
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Matrícula del Año Académico</title>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
        <header>
            <h1>Matrícula del Año Académico</h1>
            <button class="register-btn" onclick="window.location.href='ver_anio_academico.php';">Atrás</button>
        </header>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($estudiantes)): ?>
                        <?php foreach ($estudiantes as $estudiante): ?>
                            <tr>
                                <td><?= htmlspecialchars($estudiante['nombres']); ?></td>
                                <td><?= htmlspecialchars($estudiante['apellidos']); ?></td>
                                <td><?= htmlspecialchars($estudiante['cedula']); ?></td>
                                <td><?= htmlspecialchars($estudiante['estado']); ?></td>
                                <td><?= htmlspecialchars($estudiante['genero']); ?></td>
                                <td><?= htmlspecialchars($estudiante['fecha_nacimiento']); ?></td>
                                <td><?= htmlspecialchars($estudiante['edad']); ?></td>
                                <td>
                                    <form action="ver_notas.php" method="get" style="display:inline;">
                                        <input type="hidden" name="estudiante_id" value="<?= htmlspecialchars($estudiante['id']); ?>">
                                        <input type="hidden" name="anio_academico_id" value="<?= htmlspecialchars($anioAcademicoId); ?>">
                                        <button type="submit" class="action-btn">Ver notas</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No hay estudiantes registrados para este año académico, lapso y grado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
