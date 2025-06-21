<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="style.css"> 
    </head>
<body class="fondo-logeo"> <div class="register-container">
        <h2>Crear Cuenta</h2>
        <?php
        // Mostrar mensaje de error si viene de registro.php
        if (isset($_GET['error'])) {
            $error_message = '';
            switch ($_GET['error']) {
                case 'empty_fields':
                    $error_message = 'Por favor completa todos los campos.';
                    break;
                case 'password_mismatch':
                    $error_message = 'Las contraseñas no coinciden.';
                    break;
                case 'username_taken':
                    $error_message = 'El nombre de usuario ya está en uso.';
                    break;
                case 'invalid_username':
                    $error_message = 'El usuario debe tener entre 3 y 20 caracteres y solo puede contener letras y números.';
                    break;
                case 'invalid_password':
                    $error_message = 'La contraseña debe tener entre 6 y 20 caracteres.';
                    break;
                case 'db_error':
                    $error_message = 'Error al registrar el usuario. Inténtalo de nuevo.';
                    break;
                default:
                    $error_message = 'Ocurrió un error inesperado.';
                    break;
            }
            echo '<p class="error-message" style="display: block;">' . htmlspecialchars($error_message) . '</p>';
        }
        ?>
        <form action="registro.php" method="POST">
            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" id="usuario" name="usuario" required minlength="3" maxlength="20" pattern="[A-Za-z0-9]+" title="Solo letras y números (3-20 caracteres)">

            <label for="clave">Contraseña:</label>
            <input type="password" id="clave" name="clave" required minlength="6" maxlength="20">

            <label for="confirmar_clave">Confirmar Contraseña:</label>
            <input type="password" id="confirmar_clave" name="confirmar_clave" required minlength="6" maxlength="20">

            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login_from.php">Inicia Sesión aquí</a></p>
    </div>
</body>
</html>