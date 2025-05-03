<?php
require_once '../conn/db.php'; // Asegúrate de que la conexión a la base de datos sea correcta

// Verifica si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén y valida los datos del formulario
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $edad = trim($_POST['edad'] ?? '');
    $parentesco = trim($_POST['parentesco'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $anio_academico = trim($_POST['anio_academico'] ?? '');
    $representante = trim($_POST['representante'] ?? '');

    // Valida que los campos requeridos no estén vacíos
    if (
        empty($nombres) || empty($apellidos) || empty($cedula) ||
        empty($estado) || empty($genero) || empty($fecha_nacimiento) ||
        empty($edad) || empty($anio_academico) || empty($representante)
    ) {
        die('Por favor, completa todos los campos requeridos.');
    }

    try {
        // Inserta los datos en la base de datos
        $sql = "INSERT INTO estudiantes (
                    nombres, apellidos, cedula, estado, genero, 
                    fecha_nacimiento, edad, parentesco_con_representante, 
                    direccion, anio_academico_id, representante_id
                ) VALUES (
                    :nombres, :apellidos, :cedula, :estado, :genero, 
                    :fecha_nacimiento, :edad, :parentesco, 
                    :direccion, :anio_academico, :representante
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':cedula' => $cedula,
            ':estado' => $estado,
            ':genero' => $genero,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':edad' => $edad,
            ':parentesco' => $parentesco,
            ':direccion' => $direccion,
            ':anio_academico' => $anio_academico,
            ':representante' => $representante,
        ]);

        // Redirige a la página principal con un mensaje de éxito, pero con un retraso para mostrar el mensaje
        echo "<script>
                alert('¡Estudiante agregado exitosamente!');
                window.location.href = '../vistas/ver_estudiantes.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        // Manejo de errores en caso de problemas con la base de datos
        die('Error al registrar el estudiante: ' . $e->getMessage());
    }
} else {
    die('Método de solicitud no válido.');
}
?>
