<?php
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../conn/db.php'; // Asegúrate de que la conexión está cargada

    // Validar y sanitizar los datos del formulario
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $anio_academico_id = filter_input(INPUT_POST, 'anio_academico', FILTER_VALIDATE_INT);

    if (!empty($nombre) && $anio_academico_id) {
        try {
            // Normalizar el nombre eliminando espacios y convirtiéndolo a minúsculas
            $nombre = strtolower(trim($nombre));

            // Verificar si la materia ya existe en ese año académico
            $sql_check = "SELECT COUNT(*) FROM materias WHERE LOWER(TRIM(nombre)) = :nombre AND anio_academico_id = :anio_academico_id";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute(['nombre' => $nombre, 'anio_academico_id' => $anio_academico_id]);
            $exists = $stmt_check->fetchColumn();

            if ($exists > 0) {
                echo "<script>
                    console.log('⚠️ Materia duplicada detectada.');
                    alert('❌ Esta materia ya existe para ese año académico.');
                    window.history.back();
                </script>";
                exit(); // 🔥 Evita cualquier ejecución adicional
            }

            // Insertar la nueva materia
            $sql = "INSERT INTO materias (nombre, anio_academico_id) VALUES (:nombre, :anio_academico_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $_POST['nombre'], // Usamos el nombre original ingresado
                ':anio_academico_id' => $anio_academico_id
            ]);

            echo "<script>
                console.log('✅ Materia registrada correctamente.');
                alert('✅ Materia registrada exitosamente.');
                window.location.href = '../vistas/ver_materias.php'; // 🔥 Recarga sin reenviar el formulario
            </script>";
            exit(); // 🔥 Asegura que no se ejecuten más alertas
        } catch (PDOException $e) {
            echo "<script>
                console.log('❌ Error en la base de datos: " . addslashes($e->getMessage()) . "');
                alert('⚠️ Error en la base de datos. Inténtalo de nuevo.');
                window.history.back();
            </script>";
            exit();
        }
    } else {
        echo "<script>
            console.log('❌ Error en la validación del formulario.');
            alert('⚠️ Error: Datos inválidos. Por favor, complete correctamente el formulario.');
            window.history.back();
        </script>";
        exit();
    }
} else {
    echo "<script>
        console.log('⚠️ Acceso no permitido.');
        alert('⚠️ Acceso no permitido.');
        window.location.href = '../vistas/ver_materias.php';
    </script>";
    exit();
}
?>
