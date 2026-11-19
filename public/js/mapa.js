/* =========================================================
   EXPLORA RD — Mapa de destinos
   Usa Leaflet (librería de mapas gratis) con los mapas de OpenStreetMap.
   Cada <div class="mapa-explora"> trae sus destinos en data-destinos (JSON),
   preparado en resources/views/partials/mapa.blade.php
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
    if (typeof L === 'undefined') return; // si Leaflet no cargó (sin internet), se queda el aviso

    // Ícono de cada tipo de destino (iconos de Bootstrap)
    const ICONOS = { playa: 'bi-water', 'montaña': 'bi-tree-fill', ciudad: 'bi-buildings-fill' };

    // Escapa el texto para meterlo en el globo sin riesgo
    const seguro = texto => String(texto ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    document.querySelectorAll('.mapa-explora').forEach(contenedor => {
        const destinos = JSON.parse(contenedor.dataset.destinos || '[]');
        if (!destinos.length) return;

        contenedor.innerHTML = '';
        const mapa = L.map(contenedor, {
            scrollWheelZoom: false, // la rueda del ratón baja la página, no acerca el mapa
        });

        // Los «cuadritos» del mapa vienen de OpenStreetMap (hay que mencionar su autoría)
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
        }).addTo(mapa);

        const puntos = [];
        destinos.forEach(d => {
            // Marcador redondo turquesa con el ícono del tipo de destino
            const icono = L.divIcon({
                className: 'marcador-explora marcador-' + (d.tipo === 'montaña' ? 'montana' : d.tipo),
                html: '<i class="bi ' + (ICONOS[d.tipo] || 'bi-geo-alt-fill') + '"></i>',
                iconSize: [38, 38],
                iconAnchor: [19, 19],
                popupAnchor: [0, -18],
            });

            // Globo que se abre al tocar el marcador
            const foto = d.foto ? '<img src="' + seguro(d.foto) + '" alt="" loading="lazy">' : '';
            const globo =
                '<div class="globo-mapa">' + foto +
                '<div class="globo-mapa-texto">' +
                '<small>' + seguro(d.tipoNombre) + ' · ' + seguro(d.provincia) + '</small>' +
                '<strong>' + seguro(d.nombre) + '</strong>' +
                (destinos.length > 1 ? '<a href="' + seguro(d.url) + '">' + seguro(contenedor.dataset.textoVer) + ' →</a>' : '') +
                '</div></div>';

            L.marker([d.lat, d.lng], { icon: icono, title: d.nombre, alt: d.nombre })
                .bindPopup(globo, { maxWidth: 230, minWidth: 200 })
                .addTo(mapa);
            puntos.push([d.lat, d.lng]);
        });

        // Encuadra el mapa para que se vean todos los destinos
        const encuadrar = () => {
            if (puntos.length === 1) {
                mapa.setView(puntos[0], Number(contenedor.dataset.zoom) || 10);
            } else {
                mapa.fitBounds(puntos, { padding: [40, 40], maxZoom: 12 });
            }
        };
        encuadrar();

        // Si el mapa cambia de tamaño (girar el celular, abrir una pestaña oculta...),
        // se vuelve a encuadrar, a menos que la persona ya lo haya movido ella misma.
        let loMovioLaPersona = false;
        ['pointerdown', 'wheel', 'keydown'].forEach(e => contenedor.addEventListener(e, () => { loMovioLaPersona = true; }));
        if ('ResizeObserver' in window) {
            new ResizeObserver(() => {
                if (!contenedor.offsetWidth) return;
                mapa.invalidateSize();
                if (!loMovioLaPersona) encuadrar();
            }).observe(contenedor);
        }
    });
});
