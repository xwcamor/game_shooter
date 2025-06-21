<?php
session_start(); // Siempre debe ir al principio de cualquier script que use sesiones

// Habilitar reporte de errores para depuración (QUITAR EN PRODUCCIÓN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Si el usuario no ha iniciado sesión, redirige a la página de login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { // Verificamos 'loggedin' y si es true
    header("Location: login_from.php"); // Redirige al formulario de login
    exit();
}

// Si llega aquí, el usuario ha iniciado sesión
$username_display = htmlspecialchars($_SESSION['username'] ?? 'Invitado'); 
$usuario_id_session = htmlspecialchars($_SESSION['user_id'] ?? ''); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Juego Disparos - Cartoon</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        /* Tu CSS incrustado si no tienes style.css o si tienes CSS adicional */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
            text-align: center;
        }
        h1 {
            color: #333;
        }
        canvas {
            border: 2px solid #333;
            background-color: #eee;
            display: block;
            margin: 20px auto;
        }
        /* Estilos para el formulario flotante de guardar puntaje */
        #formulario {
            display: none; /* Oculto por defecto */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            text-align: center;
            width: 300px; /* Ancho fijo para el formulario */
        }
        #formulario h2 {
            margin-top: 0;
            color: #333;
        }
        #formulario button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }
        #formulario button[type="submit"]:hover {
            background-color: #45a049;
        }
        #btnCerrarFormulario {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }
        #btnCerrarFormulario:hover {
            color: #333;
        }
        #mensaje {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
            display: none; /* Oculto por defecto */
        }
        /* Estilos para el leaderboard */
        #leaderboard {
            margin-top: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        #leaderboard h2 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        #leaderboard ol {
            list-style-type: decimal;
            padding-left: 25px;
            text-align: left;
        }
        #leaderboard li {
            padding: 8px 0;
            border-bottom: 1px dotted #eee;
            color: #555;
        }
        #leaderboard li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <h1>Mini Juego Disparos</h1>
    <p>Bienvenido, <strong><?= $username_display ?></strong> | <a href="logout.php">Cerrar sesión</a></p>
    <p>Puntaje: <span id="puntaje">0</span> | Ronda: <span id="ronda">1</span></p>
    <div id="vidasContainer">❤️❤️❤️</div>
    <p id="temporizador">Tiempo restante: <span id="timer">30</span> s</p>

    <button id="btnGuardarPuntaje">Guardar Puntaje</button>

    <canvas id="juegoCanvas" width="600" height="400"></canvas>

    <div id="formulario">
        <button id="btnCerrarFormulario" title="Cerrar formulario">&times;</button>
        <h2>Guardar Puntaje</h2>
        <form id="formPuntaje" action="guardar_puntaje.php" method="post">
            <input type="hidden" id="puntajeFinal" name="puntaje" />
            <input type="hidden" name="usuario_id" value="<?= $usuario_id_session ?>" />
            <input type="hidden" name="nombre_usuario" value="<?= $username_display ?>" />
            <button type="submit">Guardar Puntaje</button>
        </form>
        <div id="mensaje"></div>
    </div>

    <div id="leaderboard">
        <h2>Mejores Puntajes</h2>
        <ol id="listaPuntajes"></ol>
    </div>

    <script src="game.js"></script>
    <script>
        const btnGuardar = document.getElementById("btnGuardarPuntaje");
        const formulario = document.getElementById("formulario");
        const puntajeFinalInput = document.getElementById("puntajeFinal");
        const btnCerrar = document.getElementById("btnCerrarFormulario");
        const formPuntaje = document.getElementById("formPuntaje");
        const mensaje = document.getElementById("mensaje");

        btnGuardar.addEventListener("click", () => {
            if (typeof pausarJuego === "function") pausarJuego();
            puntajeFinalInput.value = document.getElementById("puntaje").textContent;
            formulario.style.display = "block";
            mensaje.style.display = "none";
            formPuntaje.style.display = "block";
        });

        btnCerrar.addEventListener("click", () => {
            formulario.style.display = "none";
            if (typeof reanudarJuego === "function") reanudarJuego();
        });

        formPuntaje.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(formPuntaje);

            fetch(formPuntaje.action, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    // Si la respuesta HTTP no es 2xx, manejar como error de red/servidor
                    return res.text().then(text => { throw new Error(text) });
                }
                return res.json(); // Ahora esperamos JSON directamente
            })
            .then(data => {
                // Aquí 'data' ya es el objeto JSON { success: true, message: "..." } o { success: false, message: "..." }
                if (data.success) { // Verifica la propiedad 'success' del JSON
                    mensaje.textContent = data.message; // Muestra el mensaje del servidor
                    mensaje.style.color = "#155724"; // Verde para éxito
                    mensaje.style.backgroundColor = "#d4edda";
                    mensaje.style.border = "1px solid #c3e6cb";
                    cargarLeaderboard(); // Actualiza el leaderboard
                } else {
                    mensaje.textContent = "Error al guardar el puntaje: " + data.message; // Muestra el mensaje de error del servidor
                    mensaje.style.color = "#721c24"; // Rojo para error
                    mensaje.style.backgroundColor = "#f8d7da";
                    mensaje.style.border = "1px solid #f5c6cb";
                }
                mensaje.style.display = "block";
                formPuntaje.style.display = "none"; // Oculta el formulario después de guardar/error
            })
            .catch(err => {
                console.error("Error en la comunicación con el servidor o JSON inválido:", err);
                mensaje.textContent = "Error de red o respuesta del servidor inválida: " + err.message;
                mensaje.style.color = "#721c24";
                mensaje.style.backgroundColor = "#f8d7da";
                mensaje.style.border = "1px solid #f5c6cb";
                mensaje.style.display = "block";
            });
        });

        // Función para cargar el leaderboard
        function cargarLeaderboard() {
            fetch("leaderboard.php")
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text) });
                    }
                    return res.json();
                })
                .then(puntajes => {
                    const lista = document.getElementById("listaPuntajes");
                    lista.innerHTML = ""; // Limpiar la lista antes de añadir nuevos elementos
                    if (Array.isArray(puntajes) && puntajes.length > 0) {
                        puntajes.forEach(p => {
                            // Asegúrate de que 'nombre_usuario' y 'puntaje' existan en cada objeto de puntaje
                            if (p.nombre_usuario && p.puntaje !== undefined) {
                                lista.innerHTML += `<li>${htmlspecialchars(p.nombre_usuario)} - ${htmlspecialchars(String(p.puntaje))} pts</li>`;
                            }
                        });
                    } else {
                        lista.innerHTML = "<li>No hay puntajes registrados aún.</li>";
                    }
                })
                .catch(err => {
                    console.error("Error al cargar el leaderboard:", err);
                    const lista = document.getElementById("listaPuntajes");
                    lista.innerHTML = `<li>No se pudo cargar el leaderboard. Error: ${err.message}</li>`;
                });
        }

        // Cargar el leaderboard al iniciar la página
        cargarLeaderboard();

        // Función para escapar HTML en JavaScript (para seguridad)
        function htmlspecialchars(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>
</body>
</html>