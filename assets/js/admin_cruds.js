/**
 * Funciones globales para la administración de CRUDs
 * Incluye lógica de modales, búsqueda en tablas y peticiones AJAX
 */

function abrirModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('visible');
}

function cerrarModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('visible');
}

// Cerrar modales al hacer clic fuera
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.modal-fondo').forEach(m => {
    m.addEventListener('click', e => {
      if (e.target === m) m.classList.remove('visible');
    });
  });

  // Interceptar formularios de modales para UX mejorada (evita doble submit y permite transición suave)
  document.querySelectorAll('.modal-caja form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const btn = this.querySelector('button[type="submit"]');
      if (btn) {
        // Bloquear doble click
        if (btn.hasAttribute('disabled')) {
          e.preventDefault();
          return;
        }
        btn.setAttribute('disabled', 'true');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        
        // Si quieres usar AJAX real (fetch), descomenta lo siguiente y quita el POST directo
        /*
        e.preventDefault();
        fetch(this.action, {
          method: this.method || 'POST',
          body: new FormData(this)
        }).then(res => {
          if(res.ok) window.location.reload();
          else alert('Error en la petición');
        }).finally(() => {
          btn.removeAttribute('disabled');
          btn.innerHTML = oldHtml;
        });
        */
      }
    });
  });
});

// Función global de búsqueda para tablas/grids
function inicializarBusqueda(inputId, selectorElementos, fnFiltrarAdicional = null) {
  const input = document.getElementById(inputId);
  if (!input) return;

  input.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
    const elementos = document.querySelectorAll(selectorElementos);

    elementos.forEach(el => {
      // Ignorar filas de "sin datos"
      if (el.querySelector && el.querySelector('td[colspan]')) return;

      const texto = el.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
      let mostrar = texto.includes(query);

      // Si hay filtros adicionales (ej. estado), aplicarlos
      if (mostrar && fnFiltrarAdicional) {
        mostrar = fnFiltrarAdicional(el);
      }

      if (mostrar) {
        el.style.display = '';
        if (el.classList.contains('card-nivel')) {
          el.style.animation = 'none';
          el.offsetHeight; /* trigger reflow */
          el.style.animation = 'entrar 0.3s cubic-bezier(0.4, 0, 0.2, 1) both';
        }
      } else {
        el.style.display = 'none';
      }
    });
  });
}
