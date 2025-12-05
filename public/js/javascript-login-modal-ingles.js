// IIFE para no ensuciar el scope global
(function () {
  'use strict';

  // ===============================
  // Referencias a elementos del DOM
  // ===============================

  const loginModal     = document.getElementById('loginModal');
  const btnAbrirLogin  = document.getElementById('btnAbrirLogin');
  const btnCerrarLogin = document.getElementById('btnCerrarLogin');
  const modalLoginForm = document.getElementById('modalLoginForm');

  const usuario       = document.getElementById('username');
  const clave         = document.getElementById('password');
  const mensajeModal  = document.getElementById('mensajeModal');

  // Si no existe el modal, salimos silenciosamente (evita errores en otras vistas)
  if (!loginModal) {
    return;
  }

  // ===============================
  // Funciones auxiliares
  // ===============================

  function actualizarBotonLogin(haySesion) {
    if (!btnAbrirLogin) return;

    // Hay sesión activa → ocultar botón
    // No hay sesión      → mostrar botón
    btnAbrirLogin.style.display = haySesion ? 'none' : 'block';
  }

  function mostrarLoginModal() {
    loginModal.classList.add('is-visible');

    // Enfocar input de usuario al abrir
    if (usuario) {
      setTimeout(() => usuario.focus(), 50);
    }
  }

  function ocultarLoginModal() {
    loginModal.classList.remove('is-visible');

    if (mensajeModal) {
      mensajeModal.textContent = '';
    }
    if (clave) {
      clave.value = '';
    }
    if (usuario) {
      usuario.value = '';
    }
  }

  // ===============================
  // Listeners de inputs (limpiar mensaje)
  // ===============================

  if (usuario && mensajeModal) {
    usuario.addEventListener('input', () => {
      mensajeModal.textContent = '';
    });
  }

  if (clave && mensajeModal) {
    clave.addEventListener('input', () => {
      mensajeModal.textContent = '';
    });
  }

  // ===============================
  // Eventos del modal
  // ===============================

  // Por defecto: hay sesión (porque ya estás dentro del sistema) → botón oculto
  actualizarBotonLogin(true);

  // Abrir modal al hacer clic en el botón
  if (btnAbrirLogin) {
    btnAbrirLogin.addEventListener('click', mostrarLoginModal);
  }

  // Cerrar con la X
  if (btnCerrarLogin) {
    btnCerrarLogin.addEventListener('click', ocultarLoginModal);
  }

  // Cerrar haciendo clic en el fondo
  loginModal.addEventListener('click', (e) => {
    if (e.target === loginModal) {
      ocultarLoginModal();
    }
  });

  // Cerrar con tecla ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && loginModal.classList.contains('is-visible')) {
      ocultarLoginModal();
    }
  });

  // ===============================
  // Submit del formulario de login
  // ===============================

  if (modalLoginForm) {
    modalLoginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const data     = Object.fromEntries(formData.entries());
      console.log('[LOGIN] Enviando datos:', data);

      fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      })
      .then((response) => {
        return response.json().then((json) => {
          console.log('[LOGIN] Respuesta:', json);

          if (response.ok && json.status === 'success') {
            actualizarBotonLogin(true);

            if (mensajeModal) {
              mensajeModal.style.color = '#00ff88';
              mensajeModal.textContent = 'Acceso concedido. Redirigiendo...';
            }

            setTimeout(() => {
				App.ingles.reload();
              ocultarLoginModal();
              // location.href = '/dashboard';
            }, 800);

          } else {
            if (mensajeModal) {
              mensajeModal.style.color = '#ff9999';
              mensajeModal.textContent = 'Credenciales incorrectas. Intente de nuevo.';
            }
          }
        });
      })
      .catch((error) => {
        console.error('[LOGIN] Error en fetch:', error);
        if (mensajeModal) {
          mensajeModal.style.color = '#ff9999';
          mensajeModal.textContent = 'Error de conexión. Intente nuevamente.';
        }
      });
    });
  }

  // ===============================
  // Exponer funciones globales si quieres usarlas en otros scripts
  // ===============================
  window.actualizarBotonLogin = actualizarBotonLogin;
  window.mostrarLoginModal    = mostrarLoginModal;
  window.ocultarLoginModal    = ocultarLoginModal;

})();

    
    
    
 
