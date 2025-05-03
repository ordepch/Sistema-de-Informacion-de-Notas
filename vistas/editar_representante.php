<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Verificar si el id está presente en la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para obtener los detalles del representante
    $sql = "SELECT * FROM representantes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $representante = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra el representante
    if (!$representante) {
        die('Representante no encontrado.');
    }
} else {
    die('No se ha especificado un id válido.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $telefono_prefijo = $_POST['telefono-prefijo']; // Prefijo del teléfono
    $telefono = $_POST['telefono']; // Número de teléfono
    $direccion = $_POST['direccion'];


    // Actualizar el representante en la base de datos
    $sql = "UPDATE representantes 
            SET nombres = :nombres, apellidos = :apellidos, cedula = :cedula, telefono = :telefono, telefono_prefijo = :telefono_prefijo, direccion = :direccion 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':cedula' => $cedula,
        ':telefono' => $telefono, // Guardamos el número de teléfono completo (prefijo + número)
        ':telefono_prefijo' => $telefono_prefijo, // Guardamos el prefijo por separado
        ':direccion' => $direccion,
        ':id' => $id
    ]);

    // Mostrar mensaje emergente y redirigir
    echo "<script>
            alert('✅ ¡Representante actualizado exitosamente!');
            window.location.href = 'ver_representante.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Editar Representante</title>
    <style>
        /* Estilos básicos para el formulario */
        .edit-anio-container {
            width: 50%;
            margin: 40px auto;
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
                <h1>Editar Representante</h1>
                <button class="register-btn" onclick="window.location.href='ver_representante.php';">Atrás</button>
            </header>
            
        <div class="edit-anio-container">
                <form action="editar_representante.php?id=<?= $representante['id']; ?>" method="post">
                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($representante['nombres']); ?>" required oninput="validarSoloLetras(event)">

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($representante['apellidos']); ?>" required oninput="validarSoloLetras(event)">

                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($representante['cedula']); ?>" required oninput="validarCedula(event)">

                    <label for="telefono-prefijo">Prefijo Teléfono:</label>
                    <select id="telefono-prefijo" name="telefono-prefijo" required>
                        <option value="0412" <?= $representante['telefono_prefijo'] == '0412' ? 'selected' : ''; ?>>0412</option>
                        <option value="0414" <?= $representante['telefono_prefijo'] == '0414' ? 'selected' : ''; ?>>0414</option>
                        <option value="0416" <?= $representante['telefono_prefijo'] == '0416' ? 'selected' : ''; ?>>0416</option>
                        <option value="0424" <?= $representante['telefono_prefijo'] == '0424' ? 'selected' : ''; ?>>0424</option>
                        <option value="0426" <?= $representante['telefono_prefijo'] == '0426' ? 'selected' : ''; ?>>0426</option>
                    </select>

                    <label for="telefono">Teléfono:</label>
                    <!-- Mostrar solo el número sin el prefijo -->
                    <input type="text" id="telefono" name="telefono" value="<?= substr($representante['telefono'], 0); ?>" required maxlength="7" oninput="validarTelefono()">

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($representante['direccion']); ?>" required>

                    <button type="submit">Actualizar</button>
                </form>
            </div>
        </main>
    </div>
</body>

<script>
    // Función para validar que solo se ingresen letras en Nombres y Apellidos
    function validarSoloLetras(event) {
        let valor = event.target.value;
        valor = valor.replace(/[^a-zA-Z\s]/g, ''); // Eliminar caracteres no permitidos (números y signos)
        event.target.value = valor;
    }

    // Validación para la cédula (solo números del 0-9 y longitud entre 7 y 8)
    function validarCedula(event) {
        let cedula = event.target.value;
        cedula = cedula.replace(/[^0-9]/g, ''); // Eliminar cualquier cosa que no sea un número
        if (cedula.length > 8) {
            cedula = cedula.slice(0, 8); // Limitar a 8 dígitos si es mayor
        }
        event.target.value = cedula; // Asigna el valor actualizado
    }

    // Validación para el teléfono (solo números y longitud de 7 dígitos)
    function validarTelefono(event) {
        let telefono = event.target.value;
        telefono = telefono.replace(/[^0-9]/g, ''); // Solo números permitidos

        if (telefono.length > 7) {
            telefono = telefono.slice(0, 7); // Limitar a 7 dígitos
        }

        event.target.value = telefono; // Actualiza el campo
    }

    // Validación al enviar el formulario
    document.querySelector('form').addEventListener('submit', function(event) {
        let nombres = document.getElementById('nombres').value;
        let apellidos = document.getElementById('apellidos').value;
        let cedula = document.getElementById('cedula').value;
        let telefono = document.getElementById('telefono').value;
        let direccion = document.getElementById('direccion').value;

        if (!nombres || !apellidos || !cedula || !telefono || !direccion) {
            alert("⚠️ Todos los campos son obligatorios.");
            event.preventDefault();
        }
    });
</script>

</html>
