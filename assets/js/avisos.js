/**
 * avisos.js
 * Reemplazo de alert() con avisos propios del producto.
 *
 * Los alert() nativos bloquean el hilo, no se pueden estilizar, muestran el
 * dominio del sitio y rompen por completo la estética de la plataforma.
 *
 * Expone dos cosas:
 *   Avisos.toast(mensaje, tipo)  → mensaje breve que se va solo (error | exito | info)
 *   Avisos.dialogo({...})        → ventana modal para lo que exige una explicación
 *
 * Se inyecta su propio CSS para funcionar en cualquier vista sin tocar hojas de
 * estilo, y usa las variables del tema, así que respeta claro y oscuro.
 */
(function (global) {
  'use strict';

  var CSS = [
    '.aviso-capa{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;',
    'justify-content:center;z-index:9998;padding:20px;opacity:0;transition:opacity .16s ease}',
    '.aviso-capa.visible{opacity:1}',
    '.aviso-modal{background:var(--bg-card,var(--fondo,#1F2C33));color:var(--texto-principal,#fff);',
    'border:2px solid var(--borde-sutil,#2B3E46);border-radius:18px;max-width:440px;width:100%;',
    'padding:26px;text-align:center;box-shadow:0 12px 40px rgba(0,0,0,.4);',
    'transform:translateY(10px) scale(.98);transition:transform .16s ease;max-height:85vh;overflow-y:auto}',
    '.aviso-capa.visible .aviso-modal{transform:none}',
    '.aviso-modal-izq{text-align:left}',
    '.aviso-icono{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;',
    'justify-content:center;font-size:24px;margin:0 auto 16px;color:#fff}',
    '.aviso-titulo{font-size:1.15rem;font-weight:900;margin-bottom:8px}',
    '.aviso-texto{font-size:.92rem;font-weight:600;line-height:1.55;color:var(--texto-secundario,#C7D0D4)}',
    '.aviso-acciones{margin-top:22px;display:flex;gap:10px;justify-content:center}',
    '.aviso-btn{border:none;cursor:pointer;border-radius:12px;padding:11px 26px;font-weight:800;',
    'font-size:.88rem;background:var(--duo-green,#58CC02);color:#fff;box-shadow:0 4px 0 var(--duo-green-dark,#46A302);',
    'transition:transform .1s ease}',
    '.aviso-btn:active{transform:translateY(3px);box-shadow:0 1px 0 var(--duo-green-dark,#46A302)}',
    '.aviso-pila{position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:9999;',
    'display:flex;flex-direction:column;gap:10px;pointer-events:none;width:min(420px,calc(100% - 32px))}',
    '.aviso-toast{display:flex;align-items:flex-start;gap:11px;padding:13px 16px;border-radius:14px;',
    'background:var(--bg-card,var(--fondo,#1F2C33));color:var(--texto-principal,#fff);border:2px solid var(--borde-sutil,#2B3E46);',
    'border-left-width:6px;box-shadow:0 6px 22px rgba(0,0,0,.28);font-size:.88rem;font-weight:700;',
    'opacity:0;transform:translateY(-10px);transition:opacity .18s ease,transform .18s ease;pointer-events:auto}',
    '.aviso-toast.visible{opacity:1;transform:none}',
    '.aviso-toast i{margin-top:2px;flex-shrink:0}',
    '.aviso-toast.error{border-left-color:var(--rojo,#FF4B4B)}.aviso-toast.error i{color:var(--rojo,#FF4B4B)}',
    '.aviso-toast.exito{border-left-color:var(--duo-green,#58CC02)}.aviso-toast.exito i{color:var(--duo-green,#58CC02)}',
    '.aviso-toast.info{border-left-color:var(--azul,#1CB0F6)}.aviso-toast.info i{color:var(--azul,#1CB0F6)}',
    '@media (max-width:600px){.aviso-modal{padding:22px}}'
  ].join('');

  var ICONOS = { error: 'fa-circle-exclamation', exito: 'fa-circle-check', info: 'fa-circle-info' };

  function inyectarCss() {
    if (document.getElementById('aviso-estilos')) return;
    var estilo = document.createElement('style');
    estilo.id = 'aviso-estilos';
    estilo.textContent = CSS;
    document.head.appendChild(estilo);
  }

  function escapar(texto) {
    return String(texto == null ? '' : texto)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function pila() {
    var caja = document.getElementById('aviso-pila');
    if (!caja) {
      caja = document.createElement('div');
      caja.id = 'aviso-pila';
      caja.className = 'aviso-pila';
      caja.setAttribute('role', 'status');
      caja.setAttribute('aria-live', 'polite');
      document.body.appendChild(caja);
    }
    return caja;
  }

  function toast(mensaje, tipo, duracion) {
    inyectarCss();
    tipo = ICONOS[tipo] ? tipo : 'error';
    var nodo = document.createElement('div');
    nodo.className = 'aviso-toast ' + tipo;
    nodo.innerHTML = '<i class="fas ' + ICONOS[tipo] + '"></i><span>' + escapar(mensaje) + '</span>';
    pila().appendChild(nodo);
    requestAnimationFrame(function () { nodo.classList.add('visible'); });

    var cerrar = function () {
      nodo.classList.remove('visible');
      setTimeout(function () { if (nodo.parentNode) nodo.parentNode.removeChild(nodo); }, 200);
    };
    setTimeout(cerrar, duracion || 4500);
    nodo.addEventListener('click', cerrar);
    return cerrar;
  }

  function dialogo(opciones) {
    inyectarCss();
    opciones = opciones || {};

    var focoAnterior=document.activeElement;
    var capa = document.createElement('div');
    capa.className = 'aviso-capa';
    capa.setAttribute('role', 'dialog');
    capa.setAttribute('aria-modal', 'true');
    capa.setAttribute('aria-label', opciones.titulo || 'Aviso de SmashCode');

    var cuerpo = opciones.html
      ? '<div class="aviso-texto">' + opciones.html + '</div>'
      : '<p class="aviso-texto">' + escapar(opciones.mensaje) + '</p>';

    var cabecera = opciones.icono
      ? '<div class="aviso-icono" style="background:' + (opciones.color || 'var(--azul,#1CB0F6)') + '">' +
        '<i class="fas ' + escapar(opciones.icono) + '"></i></div>'
      : '';

    capa.innerHTML =
      '<div class="aviso-modal' + (opciones.html ? ' aviso-modal-izq' : '') + '">' +
        cabecera +
        (opciones.titulo ? '<h3 class="aviso-titulo">' + escapar(opciones.titulo) + '</h3>' : '') +
        cuerpo +
        '<div class="aviso-acciones"><button type="button" class="aviso-btn">' +
          escapar(opciones.boton || 'Entendido') +
        '</button></div>' +
      '</div>';

    function cerrar() {
      capa.classList.remove('visible');
      document.removeEventListener('keydown', alPulsar);
      if (focoAnterior && focoAnterior.isConnected) focoAnterior.focus();
      setTimeout(function () { if (capa.parentNode) capa.parentNode.removeChild(capa); }, 180);
    }
    function alPulsar(e) {
      if (e.key === 'Escape') cerrar();
      if (e.key === 'Tab') {
        var nodos=Array.from(capa.querySelectorAll('a[href],button,input,select,textarea,[tabindex]')).filter(function(n){return !n.disabled && n.tabIndex>=0;});
        var primero=nodos[0],ultimo=nodos[nodos.length-1];
        if(e.shiftKey && document.activeElement===primero){e.preventDefault();ultimo.focus();}
        else if(!e.shiftKey && document.activeElement===ultimo){e.preventDefault();primero.focus();}
      }
    }

    capa.querySelector('.aviso-btn').addEventListener('click', cerrar);
    capa.addEventListener('click', function (e) { if (e.target === capa) cerrar(); });
    document.addEventListener('keydown', alPulsar);

    document.body.appendChild(capa);
    requestAnimationFrame(function () { capa.classList.add('visible'); });
    capa.querySelector('.aviso-btn').focus();
    return cerrar;
  }

  global.Avisos = {
    toast: toast,
    error: function (m) { return toast(m, 'error'); },
    exito: function (m) { return toast(m, 'exito'); },
    info: function (m) { return toast(m, 'info'); },
    dialogo: dialogo
  };
})(window);
