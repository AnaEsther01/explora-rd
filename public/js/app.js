/* =========================================================
   EXPLORA RD — Interactividad
   Los textos vienen traducidos en window.TEXTOS (resources/views/partials/textos-js.blade.php)
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
    const T = window.TEXTOS || {};

    // ---------- Botón del ojo: mostrar u ocultar la contraseña ----------
    document.querySelectorAll('.ver-contrasena').forEach(boton => {
        boton.addEventListener('click', () => {
            const campo = document.getElementById(boton.dataset.objetivo);
            const visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            boton.querySelector('i').className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
            boton.setAttribute('aria-label', visible ? (T.mostrar || 'Mostrar contraseña') : (T.ocultar || 'Ocultar contraseña'));
        });
    });

    // ---------- Registro: elegir «Soy turista» o «Soy agencia» ----------
    const campoRol = document.getElementById('campoRol');
    if (campoRol) {
        document.querySelectorAll('input[name="rol_visual"]').forEach(opcion => {
            opcion.addEventListener('change', () => {
                const esAgencia = opcion.value === 'agencia';
                campoRol.value = opcion.value;
                document.querySelectorAll('.campos-agencia').forEach(c => c.classList.toggle('d-none', !esAgencia));
                document.getElementById('etiquetaNombre').textContent = esAgencia ? T.responsable : T.nombreCompleto;
                document.getElementById('botonRegistro').textContent = esAgencia ? T.registrarAgencia : T.crearCuenta;
            });
        });
    }

    // ---------- Admin: mostrar los datos de agencia solo si el rol es «Agencia» ----------
    const rolAdmin = document.getElementById('rolAdmin');
    if (rolAdmin) {
        rolAdmin.addEventListener('change', () => {
            document.querySelectorAll('.campos-agencia-admin').forEach(c => c.classList.toggle('d-none', rolAdmin.value !== 'agencia'));
        });
    }

    // ---------- Vista previa de la foto antes de subirla ----------
    document.querySelectorAll('input[type="file"][data-vista]').forEach(campo => {
        campo.addEventListener('change', () => {
            const archivo = campo.files[0];
            if (!archivo) return;
            const vista = document.getElementById(campo.dataset.vista);
            vista.src = URL.createObjectURL(archivo);
            vista.classList.remove('d-none');
            // Esconde el texto «Sin foto» o las iniciales que había antes
            document.querySelectorAll('#' + campo.dataset.vista + 'Vacia, #vistaLogoIniciales').forEach(e => e.classList.add('d-none'));
        });
    });

    // ---------- Contador de letras en los textos largos ----------
    document.querySelectorAll('textarea[data-contador]').forEach(texto => {
        const contador = document.getElementById(texto.dataset.contador);
        const actualizar = () => { contador.textContent = texto.value.length; };
        texto.addEventListener('input', actualizar);
        actualizar();
    });

    // Los mensajes de éxito se cierran solos después de 6 segundos
    document.querySelectorAll('.alerta-exito').forEach(alerta => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 6000);
    });

    // ---------- Favoritos: el corazón ♥ guarda o quita sin recargar la página ----------
    const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.content;
    document.querySelectorAll('.form-favorito').forEach(formulario => {
        formulario.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            const boton = formulario.querySelector('.btn-favorito');
            boton.disabled = true;
            try {
                const respuesta = await fetch(formulario.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': tokenCsrf, 'Accept': 'application/json' },
                });
                if (!respuesta.ok) throw new Error(respuesta.status);
                const datos = await respuesta.json();

                // La misma excursión puede salir dos veces en la página: se actualizan todos sus corazones
                document.querySelectorAll('.form-favorito[action="' + formulario.action + '"] .btn-favorito').forEach(b => {
                    b.classList.toggle('activo', datos.favorita);
                    b.setAttribute('aria-pressed', datos.favorita ? 'true' : 'false');
                    const titulo = datos.favorita ? T.quitarFavorito : T.guardarFavorito;
                    b.title = titulo;
                    b.setAttribute('aria-label', titulo);
                    b.querySelector('i').className = datos.favorita ? 'bi bi-heart-fill' : 'bi bi-heart';
                    const texto = b.querySelector('span');
                    if (texto) texto.textContent = datos.favorita ? b.dataset.textoGuardada : b.dataset.textoGuardar;
                    if (datos.favorita) { b.classList.remove('latido'); void b.offsetWidth; b.classList.add('latido'); }
                });
            } catch (error) {
                formulario.submit(); // si algo falla, se envía de la forma normal
            } finally {
                boton.disabled = false;
            }
        });
    });

    // Abre WhatsApp con un mensaje ya escrito
    const abrirWhatsapp = (numero, mensaje) =>
        window.open('https://wa.me/' + numero + '?text=' + encodeURIComponent(mensaje), '_blank', 'noopener');

    // ---------- Menú: se vuelve blanco al bajar por la página ----------
    const menu = document.getElementById('menu');
    if (!menu) return; // las páginas de Entrar y Registro no tienen este menú
    const menuEnlaces = document.getElementById('menuEnlaces');

    const actualizarMenu = () => {
        const abierto = menuEnlaces && menuEnlaces.classList.contains('show');
        menu.classList.toggle('menu-solido', window.scrollY > 60 || abierto);
    };
    window.addEventListener('scroll', actualizarMenu);
    window.addEventListener('load', actualizarMenu); // por si la página abre más abajo (ej.: …#solicitar)
    if (menuEnlaces) {
        menuEnlaces.addEventListener('show.bs.collapse', () => menu.classList.add('menu-solido'));
        menuEnlaces.addEventListener('hidden.bs.collapse', actualizarMenu);
        // En el celular: cerrar el menú al tocar un enlace
        menuEnlaces.querySelectorAll('.nav-link').forEach(enlace => {
            enlace.addEventListener('click', () => {
                const colapso = bootstrap.Collapse.getInstance(menuEnlaces);
                if (colapso) colapso.hide();
            });
        });
    }
    actualizarMenu();

    // ---------- Pestañas del buscador: cada una cambia los textos ----------
    let categoriaActual = 'experiencias';
    const pestanas = document.querySelectorAll('.pestana');
    pestanas.forEach(pestana => {
        pestana.addEventListener('click', () => {
            pestanas.forEach(p => { p.classList.remove('active'); p.setAttribute('aria-selected', 'false'); });
            pestana.classList.add('active');
            pestana.setAttribute('aria-selected', 'true');

            categoriaActual = pestana.dataset.categoria;
            const datos = (T.pestanas || {})[categoriaActual];
            if (datos) {
                document.getElementById('etiquetaDestino').textContent = datos.destino;
                document.getElementById('campoDestino').placeholder = datos.ayuda;
                document.getElementById('etiquetaFecha').textContent = datos.fecha;
                document.getElementById('etiquetaPersonas').textContent = datos.personas;
            }

            // «Circuitos y paquetes» filtra el catálogo por tipo
            const campoTipo = document.getElementById('campoTipo');
            campoTipo.value = categoriaActual === 'circuitos' ? 'circuitos' : '';
            campoTipo.disabled = categoriaActual !== 'circuitos';
        });
    });

    // ---------- Buscador de la portada ----------
    const buscador = document.getElementById('buscador');
    if (buscador) {
        buscador.addEventListener('submit', (evento) => {
            const destino = document.getElementById('campoDestino').value.trim();
            const fecha = document.getElementById('campoFecha').value;
            const personas = document.getElementById('campoPersonas').value;

            // Asesorías y renta de vehículos: van a su página con el formulario ya lleno
            if (categoriaActual === 'asesorias' || categoriaActual === 'vehiculos') {
                evento.preventDefault();
                const url = categoriaActual === 'asesorias' ? buscador.dataset.urlAsesorias : buscador.dataset.urlVehiculos;
                const datos = new URLSearchParams();
                if (destino) datos.set('lugar', destino);
                if (fecha) datos.set('fecha', fecha);
                datos.set('personas', personas);
                window.location.href = url + '?' + datos.toString() + '#solicitar';
                return;
            }

            // Experiencias y circuitos: no enviamos los campos vacíos para que la dirección quede limpia
            ['campoDestino', 'campoFecha'].forEach(id => {
                const campo = document.getElementById(id);
                if (!campo.value.trim()) campo.disabled = true;
            });
            setTimeout(() => ['campoDestino', 'campoFecha'].forEach(id => { document.getElementById(id).disabled = false; }), 0);
        });
    }

    // ---------- Catálogo: filtro de precio ----------
    const filtroPrecio = document.getElementById('filtroPrecio');
    if (filtroPrecio) {
        const salida = document.getElementById('valorPrecio');
        const formatear = v => Number(v) >= Number(filtroPrecio.max) ? T.cualquiera : 'RD$ ' + Number(v).toLocaleString('en-US');
        filtroPrecio.addEventListener('input', () => { salida.textContent = formatear(filtroPrecio.value); });
        // Si el precio está al máximo, no se envía (significa «cualquier precio»)
        filtroPrecio.form.addEventListener('submit', () => {
            if (Number(filtroPrecio.value) >= Number(filtroPrecio.max)) filtroPrecio.disabled = true;
            setTimeout(() => { filtroPrecio.disabled = false; }, 0);
        });
        salida.textContent = formatear(filtroPrecio.value);
    }

    // ---------- Ficha: total estimado y consulta por WhatsApp ----------
    const formConsulta = document.getElementById('formConsulta');
    if (formConsulta) {
        const selectPersonas = document.getElementById('consultaPersonas');
        const campoFecha = document.getElementById('consultaFecha');
        const total = document.getElementById('totalEstimado');
        const precio = Number(selectPersonas.dataset.precio);

        selectPersonas.addEventListener('change', () => {
            total.textContent = 'RD$ ' + (precio * Number(selectPersonas.value)).toLocaleString('en-US', { maximumFractionDigits: 0 });
        });

        // «Solicitar reserva» envía el formulario; «Consultar por WhatsApp» abre WhatsApp
        document.getElementById('btnConsultaWhatsapp').addEventListener('click', () => {
            let mensaje = T.consulta.replace(':titulo', formConsulta.dataset.titulo);
            if (campoFecha.value) mensaje += ' ' + T.fecha + ': ' + campoFecha.value + '.';
            mensaje += ' ' + T.personas + ': ' + selectPersonas.value + '.';
            abrirWhatsapp(formConsulta.dataset.whatsapp, mensaje);
        });
    }
});
