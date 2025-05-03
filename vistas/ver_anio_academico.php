<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Consulta para obtener los años académicos
$sql = "SELECT id, CONCAT(anio_inicio, ' - ', anio_fin) AS anio_academico, lapsos, anio, seccion 
        FROM anios_academicos";
$stmt = $pdo->query($sql);
$aniosAcademicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Ver Años Académicos</title>
    <style>
        /* Contenedor del formulario emergente */
        .new-anio-container {
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

        .edit-btn {
            background: #2196f3;
            color: #fff;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .edit-btn:hover {
            background: #1e88e5;
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

        .new-anio-container form {
            display: flex;
            flex-direction: column;
        }

        .new-anio-container input,
        .new-anio-container select,
        .new-anio-container button {
            margin: 10px 0;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .new-anio-container button {
            background: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        .new-anio-container button:hover {
            background: #45a049;
        }
        
    </style>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>
    <main id="main-content">
        <header>
            <h1>Años Académicos</h1>
            <button class="register-btn" onclick="showForm()">Registrar nuevo año académico</button>
        </header>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Año Académico</th>
                        <th>Año</th>
                        <th>Lapso</th>
                        <th>Sección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($aniosAcademicos)): ?>
                        <?php foreach ($aniosAcademicos as $anio): ?>
                            <tr>
                                <td><?= htmlspecialchars($anio['anio_academico']); ?></td>
                                <td><?= htmlspecialchars($anio['anio']); ?></td>
                                <td><?= htmlspecialchars($anio['lapsos']); ?></td>
                                <td><?= htmlspecialchars($anio['seccion']); ?></td>
                                <td>
                                    <form action="editar_anio_academico.php" method="get" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($anio['id']); ?>">
                                        <button type="submit" class="edit-btn">Editar</button>
                                    </form>
                                    <form action="ver_matricula.php" method="get" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($anio['id']); ?>">
                                        <input type="hidden" name="anio" value="<?= htmlspecialchars($anio['anio']); ?>">
                                        <input type="hidden" name="lapsos" value="<?= htmlspecialchars($anio['lapsos']); ?>">
                                        <input type="hidden" name="seccion" value="<?= htmlspecialchars($anio['seccion']); ?>"> <!-- Puede que necesites agregar este campo si es relevante -->
                                        <button type="submit" class="edit-btn">Ver matrícula</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay años académicos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="overlay" onclick="hideForm()"></div>

    <div class="new-anio-container" id="new-anio-container">
        <button class="close-btn" onclick="hideForm()">&times;</button>
        <h2>Registrar Nuevo Año Académico</h2>
        <form action="../funcion/registrar_anio_academico.php" method="post">
    <label for="anio_inicio">Año de Inicio:</label>
    <input type="number" id="anio_inicio" name="anio_inicio" required min="2005" max="<?= date('Y'); ?>" step="1">

    <label for="anio_fin">Año de fin:</label>
    <input type="number" id="anio_fin" name="anio_fin" readonly>

    <label for="anio">Año:</label>
    <select id="anio" name="anio" required>
        <option value="">Seleccione un año</option>
        <option value="1er">1er</option>
        <option value="2do">2do</option>
        <option value="3er">3er</option>
        <option value="4to">4to</option>
        <option value="5to">5to</option>
    </select>

    <label for="lapsos">Lapso:</label>
    <select id="lapsos" name="lapsos" required>
        <option value="">Seleccione un lapso</option>
        <option value="1">Lapso 1</option>
        <option value="2">Lapso 2</option>
        <option value="3">Lapso 3</option>
    </select>

    <label for="seccion">Sección:</label>
    <select id="seccion" name="seccion" required>
        <option value="">Seleccione una sección</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
    </select>

    <button type="submit">Guardar</button>
</form>

    </div>

    <script>
        function showForm() {
            const container = document.getElementById('new-anio-container');
            const overlay = document.querySelector('.overlay');
            container.style.display = 'block';
            overlay.style.display = 'block';
        }

        function hideForm() {
            const container = document.getElementById('new-anio-container');
            const overlay = document.querySelector('.overlay');
            container.style.display = 'none';
            overlay.style.display = 'none';
        }

        // Actualizar automáticamente el año de fin
        document.getElementById('anio_inicio').addEventListener('input', function () {
            const anioInicio = parseInt(this.value, 10);
            const currentYear = new Date().getFullYear();

            if (anioInicio >= 2005 && anioInicio <= currentYear) {
                document.getElementById('anio_fin').value = anioInicio + 1;
            } else {
                document.getElementById('anio_fin').value = '';
            }
        });
    </script>
</body>
</html>
