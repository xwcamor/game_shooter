<?php
session_start();    // Inicia la sesión existente
session_destroy();  // Destruye todas las variables de sesión del servidor
session_unset();    // Elimina todas las variables de la sesión actual

// Redirige al usuario al formulario de login (asegúrate de que el nombre del archivo sea correcto)
header("Location: login_from.php"); // ¡CORREGIDO: apunta a login_from.php!
exit();             // Termina la ejecución del script después de la redirección
?>