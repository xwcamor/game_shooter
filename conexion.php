<?php
// Configuración de la base de datos
$servername = "localhost"; // Generalmente "localhost" para XAMPP/WAMP
$username_db = "root";    // Usuario por defecto de MySQL en XAMPP/WAMP
$password_db = "";        // Contraseña por defecto de MySQL en XAMPP/WAMP (vacío)
$dbname = "juegos"; // ¡IMPORTANTE: Reemplaza esto con el nombre real de tu base de datos!

// Habilitar reporte de errores para depuración (QUITAR EN PRODUCCIÓN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Crear conexión
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Esto mostrará el error exacto de conexión
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Opcional: Establecer el conjunto de caracteres a utf8 para evitar problemas con tildes y ñ
$conn->set_charset("utf8");

// Si la conexión es exitosa, no se mostrará nada aquí
?>