<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexion.php'; // Incluye el archivo de conexión

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['usuario'] ?? ''); // Cambiado de $usuario a $username, pero sigue usando $_POST['usuario']
    $password = $_POST['clave'] ?? ''; // Cambiado de $clave a $password, pero sigue usando $_POST['clave']
    $confirm_password = $_POST['confirmar_clave'] ?? ''; // Cambiado de $confirmar_clave a $confirm_password

    // 1. Validaciones básicas de campos
    if (empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: registro_from.php?error=empty_fields");
        exit();
    }

    // 2. Validar formato de usuario (usando el patrón del HTML)
    if (!preg_match("/^[A-Za-z0-9]{3,20}$/", $username)) { // Usando $username
        header("Location: registro_from.php?error=invalid_username");
        exit();
    }

    // 3. Validar longitud de contraseña
    if (strlen($password) < 6 || strlen( $password) > 20) { // Usando $password
        header("Location: registro_from.php?error=invalid_password");
        exit();
    }

    // 4. Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) { // Usando $password
        header("Location: registro_from.php?error=password_mismatch");
        exit();
    }

    // 5. Hashear la contraseña de forma segura
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Usando $password

    // 6. Verificar si el usuario ya existe
    // CAMBIO CLAVE: Usar 'username' en lugar de 'usuario'
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
    if ($stmt_check === false) {
        error_log("Error en la preparación de la consulta de verificación de usuario: " . $conn->error);
        header("Location: registro_from.php?error=db_error");
        exit();
    }
    $stmt_check->bind_param("s", $username); // Usando $username
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: registro_from.php?error=username_taken");
        exit();
    }
    $stmt_check->close();

    // 7. Insertar el nuevo usuario en la base de datos
    // CAMBIO CLAVE: Usar 'username' y 'password' en lugar de 'usuario' y 'clave'
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
    if ($stmt_insert === false) {
        error_log("Error en la preparación de la consulta de inserción: " . $conn->error);
        header("Location: registro_from.php?error=db_error");
        exit();
    }
    $stmt_insert->bind_param("ss", $username, $hashed_password); // Usando $username y $hashed_password

    if ($stmt_insert->execute()) {
        session_start();
        $_SESSION['usuario_id'] = $stmt_insert->insert_id;
        $_SESSION['username'] = $username; // Cambiado de 'usuario' a 'username' para la sesión
        $_SESSION['loggedin'] = true;

        $stmt_insert->close();
        $conn->close();
        header("Location: index.php"); // Redirige a la página principal del juego
        exit();
    } else {
        error_log("Error al ejecutar la inserción de usuario: " . $stmt_insert->error);
        $stmt_insert->close();
        $conn->close();
        header("Location: registro_from.php?error=db_error");
        exit();
    }

} else {
    // Si se accede a registro.php directamente por GET, redirigir al formulario
    header("Location: registro_from.php");
    exit();
}
?>