<?php
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verifica si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos del formulario y límpialos
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $cedula = trim($_POST['cedula']);
    $telefono_prefijo = trim($_POST['telefono-prefijo']); // Prefijo del teléfono
    $telefono = trim($_POST['telefono']); // Número de teléfono
    $direccion = trim($_POST['direccion']);

    // Validar que los campos no estén vacíos
    if (!empty($nombres) && !empty($apellidos) && !empty($cedula) && !empty($telefono) && !empty($telefono_prefijo) && !empty($direccion)) {
        try {
            // Verificar si la cédula o el teléfono ya están registrados
            $sqlCheck = "SELECT COUNT(*) FROM representantes WHERE cedula = :cedula OR telefono = :telefono";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([ 
                ':cedula' => $cedula,
                ':telefono' => $telefono
            ]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists > 0) {
                // Si ya existe un representante con la cédula o teléfono, mostrar alerta
                echo "<script>
                    alert('❌ Ya existe un representante con esta cédula o teléfono.');
                    history.back();
                </script>";
                exit();
            }

            // Si no existe, proceder con la inserción
            $sql = "INSERT INTO representantes (nombres, apellidos, cedula, telefono, telefono_prefijo, direccion) 
                    VALUES (:nombres, :apellidos, :cedula, :telefono, :telefono_prefijo, :direccion)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([ 
                ':nombres' => $nombres,
                ':apellidos' => $apellidos,
                ':cedula' => $cedula,
                ':telefono' => $telefono, // Guardamos el teléfono
                ':telefono_prefijo' => $telefono_prefijo, // Guardamos el prefijo
                ':direccion' => $direccion,
            ]);

            // Mostrar mensaje de éxito y redirigir
            echo "<script>
                alert('✅ Representante registrado exitosamente.');
                window.location.href = '../vistas/ver_representante.php';
            </script>";
            exit();
        } catch (PDOException $e) {
            // Manejo de errores
            echo "<script>
                alert('⚠️ Error en la base de datos. Inténtalo de nuevo.');
                history.back();
            </script>";
            exit();
        }
    } else {
        // Si algún campo está vacío, mostrar alerta
        echo "<script>
            alert('⚠️ Todos los campos son obligatorios.');
            history.back();
        </script>";
        exit();
    }
} else {
    // Si no es una solicitud POST, redirigir
    header('Location: ../vistas/ver_representante.php');
    exit();
}
?>
