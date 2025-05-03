<?php
session_start();
require_once '../conn/db.php';

// Verificar que exista sesión de recuperación
if (!isset($_SESSION['username_reset'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username_reset'];
$mensajeJS = ''; // Mensaje para mostrar en alerta JS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_password'], $_POST['confirm_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) >= 8 && strlen($newPassword) <= 16) {
                // ⚠ GUARDAR CON SHA-256 (no BCRYPT)
                $passwordHash = hash("sha256", $newPassword);

                $query = "UPDATE administradores SET password_hash = ? WHERE username = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$passwordHash, $username]);

                unset($_SESSION['username_reset']);

                // Mostrar alerta y redirigir
                echo "<script>
                        alert('✅ Contraseña actualizada con éxito.');
                        window.location.href = '../index.php';
                      </script>";
                exit();
            } else {
                $mensajeJS = "❌ La contraseña debe tener entre 8 y 16 caracteres.";
            }
        } else {
            $mensajeJS = "❌ Las contraseñas no coinciden.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="../styles/styles_login.css">
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Restablecer Contraseña</h2>
            <form method="POST">
                <div class="input-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" name="new_password" id="new_password" required minlength="8" maxlength="16">
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" maxlength="16">
                    <a href="../index.php">Volver a Iniciar Sesión</a>
                </div>
                <button type="submit">Actualizar Contraseña</button>
            </form>
        </div>
        <div class="welcome-section">
            <h2>¡Bienvenido de nuevo!</h2>
            <p>Estamos aquí para ayudarte a restablecer tu contraseña y recuperar el acceso a tu cuenta.</p>
        </div>
    </div>

    <?php if (!empty($mensajeJS)): ?>
        <script>
            alert("<?= addslashes($mensajeJS) ?>");
        </script>
    <?php endif; ?>
</body>
</html>
