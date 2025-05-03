<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Consulta para obtener los representantes
$sql = "SELECT id, nombres, apellidos, cedula, telefono, direccion, telefono_prefijo FROM representantes";
$stmt = $pdo->query($sql);
$representantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Ver Representantes</title>
    <style>
        /* Estilos para los formularios */
        .new-representante-container {
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

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #4caf50;
            color: white;
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

        .action-btn.disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }

        /* Estilos para el formulario */
        .new-representante-container form {
            display: flex;
            flex-direction: column;
        }

        .new-representante-container label {
            margin-bottom: 5px;
        }

        .new-representante-container input,
        .new-representante-container button,
        .new-representante-container select {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .new-representante-container button {
            background: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        .new-representante-container button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
        <header>
            <h1>Representantes</h1>
            <button class="register-btn" onclick="showForm()">Registrar representante nuevo</button>
        </header>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($representantes)): ?>
                        <?php foreach ($representantes as $representante): ?>
                            <tr>
                                <td><?= htmlspecialchars($representante['nombres']); ?></td>
                                <td><?= htmlspecialchars($representante['apellidos']); ?></td>
                                <td><?= htmlspecialchars($representante['cedula']); ?></td>
                                <td><?= htmlspecialchars($representante['telefono_prefijo']) . " " . htmlspecialchars($representante['telefono']); ?></td>
                                <td><?= htmlspecialchars($representante['direccion']); ?></td>
                                <td>
                                    <a href="editar_representante.php?id=<?= $representante['id']; ?>" class="action-btn">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No hay representantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="overlay" onclick="hideForm()"></div>

    <div class="new-representante-container" id="new-representante-container">
        <button class="close-btn" onclick="hideForm()">×</button>
        <h2 class="title2">Registrar Nuevo Representante</h2>
        <form action="../funcion/registrar_representante.php" method="post">
            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" required oninput="validarSoloLetras(event)">

            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required oninput="validarSoloLetras(event)">

            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required oninput="validarCedula(event)">

            <label for="telefono-prefijo">Prefijo Teléfono:</label>
            <select id="telefono-prefijo" name="telefono-prefijo" required>
                <option value="0412">0412</option>
                <option value="0414">0414</option>
                <option value="0416">0416</option>
                <option value="0424">0424</option>
                <option value="0426">0426</option>
            </select>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required maxlength="7" oninput="validarTelefono()">

            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" required>

            <button type="submit">Guardar</button>
        </form>
    </div>

    <script>
        // Función para unir el prefijo con el número de teléfono
        function validarTelefono() {
            let prefijo = document.getElementById('telefono-prefijo').value;
            let telefono = document.getElementById('telefono').value;

            // Concatenar el prefijo con los 7 dígitos del teléfono
            let telefonoCompleto = prefijo + telefono;

            // Validar que el teléfono tenga 11 dígitos
            if (telefonoCompleto.length > 11) {
                telefonoCompleto = telefonoCompleto.slice(0, 11); // Limitar a 11 dígitos
            }

            // Actualizar el valor del teléfono concatenado
            document.getElementById('telefono').value = telefonoCompleto.slice(4); // Eliminar el prefijo de la vista

            // Retornar el teléfono completo para el envío (esto se enviará al backend)
            document.getElementById('telefono').setAttribute('value', telefonoCompleto);
        }
    </script>

    <script>
        function showForm() {
            const container = document.getElementById('new-representante-container');
            const overlay = document.querySelector('.overlay');
            container.style.display = 'block';
            overlay.style.display = 'block';
            container.style.animation = 'fadeIn 0.3s ease';
        }

        function hideForm() {
            const container = document.getElementById('new-representante-container');
            const overlay = document.querySelector('.overlay');
            container.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                container.style.display = 'none';
                overlay.style.display = 'none';
            }, 300);
        }

        // Función para validar que solo se ingresen letras
        function validarSoloLetras(event) {
            let valor = event.target.value;
            valor = valor.replace(/[^a-zA-Z\s]/g, ''); // Eliminar caracteres no permitidos
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

        // Función para unir el prefijo con el número de teléfono
        function validarTelefono() {
            let prefijo = document.getElementById('telefono-prefijo').value;
            let telefono = document.getElementById('telefono').value;

            // Concatenar el prefijo con los 7 dígitos del teléfono
            let telefonoCompleto = prefijo + telefono;

            // Validar que el teléfono tenga 11 dígitos
            if (telefonoCompleto.length > 11) {
                telefonoCompleto = telefonoCompleto.slice(0, 11); // Limitar a 11 dígitos
            }

            // Actualizar el valor del teléfono concatenado
            document.getElementById('telefono').value = telefonoCompleto.slice(4); // Eliminar el prefijo de la vista

            // Retornar el teléfono completo para el envío
            document.getElementById('telefono').setAttribute('value', telefonoCompleto);
        }
    </script>
</body>
</html>
