/* =========================================================
   EXPLORA RD — Galería: filtro y visor de fotos en grande
   Usa la ventana (modal) de Bootstrap; no necesita otras librerías.
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
    const mosaico = document.getElementById('mosaicoGaleria');
    if (!mosaico) return;

    const T = window.TEXTOS_GALERIA || {};
    const piezas = [...mosaico.querySelectorAll('.mosaico-pieza')];
    const contador = document.getElementById('contadorGaleria');

    // ---------- Filtro: Todas / Playa / Montaña / Ciudad / Agencias ----------
    const botonesFiltro = document.querySelectorAll('[data-filtro]');
    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', () => {
            const filtro = boton.dataset.filtro;
            botonesFiltro.forEach(b => {
                b.classList.toggle('activa', b === boton);
                b.setAttribute('aria-pressed', b === boton ? 'true' : 'false');
            });
            let visibles = 0;
            piezas.forEach(p => {
                const mostrar = filtro === 'todas' || p.dataset.filtros.split(' ').includes(filtro);
                p.hidden = !mostrar;
                if (mostrar) visibles++;
            });
            contador.textContent = contador.dataset.plantilla.replace(':count', visibles);
        });
    });

    // ---------- Visor en grande ----------
    const modal = document.getElementById('visorGaleria');
    const visor = bootstrap.Modal.getOrCreateInstance(modal);
    const imagen = document.getElementById('visorImagen');
    const titulo = document.getElementById('visorTitulo');
    const lugar = document.getElementById('visorLugar');
    const credito = document.getElementById('visorCredito');
    const enlace = document.getElementById('visorEnlace');
    const numero = document.getElementById('visorContador');
    let lista = [];   // las fotos visibles con el filtro actual
    let actual = 0;

    const mostrar = (indice) => {
        actual = (indice + lista.length) % lista.length; // al pasar la última, vuelve a la primera
        const d = lista[actual].dataset;

        imagen.classList.add('cambiando');
        imagen.onload = () => imagen.classList.remove('cambiando');
        imagen.src = d.src;
        imagen.alt = d.titulo + (d.lugar ? ', ' + d.lugar : '');
        titulo.textContent = d.titulo;
        lugar.textContent = d.lugar || '';
        enlace.href = d.enlace;
        enlace.textContent = d.textoEnlace;
        numero.textContent = (actual + 1) + ' / ' + lista.length;

        // Crédito del autor (si está anotado en img/CREDITOS.txt)
        credito.textContent = '';
        if (d.autor) {
            credito.append((T.foto || 'Foto') + ': ' + d.autor + ' · ');
            const a = document.createElement('a');
            a.href = d.urlCredito;
            a.target = '_blank';
            a.rel = 'noopener';
            a.textContent = d.fuente;
            credito.append(a);
        }

        // Precarga la siguiente para que pase rápido
        const siguiente = lista[(actual + 1) % lista.length];
        if (siguiente) new Image().src = siguiente.dataset.src;
    };

    piezas.forEach(pieza => {
        pieza.addEventListener('click', () => {
            lista = piezas.filter(p => !p.hidden);
            mostrar(lista.indexOf(pieza));
            visor.show();
        });
    });

    document.getElementById('visorAnterior').addEventListener('click', () => mostrar(actual - 1));
    document.getElementById('visorSiguiente').addEventListener('click', () => mostrar(actual + 1));

    // Flechas del teclado
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') mostrar(actual - 1);
        if (e.key === 'ArrowRight') mostrar(actual + 1);
    });

    // Deslizar con el dedo en el celular
    let inicioX = null;
    imagen.addEventListener('touchstart', (e) => { inicioX = e.touches[0].clientX; }, { passive: true });
    imagen.addEventListener('touchend', (e) => {
        if (inicioX === null) return;
        const distancia = e.changedTouches[0].clientX - inicioX;
        if (Math.abs(distancia) > 50) mostrar(actual + (distancia < 0 ? 1 : -1));
        inicioX = null;
    });

    // Al cerrar, vacía la imagen para que no se vea la anterior la próxima vez
    modal.addEventListener('hidden.bs.modal', () => { imagen.src = ''; });
});
