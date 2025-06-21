<?php
// Habilitar reporte de errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Inicia la sesión. ¡Debe ser la primera línea ejecutada!

// Incluye el archivo de conexión. Asegúrate de que la ruta es correcta.
require_once 'conexion.php';

// Asegúrate de que la conexión a la base de datos ($conn) se estableció correctamente
if (!isset($conn) || $conn->connect_error) {
    // Si la conexión falla aquí, es un error crítico.
    error_log("login.php: La conexión a la base de datos NO está disponible.");
    header("Location: login_from.php?error=db_error_critical");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['usuario'] ?? ''); // Nombre del campo de usuario en el formulario
    $password = $_POST['clave'] ?? '';       // Nombre del campo de contraseña en el formulario

    // Validaciones básicas de campos vacíos
    if (empty($username) || empty($password)) {
        header("Location: login_from.php?error=empty_fields");
        exit();
    }

    try {
        // Preparar la consulta para buscar el usuario
        // Asegúrate de que 'username' y 'password' son los nombres CORRECTOS de las columnas en tu tabla 'usuarios'.
        // Si usaste 'nombre_de_usuario' o 'clave_hashed', debes cambiarlo aquí.
        $stmt = $conn->prepare("SELECT id, username, password FROM usuarios WHERE username = ?");

        if ($stmt === false) {
            // Este es un error en la preparación de la consulta SQL.
            // Puede deberse a un error de sintaxis en la consulta o tabla/columna inexistente.
            error_log("login.php: Error al preparar la consulta: " . $conn->error);
            header("Location: login_from.php?error=db_error_prepare&debug=" . urlencode($conn->error));
            exit(); // Salimos después de redirigir con el error
        }

        // Vincular parámetros
        $stmt->bind_param("s", $username);

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            // Este es un error al ejecutar la consulta (ej. problemas de permisos, etc.)
            error_log("login.php: Error al ejecutar la consulta: " . $stmt->error);
            header("Location: login_from.php?error=db_error_execute&debug=" . urlencode($stmt->error));
            exit();
        }

        $stmt->store_result(); // Almacenar el resultado para poder usar num_rows
        $stmt->bind_result($user_id, $db_username, $hashed_password); // Vincula los resultados a variables

        // Verificar si se encontró exactamente un usuario
        if ($stmt->num_rows === 1) {
            $stmt->fetch(); // Obtener los resultados
            // Verificar la contraseña hasheada
            if (password_verify($password, $hashed_password)) {
                // Contraseña correcta, iniciar sesión
                session_regenerate_id(true); // Genera un nuevo ID de sesión por seguridad
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $db_username;
                $_SESSION['loggedin'] = true;

                $stmt->close();
                $conn->close();
                header("Location: index.php"); // Redirige a la página principal del juego
                exit();
            } else {
                // Contraseña incorrecta
                error_log("login.php: Intento de login fallido para usuario: " . $username . " - Contraseña incorrecta.");
                header("Location: login_from.php?error=incorrect_credentials");
                exit();
            }
        } else {
            // Usuario no encontrado o (teóricamente) múltiples usuarios con el mismo nombre
            error_log("login.php: Intento de login fallido - Usuario no encontrado o duplicado: " . $username);
            header("Location: login_from.php?error=incorrect_credentials");
            exit();
        }

    } catch (Exception $e) {
        // Captura cualquier otra excepción no manejada anteriormente
        error_log("login.php: Excepción inesperada: " . $e->getMessage());
        header("Location: login_from.php?error=db_error_exception&debug=" . urlencode($e->getMessage()));
        exit();
    } finally {
        // Asegurarse de cerrar el statement y la conexión si están abiertos
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
    }
} else {
    // Si se accede a login.php directamente por GET, redirigir al formulario de login
    header("Location: login_from.php");
    exit();
}
?>