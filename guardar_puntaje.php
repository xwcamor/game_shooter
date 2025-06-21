<?php
session_start(); // ¡MUY IMPORTANTE! Debe ser la primera línea.
error_reporting(E_ALL); // Habilitar reporte de errores
ini_set('display_errors', 1); // Mostrar errores en pantalla

require_once 'conexion.php'; // Incluye el archivo de conexión

header('Content-Type: application/json'); // Indicar que la respuesta será JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Verificar si el usuario ha iniciado sesión
    // Usamos 'loggedin' Y 'user_id' para una doble verificación
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado o sesión expirada.']);
        exit();
    }

    // Obtener datos del formulario
    $puntaje = filter_input(INPUT_POST, 'puntaje', FILTER_VALIDATE_INT);
    $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $nombre_usuario = filter_input(INPUT_POST, 'nombre_usuario', FILTER_SANITIZE_STRING);

    // Seguridad adicional: Asegurarse de que los datos recibidos del formulario
    // coincidan con los de la sesión para evitar falsificaciones.
    // Compara el user_id de la sesión con el user_id enviado en el formulario
    // y el username de la sesión con el nombre_usuario enviado.
    if ($usuario_id !== $_SESSION['user_id'] || $nombre_usuario !== $_SESSION['username']) {
        echo json_encode(['success' => false, 'message' => 'Datos de usuario inconsistentes con la sesión.']);
        exit();
    }

    // 2. Validar datos
    if ($puntaje === false || $puntaje < 0) {
        echo json_encode(['success' => false, 'message' => 'Puntaje inválido.']);
        exit();
    }
    if ($usuario_id === false || $usuario_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido.']);
        exit();
    }
    if (empty($nombre_usuario)) {
        echo json_encode(['success' => false, 'message' => 'Nombre de usuario no proporcionado.']);
        exit();
    }

    try {
        // 3. Preparar la consulta SQL para insertar el puntaje
        // Asegúrate de que los nombres de las columnas COINCIDAN EXACTAMENTE
        // con tu tabla 'puntajes' en phpMyAdmin.
        // Las columnas deberían ser 'usuario_id', 'puntaje', 'nombre_usuario'.
        $stmt = $conn->prepare("INSERT INTO puntajes (usuario_id, puntaje, nombre_usuario) VALUES (?, ?, ?)");
        
        if ($stmt === false) {
            error_log("Error en la preparación de guardar_puntaje: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
            exit();
        }

        // 4. Vincular parámetros y ejecutar
        $stmt->bind_param("iss", $usuario_id, $puntaje, $nombre_usuario); // i: int, s: string, s: string

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => '¡Puntaje guardado exitosamente!']);
        } else {
            error_log("Error al ejecutar la inserción de puntaje: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Error al guardar el puntaje: ' . $stmt->error]);
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Excepción en guardar_puntaje.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Excepción: ' . $e->getMessage()]);
    } finally {
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>