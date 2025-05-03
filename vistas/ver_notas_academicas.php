<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../conn/db.php'; // Conexión a la base de datos

// Obtener el ID del estudiante desde la URL
$estudiante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($estudiante_id <= 0) {
    die("ID de estudiante inválido.");
}

// Consulta para obtener las notas finales por materia (promedio de los lapsos)
$sql = "SELECT 
            anios_academicos.anio AS anio_academico,
            materias.nombre AS materia, 
            ROUND(AVG(notas.calificacion), 2) AS nota_final
        FROM notas
        JOIN materias ON notas.materia_id = materias.id
        JOIN anios_academicos ON materias.anio_academico_id = anios_academicos.id
        WHERE notas.estudiante_id = :estudiante_id
        GROUP BY anios_academicos.anio, materias.nombre
        ORDER BY anios_academicos.anio ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['estudiante_id' => $estudiante_id]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular el promedio general solo con las notas finales
$totalNotas = 0;
$totalMaterias = 0;

foreach ($notas as $nota) {
    $totalNotas += $nota['nota_final'];
    $totalMaterias++;
}

$promedio = ($totalMaterias > 0) ? round($totalNotas / $totalMaterias, 2) : "No disponible";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas Académicas</title>
    <link rel="stylesheet" href="../styles/styles_inicio.css">
</head>
<style>
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
        .promedio {
            font-size: 1.5rem;
            font-weight: bold;
            margin-left: 75%;
            margin-top: -50px;
        }
</style>
<body>
<?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
    <header>
        <h1>Notas Académicas del Estudiante</h1>
        <button class="register-btn" onclick="window.location.href='ver_estudiantes.php';">Atrás</button>
        <h2 class="promedio">Promedio General: <?= $promedio; ?></h2>
    </header>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Año Académico</th>
                    <th>Materia</th>
                    <th>Nota Final</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($notas)): ?>
                    <?php foreach ($notas as $nota): ?>
                        <tr>
                            <td><?= htmlspecialchars($nota['anio_academico']); ?></td>
                            <td><?= htmlspecialchars($nota['materia']); ?></td>
                            <td><?= htmlspecialchars($nota['nota_final']); ?></td>
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
</body>
</html>
