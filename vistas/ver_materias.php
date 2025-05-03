<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}
require_once '../conn/db.php'; // Asegúrate de que la ruta sea correcta

// Consulta para obtener las materias y los años académicos
$sql = "SELECT materias.id AS id, materias.nombre AS materia, CONCAT(anios_academicos.anio_inicio, ' - ', anios_academicos.anio_fin) AS anio_academico 
        FROM materias 
        JOIN anios_academicos ON materias.anio_academico_id = anios_academicos.id";
$stmt = $pdo->query($sql);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles_inicio.css">
    <title>Ver Materias</title>
    <style>
        /* Contenedor del formulario emergente */
        .new-materia-container {
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

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
            to {
                opacity: 0;
                transform: translate(-50%, -40%);
            }
        }

        .new-materia-container form {
            display: flex;
            flex-direction: column;
        }

        .new-materia-container input,
        .new-materia-container select,
        .new-materia-container button {
            margin: 10px 0;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .new-materia-container button {
            background: #4caf50;
            color: #fff;
            cursor: pointer;
        }

        .new-materia-container button:hover {
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
            <h1>Materias</h1>
            <button class="register-btn" onclick="showForm()">Registrar materia nueva</button>
        </header>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Año Académico</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($materias)): ?>
                        <?php foreach ($materias as $materia): ?>
                            <tr>
                                <td><?= htmlspecialchars($materia['materia']); ?></td>
                                <td><?= htmlspecialchars($materia['anio_academico']); ?></td>
                                <td>
                                    <a href="editar_materia.php?id=<?= htmlspecialchars($materia['id']); ?>" class="action-btn">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No hay materias registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function showForm() {
            const container = document.getElementById('new-materia-container');
            const overlay = document.querySelector('.overlay');
            container.style.display = 'block';
            overlay.style.display = 'block';
            container.style.animation = 'fadeIn 0.3s ease';
        }

        function hideForm() {
            const container = document.getElementById('new-materia-container');
            const overlay = document.querySelector('.overlay');
            container.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                container.style.display = 'none';
                overlay.style.display = 'none';
            }, 300);
        }
    </script>

    <div class="overlay" onclick="hideForm()"></div>

    <div class="new-materia-container" id="new-materia-container">
        <button class="close-btn" onclick="hideForm()">×</button>
        <h2>Registrar Nueva Materia</h2>
        <form id="materiaForm" action="../funcion/registrar_materia.php" method="post">
            <label for="nombre">Nombre de la materia:</label>
            <input type="text" id="nombre" name="nombre" required>
            
            <label for="anio_academico">Año Académico:</label>
            <select id="anio_academico" name="anio_academico" required>
                <option value="">Seleccione un año académico</option>
                <?php
                // Consulta para obtener los años académicos
                $sqlAnios = "SELECT id, CONCAT(anio_inicio, ' - ', anio_fin) AS anio_academico FROM anios_academicos";
                $stmtAnios = $pdo->query($sqlAnios);
                $aniosAcademicos = $stmtAnios->fetchAll(PDO::FETCH_ASSOC);

                // Generar las opciones del select
                foreach ($aniosAcademicos as $anio) {
                    echo '<option value="' . htmlspecialchars($anio['id']) . '">' . htmlspecialchars($anio['anio_academico']) . '</option>';
                }
                ?>
            </select>
            
            <button type="submit">Guardar</button>
        </form>
    </div>
        <script>
        document.getElementById("materiaForm").addEventListener("submit", function(event) {
            let submitButton = document.getElementById("submitButton");
            submitButton.disabled = true; // 🔥 Evita múltiples envíos
            submitButton.innerText = "Guardando...";
        });
    </script>
</body>
</html>
