<?php
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $anio_inicio = filter_input(INPUT_POST, 'anio_inicio', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 2005, 'max_range' => date('Y')]
    ]);
    $anio_fin = filter_input(INPUT_POST, 'anio_fin', FILTER_VALIDATE_INT);
    $lapsos = filter_input(INPUT_POST, 'lapsos', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 3]
    ]);
    $anio = filter_input(INPUT_POST, 'anio', FILTER_SANITIZE_STRING);
    $seccion = filter_input(INPUT_POST, 'seccion', FILTER_SANITIZE_STRING);

    // Validar que todos los campos son válidos
    if ($anio_inicio && $anio_fin && $lapsos && in_array($anio, ['1er', '2do', '3er', '4to', '5to']) && !empty($seccion)) {
        try {
            // Verificar si ya existe un año académico con los mismos valores
            $sql_check = "SELECT COUNT(*) FROM anios_academicos 
                          WHERE anio_inicio = :anio_inicio 
                          AND anio_fin = :anio_fin 
                          AND lapsos = :lapsos 
                          AND anio = :anio 
                          AND seccion = :seccion";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([
                ':anio_inicio' => $anio_inicio,
                ':anio_fin' => $anio_fin,
                ':lapsos' => $lapsos,
                ':anio' => $anio,
                ':seccion' => $seccion
            ]);
            $exists = $stmt_check->fetchColumn();

            if ($exists > 0) {
                // Si el año académico ya existe, mostrar alerta en JavaScript
                echo "<script>
                    alert('❌ Este año académico ya existe con los mismos datos.');
                    history.back();
                </script>";
                exit();
            }

            // Insertar el nuevo año académico
            $sql = "INSERT INTO anios_academicos (anio_inicio, anio_fin, lapsos, anio, seccion)
                    VALUES (:anio_inicio, :anio_fin, :lapsos, :anio, :seccion)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':anio_inicio' => $anio_inicio,
                ':anio_fin' => $anio_fin,
                ':lapsos' => $lapsos,
                ':anio' => $anio,
                ':seccion' => $seccion
            ]);

            // Mostrar mensaje de éxito y recargar la página
            echo "<script>
                alert('✅ Año académico registrado exitosamente.');
                window.location.href = '../vistas/ver_anio_academico.php';
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
} else {
    echo "<script>
        alert('⚠️ Acceso no permitido.');
        window.location.href = '../vistas/ver_anio_academico.php';
    </script>";
    exit();
}
?>
