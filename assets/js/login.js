/**
 * login.js — Lógica JS del módulo de autenticación
 * Maneja el cambio entre iniciar sesión y registrarse sin recargar la página.
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Tabs de ACCIÓN (Ingresar / Registrarse) ── */
  document.getElementById('btn-ingresar')?.addEventListener('click',  () => activarAccion('ingresar'));
  document.getElementById('btn-registrar')?.addEventListener('click', () => activarAccion('registrar'));

  /**
   * Muestra el formulario correspondiente a la acción seleccionada.
   * @param {string} accion 'ingresar' | 'registrar'
   */
  function activarAccion(accion) {
    const fIngresar  = document.getElementById('formulario-ingresar');
    const fRegistro  = document.getElementById('formulario-registro');
    const btnIng     = document.getElementById('btn-ingresar');
    const btnReg     = document.getElementById('btn-registrar');

    if (accion === 'ingresar') {
      fIngresar?.style && (fIngresar.style.display = 'block');
      fRegistro?.style && (fRegistro.style.display = 'none');
      btnIng?.classList.add('activo');
      btnReg?.classList.remove('activo');
    } else {
      fIngresar?.style && (fIngresar.style.display = 'none');
      fRegistro?.style && (fRegistro.style.display = 'block');
      btnIng?.classList.remove('activo');
      btnReg?.classList.add('activo');
    }
  }

  /* ── Validación del lado del cliente (W11) ── */
  const inputFicha = document.getElementById('ficha-registro');
  const errFicha   = document.getElementById('error-ficha-registro');
  const inputClave = document.getElementById('clave-registro');
  const errClave   = document.getElementById('error-clave-registro');
  const selProg    = document.getElementById('programa-registro');
  const datalist   = document.getElementById('lista-fichas-registro');

  if (inputFicha && errFicha) {
    inputFicha.addEventListener('input', () => {
      const original = inputFicha.value;
      if (/[^0-9]/.test(original)) {
        errFicha.textContent = 'La ficha solo admite números (sin letras ni caracteres especiales).';
        errFicha.style.display = 'block';
        inputFicha.value = original.replace(/[^0-9]/g, '');
      } else {
        errFicha.textContent = '';
        errFicha.style.display = 'none';
      }
    });

    inputFicha.addEventListener('blur', () => {
      const val = inputFicha.value.trim();
      if (!val) {
        errFicha.textContent = 'La ficha SENA es obligatoria para aprendices.';
        errFicha.style.display = 'block';
      } else if (!/^[0-9]+$/.test(val)) {
        errFicha.textContent = 'La ficha SENA debe contener únicamente dígitos.';
        errFicha.style.display = 'block';
      } else {
        errFicha.textContent = '';
        errFicha.style.display = 'none';
      }
    });
  }

  // Filtrar sugerencias de fichas según el programa seleccionado
  if (selProg && datalist) {
    selProg.addEventListener('change', () => {
      const progId = selProg.value;
      const options = datalist.querySelectorAll('option');
      options.forEach(opt => {
        const optProg = opt.getAttribute('data-programa');
        opt.disabled = (progId && optProg && optProg !== progId);
      });
    });
  }

  document.getElementById('formulario-registro')?.addEventListener('submit', (e) => {
    let hayError = false;

    // Validar ficha SENA
    if (inputFicha && errFicha) {
      const valFicha = inputFicha.value.trim();
      if (!valFicha) {
        e.preventDefault();
        errFicha.textContent = 'La ficha SENA es obligatoria para el registro.';
        errFicha.style.display = 'block';
        inputFicha.focus();
        hayError = true;
      } else if (!/^[0-9]+$/.test(valFicha)) {
        e.preventDefault();
        errFicha.textContent = 'La ficha SENA debe contener únicamente dígitos numéricos.';
        errFicha.style.display = 'block';
        inputFicha.focus();
        hayError = true;
      } else {
        errFicha.textContent = '';
        errFicha.style.display = 'none';
      }
    }

    // Validar contraseña
    const clave = inputClave?.value ?? '';
    if (clave.length < 8 || !/[A-Z]/.test(clave) || !/[0-9]/.test(clave)) {
      e.preventDefault();
      if (errClave) {
        errClave.textContent = 'La contraseña debe tener mínimo 8 caracteres, 1 mayúscula y 1 número.';
        errClave.style.display = 'block';
        if (!hayError) inputClave?.focus();
      }
      hayError = true;
    } else if (errClave) {
      errClave.textContent = '';
      errClave.style.display = 'none';
    }
  });

  /* ── Alternar Ojo de Contraseña ── */
  const togglesPassword = document.querySelectorAll('.toggle-password');
  togglesPassword.forEach(toggle => {
    toggle.addEventListener('click', () => {
      const targetId = toggle.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          toggle.classList.remove('fa-eye');
          toggle.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          toggle.classList.remove('fa-eye-slash');
          toggle.classList.add('fa-eye');
        }
      }
    });
  });

});
