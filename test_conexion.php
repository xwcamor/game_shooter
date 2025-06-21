<?php
// Habilitar reporte de errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
$servername = "localhost"; // Generalmente "localhost" para XAMPP/WAMP
$username_db = "root";    // Usuario por defecto de MySQL en XAMPP/WAMP
$password_db = "";        // Contraseña por defecto de MySQL en XAMPP/WAMP (vacío)
$dbname = "juegos"; // ¡MUY IMPORTANTE: Cambia esto al nombre exacto de tu base de datos!
                                        // Por ejemplo, si se llama 'juegobd', pon $dbname = "juegobd";

echo "<h3>Intentando conectar a la base de datos...</h3>";
echo "<p>Servidor: $servername</p>";
echo "<p>Usuario DB: $username_db</p>";
echo "<p>Base de Datos: $dbname</p>";

// Crear conexión
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Si la conexión falla, esto detendrá la ejecución y mostrará el error exacto
    die("<h3>Error CRÍTICO de Conexión a la Base de Datos:</h3>" .
        "<p><strong>Mensaje de MySQL:</strong> " . $conn->connect_error . "</p>" .
        "<p><strong>Código de Error:</strong> " . $conn->connect_errno . "</p>" .
        "<p>Por favor, revisa:</p>" .
        "<ul>" .
        "<li>Que tu servidor MySQL (en XAMPP/WAMP) esté **iniciado** y funcionando.</li>" .
        "<li>Que el `\$dbname` (`" . htmlspecialchars($dbname) . "`) en `test_conexion.php` sea el **nombre exacto** de tu base de datos en phpMyAdmin.</li>" .
        "<li>Que el `\$username_db` (`" . htmlspecialchars($username_db) . "`) y `\$password_db` en `test_conexion.php` sean los **correctos** para tu MySQL.</li>" .
        "</ul>");
}

echo "<h3>¡Conexión exitosa a la base de datos '$dbname'!</h3>";

// Opcional: Establecer el conjunto de caracteres
if (!$conn->set_charset("utf8")) {
    echo "<p style='color:orange;'>Advertencia: Error al establecer el juego de caracteres: " . $conn->error . "</p>";
}

$conn->close();
echo "<p>Conexión cerrada.</p>";
?>