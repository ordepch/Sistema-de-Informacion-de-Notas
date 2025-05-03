<?php
session_start();

require_once 'conn/db.php';

// Redirigir solo si ya hay sesión iniciada y no se está enviando un formulario
if (isset($_SESSION['username']) && !isset($_POST['form_type'])) {
    header("Location: vistas/inicio.php");
    exit;
}

$error = '';
$securityQuestion = '';
$securityQuestion2 = '';
$usernameExists = false;
$secondAttempt = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'login') {
        // LOGIN NORMAL
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT password_hash FROM administradores WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && hash("sha256", $password) === $result['password_hash']) {
            $_SESSION['username'] = $username;
            echo "<script>
                    alert('Inicio de sesión con éxito');
                    window.location.href = 'vistas/inicio.php';
                  </script>";
            exit();
        } else {
            echo "<script>
                alert('Usuario o contraseña incorrectos.');
                window.location.href = window.location.href + '?nocache=' + new Date().getTime();
            </script>";
        }

    } elseif ($formType === 'recovery') {
        // RECUPERACIÓN DE CONTRASEÑA - VERIFICAR USUARIO
        $username = $_POST['username'];
        $query = "SELECT pregunta_seguridad_1, pregunta_seguridad_2 FROM administradores WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $securityQuestion = $result['pregunta_seguridad_1'];
            $securityQuestion2 = $result['pregunta_seguridad_2'];
            $_SESSION['recovery_username'] = $username;
            $usernameExists = true;
        } else {
            echo "<script>alert('Usuario no encontrado.');</script>";
        }

    } elseif ($formType === 'security_question_1') {
        // PRIMERA PREGUNTA DE SEGURIDAD
        $username = $_SESSION['recovery_username'] ?? '';
        $answer = hash("sha256", $_POST['answer']);
        $query = "SELECT respuesta_seguridad_1_hash FROM administradores WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $answer === $result['respuesta_seguridad_1_hash']) {
            $_SESSION['username_reset'] = $username;
            header("Location: vistas/reset_password.php");
            exit();
        } else {
            $secondAttempt = true;
            echo "<script>alert('Respuesta incorrecta. Intenta con la segunda pregunta.');</script>";
        }

    } elseif ($formType === 'security_question_2') {
        // SEGUNDA PREGUNTA DE SEGURIDAD
        $username = $_SESSION['recovery_username'] ?? '';
        $answer = hash("sha256", $_POST['answer_2']);
        $query = "SELECT respuesta_seguridad_2_hash FROM administradores WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $answer === $result['respuesta_seguridad_2_hash']) {
            $_SESSION['username_reset'] = $username;
            header("Location: vistas/reset_password.php");
            exit();
        } else {
            echo "<script>alert('Respuesta incorrecta. Intenta de nuevo.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="img/logo-virginia-gil.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="styles/styles_login.css">
    <style>
        .hidden { display: none; }
        .fade-in { animation: fadeIn 0.5s forwards; }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Formulario de inicio de sesión -->
        <div id="login-form" class="form-section">
            <h2>Iniciar Sesión</h2>
            <form method="POST">
                <input type="hidden" name="form_type" value="login">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Usuario" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <br>
                    <a href="#" id="forgot-password-link">Olvidé mi contraseña</a>
                </div>
                <button type="submit">Iniciar Sesión</button>
            </form>
        </div>

        <!-- Formulario de recuperación de contraseña -->
        <div id="recovery-form" class="form-section hidden">
            <h2>Recuperar Contraseña</h2>
            <form method="POST">
                <input type="hidden" name="form_type" value="recovery">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Usuario" required>
                </div>
                <button type="submit">Verificar Usuario</button>
                <br><br>
                <button type="button" id="back-to-login-btn">Ir Atrás</button>
            </form>
        </div>

        <!-- Formulario de preguntas de seguridad -->
        <div id="security-question-form" class="form-section <?= $usernameExists || $secondAttempt ? '' : 'hidden' ?>">
            <h2>Pregunta de Seguridad</h2>
            <form id="first-question-form" method="POST" class="<?= $secondAttempt ? 'hidden' : '' ?>">
                <input type="hidden" name="form_type" value="security_question_1">
                <p id="security-question"><?= htmlspecialchars($securityQuestion) ?></p>
                <div class="input-group">
                    <input type="text" name="answer" placeholder="Respuesta" required>
                </div>
                <button type="submit">Verificar Respuesta</button>
                <br><br>
                <button type="button" id="second-question-btn">Intentar con 2da pregunta</button>
            </form>

            <form id="second-question-form" method="POST" class="<?= !$secondAttempt ? 'hidden' : '' ?>">
                <input type="hidden" name="form_type" value="security_question_2">
                <p id="security-question-2"><?= htmlspecialchars($securityQuestion2) ?></p>
                <div class="input-group">
                    <input type="text" name="answer_2" placeholder="Respuesta 2" required>
                </div>
                <button type="submit">Verificar Respuesta 2</button>
                <br><br>
                <button type="button" id="first-question-btn">Intentar con 1ra pregunta</button>
            </form>
        </div>

        <div class="welcome-section">
            <h2>¡Bienvenido!</h2>
            <p>Por favor, ingresa tus credenciales para continuar.</p><br>
        </div>
    </div>

    <script>
        const forgotPasswordLink = document.getElementById('forgot-password-link');
        const loginForm = document.getElementById('login-form');
        const recoveryForm = document.getElementById('recovery-form');
        const securityQuestionForm = document.getElementById('security-question-form');
        const firstQuestionForm = document.getElementById('first-question-form');
        const secondQuestionForm = document.getElementById('second-question-form');
        const secondQuestionBtn = document.getElementById('second-question-btn');
        const firstQuestionBtn = document.getElementById('first-question-btn');
        const backToLoginBtn = document.getElementById('back-to-login-btn');

        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.classList.add('hidden');
            recoveryForm.classList.remove('hidden');
        });

        backToLoginBtn.addEventListener('click', () => {
            recoveryForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        });

        secondQuestionBtn.addEventListener('click', () => {
            firstQuestionForm.classList.add('hidden');
            secondQuestionForm.classList.remove('hidden');
        });

        firstQuestionBtn.addEventListener('click', () => {
            secondQuestionForm.classList.add('hidden');
            firstQuestionForm.classList.remove('hidden');
        });
    </script>
</body>
</html>
