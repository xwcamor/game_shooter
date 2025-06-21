<?php
// Desactivar reporte de errores en producción para evitar que arruinen el JSON
// error_reporting(E_ALL); // Mantén esto solo para depuración
// ini_set('display_errors', 1); // Mantén esto solo para depuración

// Es importante que no haya absolutamente nada antes de esta etiqueta de apertura <?php

require_once 'conexion.php'; // Incluye tu archivo de conexión

// Asegúrate de que $conn es una instancia de mysqli válida y que la conexión es exitosa
if ($conn->connect_error) {
    // Si hay un error de conexión, envía un JSON de error
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $conn->connect_error]);
    exit();
}

// Consulta para obtener los mejores puntajes
// ¡IMPORTANTE! Verifica que las columnas 'nombre_usuario' y 'puntaje' existan en tu tabla 'puntajes'.
// Si la tabla 'puntajes' no tiene 'nombre_usuario', o se llama diferente (ej. 'username'),
// aquí es donde debes corregirlo.
$sql = "SELECT nombre_usuario, puntaje FROM puntajes ORDER BY puntaje DESC, fecha_puntaje DESC LIMIT 10";
$result = $conn->query($sql);

$puntajes = array();

if ($result) { // Verificar si la consulta fue exitosa
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $puntajes[] = $row;
        }
    }
} else {
    // Si la consulta falló (ej. columna no existe), registra el error y envía un JSON de error
    error_log("Error en la consulta SQL para leaderboard: " . $conn->error); // Registra en el log de errores de PHP
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta de puntajes: ' . $conn->error]);
    $conn->close();
    exit();
}

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');
// Enviar los puntajes como JSON
echo json_encode($puntajes);

$conn->close();

// Es crucial NO CERRAR la etiqueta PHP con ?> cuando el archivo solo sirve datos (como JSON)
// para evitar espacios en blanco o saltos de línea accidentales después del JSON.