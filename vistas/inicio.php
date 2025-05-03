<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirige al inicio de sesión si no hay sesión activa
    exit();
}

// Conectar a la base de datos
require_once '../conn/db.php';

// Obtener el nombre del administrador
$username = $_SESSION['username'];
$query = "SELECT nombre FROM administradores WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$adminName = $result['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="stylesheet" href="../styles/styles_inicio.css">
</head>
<style>
        /* Estilo para el footer fijo en la parte inferior */
        .cc-footer {
            background-color: #111111;
            color: #ffffff;
            text-align: center;
            padding: 15px 0;
            font-family: Arial, sans-serif;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }
        .cc-license {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }
        .cc-license img {
            height: 16px;
            margin-left: 3px;
        }
    </style>
<body>
    <?php include '../sidebar/sidebar.php'; ?>

    <div id="main-content">
        <header>
            <h1>Bienvenido al Sistema de Gestión Académica</h1>
        </header>

        <section class="dashboard">
            <div class="card">
                <h3>📘 Estudiantes</h3>
                <p>Consulta y administra la lista de estudiantes inscritos.</p>
            </div>
            <div class="card">
                <h3>👨‍👩‍👧‍👦 Representantes</h3>
                <p>Gestiona los datos de los representantes legales.</p>
            </div>
            <div class="card">
                <h3>📅 Año Académico</h3>
                <p>Configura los años académicos disponibles.</p>
            </div>
            <div class="card">
                <h3>📚 Materias</h3>
                <p>Administra las asignaturas del sistema.</p>
            </div>
        </section>
        <img src="../img/Logo Virginia Gil.png" alt="Logo Virginia Gil" style="width: 25%; height: auto; margin-left: 580px; margin-top: 50px;">
    </div>
    <footer class="cc-footer">
        <div class="cc-license">
            <span>This work is licensed under</span>
            <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1" 
               target="_blank" 
               rel="license noopener noreferrer"
               style="color: white; text-decoration: underline;">
                CC BY-NC-SA 4.0
            </a>
            <img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC">
            <img src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY">
            <img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC">
            <img src="https://mirrors.creativecommons.org/presskit/icons/sa.svg?ref=chooser-v1" alt="SA">
        </div>
    </footer>
</body>
</html>
