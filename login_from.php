<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css"> 
    </head>
<body class="fondo-logeo">
    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php
        // Mostrar mensaje de éxito si viene de registro.php
        if (isset($_GET['registro_exitoso']) && $_GET['registro_exitoso'] == 'true') {
            echo '<p class="success-message">¡Registro exitoso! Ya puedes iniciar sesión.</p>';
        }

        // Mostrar mensaje de error si viene de login.php
        if (isset($_GET['error'])) {
            $error_message = '';
            switch ($_GET['error']) {
                case 'empty_fields':
                    $error_message = 'Por favor, rellena todos los campos.';
                    break;
                case 'incorrect_credentials':
                    $error_message = 'Usuario o contraseña incorrectos.';
                    break;
                case 'db_error':
                    $error_message = 'Error en la base de datos. Inténtalo de nuevo.';
                    break;
                default:
                    $error_message = 'Ha ocurrido un error inesperado.';
                    break;
            }
            // Muestra el mensaje de error
            echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>';
        }
        ?>

        <form action="login.php" method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="clave">Contraseña:</label>
            <input type="password" id="clave" name="clave" required>

            <button type="submit">Ingresar</button>
        </form>
        <p>¿No tienes cuenta? <a href="registro_from.php">Regístrate aquí</a></p>
    </div>
</body>
</html>