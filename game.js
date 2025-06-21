// game.js

// ==============================================================================
// === Obtención del Canvas y Variables Globales ===
// ==============================================================================
const canvas = document.getElementById('juegoCanvas');

// Si el canvas no existe (ej. el script se carga en login_from.php), no ejecutar el resto
if (!canvas) {
    console.warn("Canvas 'juegoCanvas' no encontrado. El script game.js no se ejecutará completamente.");
} else {
    const ctx = canvas.getContext('2d');

    // Variables del juego
    let puntaje = 0;
    let ronda = 1;
    let vidas = 3;
    let temporizador = 30; // Segundos por ronda
    let juegoPausado = false;
    let juegoTerminado = false;
    let intervaloTemporizador;

    // Elementos del DOM para actualizar la UI
    const puntajeSpan = document.getElementById('puntaje');
    const rondaSpan = document.getElementById('ronda');
    const vidasContainer = document.getElementById('vidasContainer');
    const timerSpan = document.getElementById('timer');

    // ==============================================================================
    // === Carga de Recursos (Imágenes y Sonidos) ===
    // ==============================================================================
    const imagenes = {};
    const sonidos = {};
    let recursosCargados = 0;
    // Tenemos 4 imágenes (fondo, jugador, enemigo, mira) + 3 sonidos (disparo, enemigo_muerto, victoria)
    // + 1 música de fondo = 8 recursos en total.
    const totalRecursos = 8; // ¡Número total de recursos actualizado!

    // Función auxiliar para cargar una imagen
    function cargarImagen(nombre, ruta) {
        imagenes[nombre] = new Image();
        imagenes[nombre].src = ruta;
        imagenes[nombre].onload = () => {
            recursosCargados++;
            console.log(`Imagen '${nombre}' cargada.`);
            verificarCargaRecursos();
        };
        imagenes[nombre].onerror = () => {
            console.error(`Error al cargar la imagen '${nombre}': ` + ruta);
        };
    }

    // Función auxiliar para cargar un sonido
    function cargarSonido(nombre, ruta) {
        sonidos[nombre] = new Audio(ruta);
        // El evento 'oncanplaythrough' asegura que el audio se puede reproducir sin interrupciones
        sonidos[nombre].oncanplaythrough = () => {
            recursosCargados++;
            console.log(`Sonido '${nombre}' cargado.`);
            verificarCargaRecursos();
        };
        sonidos[nombre].onerror = () => {
            console.error(`Error al cargar el sonido '${nombre}': ` + ruta);
        };
    }

    // Cargar todas las imágenes
    cargarImagen('fondo', 'imagenes/fondo.jpg');
    cargarImagen('jugador', 'imagenes/jugador.png');
    cargarImagen('enemigo', 'imagenes/enemigo.png');
    cargarImagen('mira', 'imagenes/mira.png');

    // Cargar tus sonidos
    cargarSonido('disparo', 'sonidos/disparo.mp3');
    cargarSonido('enemigo_muerto', 'sonidos/enemigo_muerto.mp3');
    cargarSonido('victoria', 'sonidos/victoria.mp3');
    // Asegúrate de que esta ruta sea correcta:
    // Si tu archivo se llama 'fondo.mp3' y está en la raíz de 'juego_2', usa 'fondo.mp3'
    // Si tu archivo se llama 'musica_fondo.mp3' y está en la carpeta 'sonidos', usa 'sonidos/musica_fondo.mp3'
    cargarSonido('musica_fondo', 'sonidos/musica_fondo.mp3'); 

    // Función para verificar si todos los recursos han cargado
    function verificarCargaRecursos() {
        if (recursosCargados === totalRecursos) {
            console.log('Todos los recursos cargados. Iniciando juego...');
            // Iniciar el juego solo cuando todos los recursos estén listos
            actualizarUI();
            iniciarTemporizador();
            requestAnimationFrame(gameLoop); // Inicia el bucle de juego
            // --- Reproducir música de fondo aquí ---
            if (sonidos.musica_fondo) {
                sonidos.musica_fondo.loop = true; // Hace que la música se repita
                sonidos.musica_fondo.volume = 0.5; // Ajusta el volumen (0.0 a 1.0)
                // Usamos un pequeño retraso o un intento de play para sortear restricciones de autoplay del navegador
                sonidos.musica_fondo.play().catch(e => {
                    console.warn("Autoplay de música de fondo bloqueado. Haz clic en la pantalla para iniciarla.");
                    // Si el autoplay es bloqueado, puedes agregar un listener para iniciarla con la primera interacción
                    // Importante: Solo añade el listener una vez
                    canvas.addEventListener('click', () => {
                        if (sonidos.musica_fondo.paused) {
                            sonidos.musica_fondo.play().catch(e => console.error("Error al reintentar play:", e));
                        }
                    }, { once: true }); // El evento se dispara solo una vez
                });
            }
        }
    }

    // ==============================================================================
    // === Elementos del Juego ===
    // ==============================================================================

    // Jugador
    let jugadorX = canvas.width / 2 - 25;
    const jugadorY = canvas.height - 70;
    const jugadorAncho = 50;
    const jugadorAlto = 50;

    // Enemigo (por ahora uno solo)
    let enemigoX = Math.random() * (canvas.width - 50);
    let enemigoY = 50;
    const enemigoAncho = 50;
    const enemigoAlto = 50;
    let enemigoVelocidad = 2; // Velocidad inicial

    // Balas
    let balas = [];
    const balaAncho = 5;
    const balaAlto = 15;
    const balaVelocidad = 7;

    // ==============================================================================
    // === Funciones de Dibujo ===
    // ==============================================================================

    // Dibujar el fondo
    function dibujarFondo() {
        if (imagenes.fondo && imagenes.fondo.complete) {
            ctx.drawImage(imagenes.fondo, 0, 0, canvas.width, canvas.height);
        } else {
            // Fallback si la imagen no se carga
            ctx.fillStyle = '#1a1a1a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
    }

    // Dibujar el jugador
    function dibujarJugador() {
        if (imagenes.jugador && imagenes.jugador.complete) {
            ctx.drawImage(imagenes.jugador, jugadorX, jugadorY, jugadorAncho, jugadorAlto);
        } else {
            ctx.fillStyle = 'blue';
            ctx.fillRect(jugadorX, jugadorY, jugadorAncho, jugadorAlto);
        }
    }

    // Dibujar el enemigo
    function dibujarEnemigo() {
        if (imagenes.enemigo && imagenes.enemigo.complete) {
            ctx.drawImage(imagenes.enemigo, enemigoX, enemigoY, enemigoAncho, enemigoAlto);
        } else {
            ctx.fillStyle = 'red';
            ctx.fillRect(enemigoX, enemigoY, enemigoAncho, enemigoAlto);
        }
    }

    // Dibujar las balas
    function dibujarBalas() {
        ctx.fillStyle = 'yellow'; // Color de las balas si no hay imagen
        balas.forEach(bala => {
            ctx.fillRect(bala.x, bala.y, balaAncho, balaAlto);
        });
    }

    // ==============================================================================
    // === Lógica de Juego (Movimiento, Acciones, Colisiones) ===
    // ==============================================================================

    // Mover el enemigo
    function moverEnemigo() {
        enemigoY += enemigoVelocidad;
        // Si el enemigo sale por abajo, lo reseteamos arriba
        if (enemigoY > canvas.height) {
            enemigoY = -enemigoAlto; // Reaparece fuera de la pantalla por arriba
            enemigoX = Math.random() * (canvas.width - enemigoAncho); // Nueva posición X
            vidas--; // Pierdes una vida si se escapa
            actualizarUI();
            console.log("Enemigo se escapó. Vidas restantes:", vidas); // Depuración
            if (vidas <= 0) {
                juegoTerminado = true;
                alert("¡GAME OVER! Te quedaste sin vidas. Tu puntaje final es: " + puntaje);
                // Pausar la música de fondo aquí también
                if (sonidos.musica_fondo && !sonidos.musica_fondo.paused) {
                    sonidos.musica_fondo.pause(); 
                }
                document.getElementById('btnGuardarPuntaje').click();
                if (sonidos.victoria) { // Usamos el sonido de victoria también para game over
                    sonidos.victoria.currentTime = 0;
                    sonidos.victoria.play().catch(e => console.error("Error al reproducir sonido de game over:", e));
                }
            }
        }
    }

    // Mover las balas
    function moverBalas() {
        for (let i = balas.length - 1; i >= 0; i--) {
            balas[i].y -= balas[i].velocidad; // Mueve la bala hacia arriba

            // Eliminar balas que salen de la pantalla
            if (balas[i].y < 0) {
                balas.splice(i, 1);
            }
        }
    }

    // Disparar una bala
    function disparar() {
        if (juegoPausado || juegoTerminado) return;

        // Crea una nueva bala en la posición del jugador
        balas.push({
            x: jugadorX + jugadorAncho / 2 - balaAncho / 2, // Centro del jugador
            y: jugadorY,
            velocidad: balaVelocidad
        });
        console.log("Bala disparada. Total de balas:", balas.length); // Depuración

        // Reproducir sonido de disparo
        if (sonidos.disparo) {
            sonidos.disparo.currentTime = 0; // Reinicia el sonido si ya se está reproduciendo
            sonidos.disparo.play().catch(e => console.error("Error al reproducir sonido de disparo:", e));
        }
    }

    // Detección de colisiones entre balas y enemigo
    function detectarColisiones() {
        for (let i = balas.length - 1; i >= 0; i--) {
            const bala = balas[i];

            // Colisión bala-enemigo (algoritmo AABB - Axis-Aligned Bounding Box)
            if (
                bala.x < enemigoX + enemigoAncho &&
                bala.x + balaAncho > enemigoX &&
                bala.y < enemigoY + enemigoAlto &&
                bala.y + balaAlto > enemigoY
            ) {
                // Colisión detectada
                console.log("¡Colisión detectada!"); // Depuración
                puntaje += 100; // Incrementa puntaje
                actualizarUI();

                // Eliminar la bala
                balas.splice(i, 1);

                // Reproducir sonido de enemigo muerto
                if (sonidos.enemigo_muerto) {
                    sonidos.enemigo_muerto.currentTime = 0;
                    sonidos.enemigo_muerto.play().catch(e => console.error("Error al reproducir sonido enemigo muerto:", e));
                }

                // Resetear el enemigo a una nueva posición
                enemigoY = -enemigoAlto; // Reaparece arriba
                enemigoX = Math.random() * (canvas.width - enemigoAncho); // Nueva posición X
                enemigoVelocidad += 0.2; // Aumentar un poco la velocidad del enemigo para la próxima vez
                console.log("Enemigo golpeado. Nuevo puntaje:", puntaje); // Depuración
            }
        }
    }

    // ==============================================================================
    // === Actualización de la Interfaz y Temporizador ===
    // ==============================================================================

    // Actualiza los textos de puntaje, ronda, vidas y temporizador en la UI
    function actualizarUI() {
        if (puntajeSpan) puntajeSpan.textContent = puntaje;
        if (rondaSpan) rondaSpan.textContent = ronda;
        if (vidasContainer) vidasContainer.innerHTML = '❤️'.repeat(vidas);
        if (timerSpan) timerSpan.textContent = temporizador;
    }

    // Iniciar el temporizador de la ronda
    function iniciarTemporizador() {
        if (intervaloTemporizador) clearInterval(intervaloTemporizador); // Limpiar cualquier temporizador anterior
        intervaloTemporizador = setInterval(() => {
            if (!juegoPausado && !juegoTerminado) {
                temporizador--;
                actualizarUI();
                if (temporizador <= 0) {
                    clearInterval(intervaloTemporizador);
                    juegoTerminado = true; // El juego termina por tiempo
                    alert("¡Tiempo fuera! Fin de la ronda. Tu puntaje final es: " + puntaje);
                    // Pausar la música de fondo aquí también
                    if (sonidos.musica_fondo && !sonidos.musica_fondo.paused) {
                        sonidos.musica_fondo.pause(); 
                    }
                    document.getElementById('btnGuardarPuntaje').click(); // Abrir formulario de guardar puntaje
                    if (sonidos.victoria) {
                        sonidos.victoria.currentTime = 0;
                        sonidos.victoria.play().catch(e => console.error("Error al reproducir sonido de victoria:", e));
                    }
                }
            }
        }, 1000); // Cada segundo
    }

    // ==============================================================================
    // === Bucle Principal del Juego ===
    // ==============================================================================

    // Esta función se llama repetidamente para actualizar y dibujar el juego
    function gameLoop() {
        if (juegoPausado || juegoTerminado) {
            return; // No hacer nada si el juego está pausado o terminado
        }

        // 1. Actualizar la lógica de todos los elementos del juego
        moverEnemigo();
        moverBalas();
        detectarColisiones();

        // 2. Limpiar todo el canvas antes de volver a dibujar
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // 3. Dibujar todos los elementos actualizados
        dibujarFondo();
        dibujarJugador();
        dibujarEnemigo();
        dibujarBalas();


        // Solicitar al navegador que dibuje el siguiente frame
        requestAnimationFrame(gameLoop);
    }

    // ==============================================================================
    // === Funciones de Control (Pausar/Reanudar) ===
    // ==============================================================================

    // Funciones globales para ser llamadas desde index.php (para el formulario de puntaje)
    window.pausarJuego = function() {
        juegoPausado = true;
        clearInterval(intervaloTemporizador); // Detiene el temporizador
        console.log("Juego Pausado");
        if (sonidos.musica_fondo) {
            sonidos.musica_fondo.pause(); // Pausar la música al pausar el juego
        }
    };

    window.reanudarJuego = function() {
        if (!juegoTerminado) { // Solo reanudar si el juego no ha terminado
            juegoPausado = false;
            iniciarTemporizador(); // Reinicia el temporizador
            requestAnimationFrame(gameLoop); // Reanuda el bucle de juego
            console.log("Juego Reanudado");
            if (sonidos.musica_fondo) {
                sonidos.musica_fondo.play().catch(e => console.error("Error al reanudar música:", e)); // Reanudar la música
            }
        }
    };

    // ==============================================================================
    // === Control de Eventos (Teclado) ===
    // ==============================================================================

    // Manejo de movimiento del jugador con teclas A y D
    document.addEventListener('keydown', (e) => {
        if (juegoPausado || juegoTerminado) return; // No permitir movimiento si el juego está pausado/terminado

        const velocidad = 10;
        if (e.key === 'a' || e.key === 'A') {
            jugadorX -= velocidad;
        } else if (e.key === 'd' || e.key === 'D') {
            jugadorX += velocidad;
        }
        // Limitar el movimiento del jugador dentro de los límites del canvas
        if (jugadorX < 0) jugadorX = 0;
        if (jugadorX + jugadorAncho > canvas.width) jugadorX = canvas.width - jugadorAncho;
    });

    // Manejo de disparo con la barra espaciadora
    document.addEventListener('keydown', (e) => {
        if (e.key === ' ' && !juegoPausado && !juegoTerminado) {
            disparar();
            e.preventDefault(); // Evita que la barra espaciadora haga scroll en la página
        }
    });


    // ==============================================================================
    // === Inicialización del Juego ===
    // ==============================================================================

    // Este console.log se ejecuta cuando el script se carga por primera vez
    console.log("game.js cargado. Esperando a que todos los recursos se carguen para iniciar el juego...");

    // El juego se inicializa llamando a verificarCargaRecursos() cuando el último recurso carga.
    // Esto asegura que todas las imágenes y sonidos estén listos antes de empezar a dibujar.
}