/* =========================================================
   EXPLORA RD — Chat «Pregúntale a Explora RD» (asistente con IA)
   Envía la conversación a POST /asistente y muestra la respuesta.
   La conversación se guarda solo en esta pestaña (sessionStorage).
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
    const panel = document.getElementById('asistentePanel');
    if (!panel) return;

    const boton = document.getElementById('asistenteBoton');
    const lista = document.getElementById('asistenteMensajes');
    const sugerencias = document.getElementById('asistenteSugerencias');
    const formulario = document.getElementById('asistenteFormulario');
    const campo = document.getElementById('asistentePregunta');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const CLAVE = 'explora-asistente';

    // Abrir y cerrar la ventana (funciona también en el modo «Próximamente»)
    const abrir = (abierto) => {
        panel.hidden = !abierto;
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        boton.classList.toggle('activo', abierto);
        if (abierto && campo) { campo.focus(); lista.scrollTop = lista.scrollHeight; }
    };
    boton.addEventListener('click', () => abrir(panel.hidden));
    document.getElementById('asistenteCerrar').addEventListener('click', () => { abrir(false); boton.focus(); });
    panel.addEventListener('keydown', (e) => { if (e.key === 'Escape') { abrir(false); boton.focus(); } });

    // Modo «Próximamente» (todavía sin clave de la IA): no hay chat, solo el aviso con WhatsApp
    if (!formulario) return;

    // Conversación: [{role: 'user'|'assistant', content: '...'}]
    let conversacion = [];
    try { conversacion = JSON.parse(sessionStorage.getItem(CLAVE) || '[]'); } catch (e) { conversacion = []; }
    const guardar = () => { try { sessionStorage.setItem(CLAVE, JSON.stringify(conversacion.slice(-20))); } catch (e) { /* sin almacenamiento: no pasa nada */ } };

    // Muestra el texto de forma segura: escapa el HTML y convierte los enlaces en enlaces de verdad
    const escribir = (elemento, texto) => {
        elemento.textContent = '';
        const partes = String(texto).split(/(https?:\/\/[^\s)]+)/g);
        partes.forEach((parte, i) => {
            if (i % 2 === 0) { elemento.append(parte); return; }   // texto normal
            const limpio = parte.replace(/[.,;:!?]+$/, '');        // sin el punto final de la oración
            let url;
            try { url = new URL(limpio); } catch (e) { elemento.append(parte); return; }
            const enlace = document.createElement('a');
            enlace.href = url.href;
            const propio = url.origin === location.origin;
            // Enlaces de Explora RD: se muestra solo la ruta (/excursiones/...); los demás, el sitio y la ruta
            enlace.textContent = (propio ? '' : url.host) + url.pathname.replace(/\/$/, '');
            if (!propio) { enlace.target = '_blank'; enlace.rel = 'noopener'; }
            elemento.append(enlace, parte.slice(limpio.length));
        });
    };

    const agregar = (rol, texto, clase = '') => {
        const burbuja = document.createElement('div');
        burbuja.className = 'asistente-mensaje ' + (rol === 'user' ? 'asistente-turista' : 'asistente-ia') + (clase ? ' ' + clase : '');
        escribir(burbuja, texto);
        lista.appendChild(burbuja);
        lista.scrollTop = lista.scrollHeight;
        return burbuja;
    };

    // Al abrir la página, se vuelve a mostrar la conversación de esta pestaña
    conversacion.forEach(m => agregar(m.role, m.content));
    if (conversacion.length) sugerencias.hidden = true;

    document.getElementById('asistenteLimpiar').addEventListener('click', () => {
        conversacion = [];
        guardar();
        lista.querySelectorAll('.asistente-mensaje:not(:first-child)').forEach(m => m.remove());
        sugerencias.hidden = false;
        campo.focus();
    });

    // Enter envía; Shift + Enter hace un salto de línea
    campo.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); formulario.requestSubmit(); }
    });

    let enviando = false;
    const preguntar = async (pregunta) => {
        pregunta = pregunta.trim();
        if (!pregunta || enviando) return;
        enviando = true;
        sugerencias.hidden = true;
        campo.value = '';

        agregar('user', pregunta);
        conversacion.push({ role: 'user', content: pregunta });
        const esperando = agregar('assistant', panel.dataset.textoEsperando, 'asistente-esperando');

        try {
            const respuesta = await fetch(panel.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ mensajes: conversacion }),
            });
            esperando.remove();

            if (respuesta.status === 429) {
                conversacion.pop();
                agregar('assistant', panel.dataset.textoMuchas, 'asistente-alerta');
            } else {
                const datos = await respuesta.json();
                const texto = datos.respuesta || datos.message || panel.dataset.textoError;
                if (respuesta.ok && datos.disponible !== false && !datos.error) {
                    agregar('assistant', texto);
                    conversacion.push({ role: 'assistant', content: texto });
                } else {
                    // Sin IA o con error: se avisa y se ofrece WhatsApp, sin guardarlo en la conversación
                    conversacion.pop();
                    agregar('assistant', texto + ' ' + panel.dataset.whatsapp, 'asistente-alerta');
                }
            }
        } catch (error) {
            esperando.remove();
            conversacion.pop();
            agregar('assistant', panel.dataset.textoError, 'asistente-alerta');
        } finally {
            guardar();
            enviando = false;
            campo.focus();
        }
    };

    formulario.addEventListener('submit', (e) => { e.preventDefault(); preguntar(campo.value); });
    sugerencias.querySelectorAll('.asistente-sugerencia').forEach(s => s.addEventListener('click', () => preguntar(s.textContent)));
});
