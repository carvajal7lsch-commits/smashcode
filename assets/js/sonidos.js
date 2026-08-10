/**
 * sonidos.js — Sistema de Efectos de Sonido para SmashCode
 * Utiliza Web Audio API para generar efectos sintetizados limpios, rápidos y sin dependencias externas.
 * Respeta la preferencia configurada en el perfil ('smashcode_sonido').
 */
const SonidosApp = (function() {
  let audioCtx = null;

  function obtenerAudioContext() {
    if (!audioCtx) {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (AudioContext) {
        audioCtx = new AudioContext();
      }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
    return audioCtx;
  }

  function estaHabilitado() {
    const val = localStorage.getItem('smashcode_sonido');
    return val !== '0'; // Habilitado por defecto si es null o '1'
  }

  function setHabilitado(habilitar) {
    localStorage.setItem('smashcode_sonido', habilitar ? '1' : '0');
  }

  /**
   * Tono de Acierto / Respuesta Correcta (Chime Duolingo C5 -> G5)
   */
  function playCorrect() {
    if (!estaHabilitado()) return;
    const ctx = obtenerAudioContext();
    if (!ctx) return;

    const ahora = ctx.currentTime;
    
    // Nota 1: C5 (523.25 Hz)
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'triangle';
    osc1.frequency.setValueAtTime(523.25, ahora);
    gain1.gain.setValueAtTime(0.25, ahora);
    gain1.gain.exponentialRampToValueAtTime(0.001, ahora + 0.18);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(ahora);
    osc1.stop(ahora + 0.18);

    // Nota 2: G5 (783.99 Hz)
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'triangle';
    osc2.frequency.setValueAtTime(783.99, ahora + 0.1);
    gain2.gain.setValueAtTime(0.28, ahora + 0.1);
    gain2.gain.exponentialRampToValueAtTime(0.001, ahora + 0.38);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(ahora + 0.1);
    osc2.stop(ahora + 0.38);
  }

  /**
   * Tono de Error / Respuesta Incorrecta (Buzzer suave F3 -> D3)
   */
  function playIncorrect() {
    if (!estaHabilitado()) return;
    const ctx = obtenerAudioContext();
    if (!ctx) return;

    const ahora = ctx.currentTime;

    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(220, ahora); // A3
    osc.frequency.exponentialRampToValueAtTime(146.83, ahora + 0.28); // D3
    gain.gain.setValueAtTime(0.2, ahora);
    gain.gain.exponentialRampToValueAtTime(0.001, ahora + 0.28);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(ahora);
    osc.stop(ahora + 0.28);
  }

  /**
   * Fanfarria de Victoria / Quiz Aprobado (C5 -> E5 -> G5 -> C6)
   */
  function playVictory() {
    if (!estaHabilitado()) return;
    const ctx = obtenerAudioContext();
    if (!ctx) return;

    const notas = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
    const duracion = 0.14;
    let t = ctx.currentTime;

    notas.forEach((freq, idx) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      const delay = idx * 0.11;
      const largo = (idx === notas.length - 1) ? 0.45 : duracion;

      osc.type = 'triangle';
      osc.frequency.setValueAtTime(freq, t + delay);
      gain.gain.setValueAtTime(0.25, t + delay);
      gain.gain.exponentialRampToValueAtTime(0.001, t + delay + largo);

      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(t + delay);
      osc.stop(t + delay + largo);
    });
  }

  /**
   * Sonido al encender/apagar el toggle de sonido
   */
  function playToggle(encendido) {
    const ctx = obtenerAudioContext();
    if (!ctx) return;

    const ahora = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    
    if (encendido) {
      osc.frequency.setValueAtTime(440, ahora);
      osc.frequency.exponentialRampToValueAtTime(880, ahora + 0.12);
    } else {
      osc.frequency.setValueAtTime(660, ahora);
      osc.frequency.exponentialRampToValueAtTime(330, ahora + 0.12);
    }

    gain.gain.setValueAtTime(0.18, ahora);
    gain.gain.exponentialRampToValueAtTime(0.001, ahora + 0.12);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(ahora);
    osc.stop(ahora + 0.12);
  }

  return {
    estaHabilitado,
    setHabilitado,
    playCorrect,
    playIncorrect,
    playVictory,
    playToggle
  };
})();

// Exportar globalmente
window.SonidosApp = SonidosApp;
