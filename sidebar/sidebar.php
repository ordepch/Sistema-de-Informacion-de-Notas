<div id="sidebar">
    <h2 class="sidebar-title">Menú Principal</h2>
    <ul class="sidebar-menu">
        <li><a href="inicio.php">🏠 Inicio</a></li>
        <li><a href="ver_estudiantes.php">📘 Ver Estudiantes</a></li>
        <li><a href="ver_representante.php">👨‍👩‍👧‍👦 Ver Representantes</a></li>
        <li><a href="ver_anio_academico.php">📅 Ver Año Académico</a></li>
        <li><a href="ver_materias.php">📚 Ver Materias</a></li>
        <li><a href="../sidebar/logout.php">🚪 Salir</a></li>
    </ul>
</div>

<style>
    #sidebar {
        width: 250px;
        background: #111;
        color: #fff;
        position: fixed;
        height: 100vh;
        padding: 15px 0;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar-title {
        text-align: center;
        font-size: 1.5rem;
        color: #00e6ff;
        text-shadow: 0 0 10px #00e6ff, 0 0 20px #007bff;
        animation: neon-border 2s infinite alternate;
    }

    @keyframes neon-border {
        from {
            text-shadow: 0 0 10px #00e6ff, 0 0 20px #007bff;
        }
        to {
            text-shadow: 0 0 20px #00e6ff, 0 0 40px #007bff;
        }
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }

    .sidebar-menu li {
        padding: 10px 20px;
    }

    .sidebar-menu a {
        text-decoration: none;
        color: #fff;
        font-size: 1rem;
        display: block;
        transition: background 0.3s, color 0.3s;
    }

    .sidebar-menu a:hover {
        background: #007bff;
        color: #fff;
        border-radius: 8px;
    }
</style>
