"""
Pruebas del reporte de Python (usa unittest, que ya viene con Python).

Cómo se ejecutan (desde la carpeta del proyecto):
    python -m unittest discover -s python -v
"""

import json
import tempfile
import unittest
from pathlib import Path

import reporte


def datos_de_ejemplo():
    """Datos parecidos a los que prepara Laravel en datos.json."""
    return {
        "generado": "19/11/2026 10:00 AM",
        "totales": {
            "turistas": 2, "agencias": 2, "destinos": 12, "excursiones": 9, "reservas": 5, "personas": 12,
            "ingresos_estimados": 35000, "solicitudes": 2, "solicitudes_pendientes": 1,
            "resenas": 3, "promedio_estrellas": 4.33, "favoritos": 2,
        },
        "reservas_por_estado": {"pendiente": 1, "confirmada": 3, "cancelada": 1},
        "reservas_por_mes": [
            {"mes": "oct 2026", "reservas": 1, "personas": 2},
            {"mes": "nov 2026", "reservas": 3, "personas": 8},
            {"mes": "dic 2026", "reservas": 0, "personas": 0},
        ],
        "mas_reservadas": [
            {"titulo": "Catamarán a isla Saona", "agencia": "Brisa Tropical Tours", "reservas": 3, "personas": 8},
            {"titulo": "Rafting en el río Yaque del Norte", "agencia": "Aventuras Jarabacoa", "reservas": 1, "personas": 2},
        ],
        "mejor_valoradas": [
            {"titulo": "Experiencia de playa en Samaná", "agencia": "Brisa Tropical Tours", "promedio": 5.0, "resenas": 1},
        ],
        "destinos": [
            {"nombre": "Punta Cana", "tipo": "playa", "favoritos": 1, "reservas": 3},
            {"nombre": "Jarabacoa", "tipo": "montaña", "favoritos": 1, "reservas": 1},
        ],
        "agencias": [
            {"nombre": "Brisa Tropical Tours", "excursiones": 5, "reservas": 3, "confirmadas": 2,
             "ingresos_estimados": 35000, "promedio_estrellas": 4.5},
            {"nombre": "Aventuras <Jarabacoa>", "excursiones": 4, "reservas": 1, "confirmadas": 1,
             "ingresos_estimados": 0, "promedio_estrellas": None},
        ],
        "solicitudes_por_servicio": {"Asesoría de viaje": 1, "Renta de vehículo": 1},
    }


def datos_vacios():
    """Una plataforma recién publicada: sin reservas, reseñas ni favoritos."""
    datos = datos_de_ejemplo()
    datos["totales"].update({"reservas": 0, "personas": 0, "ingresos_estimados": 0, "resenas": 0,
                             "promedio_estrellas": 0, "favoritos": 0, "solicitudes_pendientes": 0})
    datos.update({
        "reservas_por_estado": {"pendiente": 0, "confirmada": 0, "cancelada": 0},
        "reservas_por_mes": [{"mes": "nov 2026", "reservas": 0, "personas": 0}],
        "mas_reservadas": [], "mejor_valoradas": [], "destinos": [], "agencias": [],
    })
    return datos


class PruebasReporte(unittest.TestCase):

    def test_formato_de_dinero(self):
        self.assertEqual(reporte.dinero(1234567.4), "RD$ 1,234,567")
        self.assertEqual(reporte.dinero(None), "RD$ 0")

    def test_acortar_titulos_largos(self):
        self.assertEqual(reporte.acortar("Corto"), "Corto")
        self.assertEqual(len(reporte.acortar("x" * 50, 20)), 20)

    def test_conclusiones_con_los_numeros_correctos(self):
        frases = " ".join(reporte.conclusiones(datos_de_ejemplo()))
        self.assertIn("«Catamarán a isla Saona»", frases)
        self.assertIn("5.0 de 5 estrellas (1 reseña)", frases)
        self.assertIn("nov 2026 (3 reservas, 8 personas)", frases)
        self.assertIn("El 60 % de las reservas", frases)       # 3 de 5
        self.assertIn("1 solicitud de asesoría", frases)
        self.assertIn("El destino que más interesa es Punta Cana", frases)

    def test_la_pagina_tiene_graficas_tarjetas_y_tabla(self):
        pagina = reporte.generar_html(datos_de_ejemplo())
        self.assertIn("Estadísticas de la plataforma", pagina)
        self.assertEqual(pagina.count('src="data:image/png;base64,'), 5)  # 5 gráficas
        self.assertIn("RD$ 35,000", pagina)
        self.assertIn("4.33 ★", pagina)

    def test_escapa_los_nombres_para_no_romper_la_pagina(self):
        pagina = reporte.generar_html(datos_de_ejemplo())
        self.assertIn("Aventuras &lt;Jarabacoa&gt;", pagina)
        self.assertNotIn("Aventuras <Jarabacoa>", pagina)

    def test_funciona_sin_datos(self):
        pagina = reporte.generar_html(datos_vacios())
        self.assertIn("Todavía no hay suficientes reservas", pagina)
        self.assertEqual(pagina.count('src="data:image/png;base64,'), 1)  # solo la de meses

    def test_el_programa_lee_el_json_y_guarda_el_html(self):
        with tempfile.TemporaryDirectory() as carpeta:
            entrada = Path(carpeta) / "datos.json"
            salida = Path(carpeta) / "sub" / "reporte.html"
            entrada.write_text(json.dumps(datos_de_ejemplo()), encoding="utf-8")

            self.assertEqual(reporte.main(["--datos", str(entrada), "--salida", str(salida)]), 0)
            self.assertTrue(salida.exists())

    def test_avisa_si_falta_el_archivo_de_datos(self):
        with tempfile.TemporaryDirectory() as carpeta:
            self.assertEqual(reporte.main(["--datos", str(Path(carpeta) / "no-existe.json")]), 1)


if __name__ == "__main__":
    unittest.main()
