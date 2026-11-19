"""
EXPLORA RD — Reporte de estadísticas (función complementaria en Python)

Qué hace:
  1. Lee el archivo datos.json que prepara Laravel (php artisan explora:reporte).
  2. Dibuja las gráficas con matplotlib, con los colores de Explora RD.
  3. Escribe conclusiones automáticas (por ejemplo, cuál es la excursión más reservada).
  4. Guarda todo en una sola página HTML (las gráficas van dentro de la página).

Cómo se usa (desde la carpeta del proyecto):
  python python/reporte.py --datos storage/app/reportes/datos.json --salida storage/app/reportes/reporte.html

Normalmente no hace falta escribirlo: lo ejecuta «php artisan explora:reporte»
o el botón «Generar reporte» del panel del administrador.

Necesita: Python 3.10 o más reciente y matplotlib (pip install matplotlib).
"""

import argparse
import base64
import html
import io
import json
import sys
from pathlib import Path

import matplotlib

matplotlib.use("Agg")  # dibuja sin abrir ventanas (sirve también en un servidor)
import matplotlib.pyplot as plt  # noqa: E402

# Colores de Explora RD (los mismos de la página)
TURQUESA = "#0E8A92"
AZUL = "#0B3C49"
ARENA = "#F1E3C6"
NARANJA = "#D9822B"
VERDE = "#3E8E4F"
ROJO = "#C0504D"
GRIS = "#5B6E73"

plt.rcParams.update({
    "font.family": "sans-serif",
    "font.size": 10,
    "axes.edgecolor": "#CFDADC",
    "axes.labelcolor": AZUL,
    "axes.titleweight": "bold",
    "axes.titlecolor": AZUL,
    "xtick.color": GRIS,
    "ytick.color": GRIS,
    "axes.spines.top": False,
    "axes.spines.right": False,
})


# ---------------------------------------------------------------- ayudas

def dinero(valor):
    """1234567.5 → «RD$ 1,234,568»"""
    return "RD$ {:,.0f}".format(valor or 0)


def acortar(texto, largo=32):
    """Corta los títulos largos para que quepan en la gráfica."""
    return texto if len(texto) <= largo else texto[: largo - 1] + "…"


def figura_a_base64(fig):
    """Convierte una gráfica en una imagen PNG escrita como texto (para meterla dentro del HTML)."""
    memoria = io.BytesIO()
    fig.savefig(memoria, format="png", dpi=130, bbox_inches="tight", facecolor="white")
    plt.close(fig)
    return base64.b64encode(memoria.getvalue()).decode("ascii")


def poner_numeros(ax, barras, horizontal=False, formato="{:g}"):
    """Escribe el número al final de cada barra."""
    for barra in barras:
        valor = barra.get_width() if horizontal else barra.get_height()
        if not valor:
            continue
        if horizontal:
            ax.text(valor, barra.get_y() + barra.get_height() / 2, " " + formato.format(valor),
                    va="center", ha="left", fontsize=9, color=AZUL)
        else:
            ax.text(barra.get_x() + barra.get_width() / 2, valor, formato.format(valor),
                    va="bottom", ha="center", fontsize=9, color=AZUL)


# ---------------------------------------------------------------- gráficas

def grafica_reservas_por_mes(meses):
    fig, ax = plt.subplots(figsize=(8, 3.4))
    nombres = [m["mes"] for m in meses]
    valores = [m["reservas"] for m in meses]
    barras = ax.bar(nombres, valores, color=TURQUESA, width=0.65)
    poner_numeros(ax, barras)
    ax.set_title("Viajes reservados por mes", loc="left")
    ax.set_ylabel("Reservas")
    ax.yaxis.get_major_locator().set_params(integer=True)
    ax.tick_params(axis="x", rotation=45)
    return figura_a_base64(fig)


def grafica_barras_horizontales(filas, campo, titulo, color, formato="{:g}", maximo=None):
    fig, ax = plt.subplots(figsize=(5.2, 0.55 * max(len(filas), 2) + 0.9))
    nombres = [acortar(f["titulo"]) for f in filas][::-1]  # la primera queda arriba
    valores = [f[campo] for f in filas][::-1]
    barras = ax.barh(nombres, valores, color=color, height=0.6)
    poner_numeros(ax, barras, horizontal=True, formato=formato)
    ax.set_title(titulo, loc="left", fontsize=13)
    ax.tick_params(axis="y", labelsize=11)
    if maximo:
        ax.set_xlim(0, maximo)
    else:
        ax.set_xlim(0, max(valores + [1]) * 1.18)
        ax.xaxis.get_major_locator().set_params(integer=True)
    return figura_a_base64(fig)


def grafica_estados(estados):
    etiquetas = {"pendiente": "Pendientes", "confirmada": "Confirmadas", "cancelada": "Canceladas"}
    colores = {"pendiente": NARANJA, "confirmada": TURQUESA, "cancelada": ROJO}
    claves = [c for c in estados if estados[c] > 0]
    fig, ax = plt.subplots(figsize=(5.2, 3.4))
    ax.pie(
        [estados[c] for c in claves],
        labels=[f"{etiquetas.get(c, c)} ({estados[c]})" for c in claves],
        colors=[colores.get(c, GRIS) for c in claves],
        startangle=90, counterclock=False,
        wedgeprops={"width": 0.42, "edgecolor": "white", "linewidth": 2},
        textprops={"color": AZUL, "fontsize": 9},
    )
    ax.set_title("Reservas por estado", loc="left")
    return figura_a_base64(fig)


def grafica_destinos(destinos):
    filas = destinos[:8]
    fig, ax = plt.subplots(figsize=(5.2, 0.55 * max(len(filas), 2) + 0.9))
    nombres = [d["nombre"] for d in filas][::-1]
    reservas = [d["reservas"] for d in filas][::-1]
    favoritos = [d["favoritos"] for d in filas][::-1]
    b1 = ax.barh(nombres, reservas, color=TURQUESA, height=0.6, label="Reservas")
    b2 = ax.barh(nombres, favoritos, left=reservas, color=NARANJA, height=0.6, label="Favoritos")
    for barra_r, barra_f, r, f in zip(b1, b2, reservas, favoritos):
        ax.text(r + f, barra_r.get_y() + barra_r.get_height() / 2, f" {r + f}", va="center", fontsize=9, color=AZUL)
    ax.set_title("Interés por destino (reservas + favoritos)", loc="left", fontsize=13)
    ax.tick_params(axis="y", labelsize=11)
    ax.set_xlim(0, max([r + f for r, f in zip(reservas, favoritos)] + [1]) * 1.18)
    ax.xaxis.get_major_locator().set_params(integer=True)
    ax.legend(frameon=False, loc="lower right", fontsize=9)
    return figura_a_base64(fig)


# ---------------------------------------------------------------- conclusiones

def conclusiones(datos):
    """Frases que resumen lo más importante (las «escribe» el programa con los números)."""
    frases = []
    t = datos["totales"]

    if datos["mas_reservadas"]:
        top = datos["mas_reservadas"][0]
        frases.append(f"La excursión más reservada es «{top['titulo']}» de {top['agencia']}, "
                      f"con {top['reservas']} reservas y {top['personas']} personas.")

    if datos["mejor_valoradas"]:
        mejor = datos["mejor_valoradas"][0]
        frases.append(f"La mejor valorada es «{mejor['titulo']}», con {mejor['promedio']} de 5 estrellas "
                      f"({mejor['resenas']} {'reseña' if mejor['resenas'] == 1 else 'reseñas'}).")

    meses_con_viajes = [m for m in datos["reservas_por_mes"] if m["reservas"]]
    if meses_con_viajes:
        pico = max(meses_con_viajes, key=lambda m: m["reservas"])
        frases.append(f"El mes con más viajes es {pico['mes']} ({pico['reservas']} reservas, {pico['personas']} personas).")

    total = sum(datos["reservas_por_estado"].values())
    if total:
        pendientes = datos["reservas_por_estado"].get("pendiente", 0)
        confirmadas = datos["reservas_por_estado"].get("confirmada", 0)
        frases.append(f"El {round(confirmadas * 100 / total)} % de las reservas está confirmado"
                      + (f" y quedan {pendientes} por responder." if pendientes else " y no hay reservas por responder."))

    if t["solicitudes_pendientes"]:
        frases.append(f"Hay {t['solicitudes_pendientes']} "
                      f"{'solicitud' if t['solicitudes_pendientes'] == 1 else 'solicitudes'} de asesoría o vehículo esperando respuesta.")

    if datos["destinos"]:
        favorito = max(datos["destinos"], key=lambda d: d["reservas"] + d["favoritos"])
        frases.append(f"El destino que más interesa es {favorito['nombre']}.")

    if not frases:
        frases.append("Todavía no hay suficientes reservas para sacar conclusiones.")
    return frases


# ---------------------------------------------------------------- página HTML

def generar_html(datos):
    e = html.escape
    t = datos["totales"]

    tarjetas = [
        ("Reservas", t["reservas"]),
        ("Personas que viajan", t["personas"]),
        ("Ingresos estimados (confirmadas)", dinero(t["ingresos_estimados"])),
        ("Excursiones publicadas", t["excursiones"]),
        ("Turistas registrados", t["turistas"]),
        ("Agencias activas", t["agencias"]),
        ("Promedio de estrellas", f"{t['promedio_estrellas']} ★" if t["resenas"] else "—"),
        ("Guardadas en favoritos", t["favoritos"]),
    ]
    html_tarjetas = "".join(f'<div class="tarjeta"><span>{e(n)}</span><strong>{e(str(v))}</strong></div>' for n, v in tarjetas)

    # Orden pensado para dos columnas: meses arriba (ancha), luego de dos en dos
    graficas = [f'<img class="ancha" src="data:image/png;base64,{grafica_reservas_por_mes(datos["reservas_por_mes"])}" alt="Viajes reservados por mes">']
    if sum(datos["reservas_por_estado"].values()):
        graficas.append(f'<img src="data:image/png;base64,{grafica_estados(datos["reservas_por_estado"])}" alt="Reservas por estado">')
    if datos["destinos"]:
        graficas.append(f'<img src="data:image/png;base64,{grafica_destinos(datos["destinos"])}" alt="Interés por destino">')
    if datos["mas_reservadas"]:
        graficas.append(f'<img src="data:image/png;base64,{grafica_barras_horizontales(datos["mas_reservadas"], "reservas", "Excursiones más reservadas", TURQUESA)}" alt="Excursiones más reservadas">')
    if datos["mejor_valoradas"]:
        graficas.append(f'<img src="data:image/png;base64,{grafica_barras_horizontales(datos["mejor_valoradas"], "promedio", "Mejor valoradas (estrellas)", NARANJA, "{:.1f}", 5.6)}" alt="Excursiones mejor valoradas">')

    filas_agencias = "".join(
        "<tr><td>{}</td><td>{}</td><td>{}</td><td>{}</td><td>{}</td><td>{}</td></tr>".format(
            e(a["nombre"]), a["excursiones"], a["reservas"], a["confirmadas"], dinero(a["ingresos_estimados"]),
            f"{a['promedio_estrellas']} ★" if a["promedio_estrellas"] is not None else "—")
        for a in datos["agencias"]
    ) or '<tr><td colspan="6">Todavía no hay agencias activas.</td></tr>'

    servicios = " · ".join(f"{e(nombre)}: <strong>{n}</strong>" for nombre, n in datos["solicitudes_por_servicio"].items())
    lista_conclusiones = "".join(f"<li>{e(f)}</li>" for f in conclusiones(datos))

    return f"""<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reporte de Explora RD — {e(datos['generado'])}</title>
<style>
  body {{ font-family: Poppins, 'Segoe UI', Arial, sans-serif; color: #1B2B30; background: #F3F8F8; margin: 0; padding: 24px; }}
  .hoja {{ max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 6px 24px rgba(11,60,73,.08); }}
  h1 {{ font-family: Lora, Georgia, serif; color: {AZUL}; margin: 0; font-size: 28px; }}
  h2 {{ font-family: Lora, Georgia, serif; color: {AZUL}; font-size: 20px; margin: 28px 0 12px; }}
  .sub {{ color: {GRIS}; margin: 4px 0 0; font-size: 14px; }}
  .marca {{ display: inline-block; background: {TURQUESA}; color: #fff; border-radius: 50px; padding: 3px 12px; font-size: 12px; font-weight: 600; margin-bottom: 8px; }}
  .tarjetas {{ display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-top: 20px; }}
  .tarjeta {{ background: #F3F8F8; border-radius: 12px; padding: 14px; }}
  .tarjeta span {{ display: block; font-size: 12px; color: {GRIS}; }}
  .tarjeta strong {{ font-size: 22px; color: {AZUL}; }}
  .conclusiones {{ background: {ARENA}; border-radius: 12px; padding: 14px 18px 14px 36px; margin: 0; }}
  .conclusiones li {{ margin: 6px 0; }}
  .graficas {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 16px; align-items: start; }}
  @media (max-width: 520px) {{ .graficas {{ grid-template-columns: 1fr; }} }}
  .graficas img {{ width: 100%; height: auto; border: 1px solid #E4ECEE; border-radius: 12px; }}
  .graficas img.ancha {{ grid-column: 1 / -1; }}
  table {{ width: 100%; border-collapse: collapse; font-size: 14px; }}
  th, td {{ text-align: left; padding: 9px 10px; border-bottom: 1px solid #E4ECEE; }}
  th {{ background: {AZUL}; color: #fff; font-weight: 600; }}
  .tabla {{ overflow-x: auto; }}
  .pie {{ color: {GRIS}; font-size: 12px; margin-top: 24px; }}
  @media print {{ body {{ background: #fff; padding: 0; }} .hoja {{ box-shadow: none; }} }}
</style>
</head>
<body>
<div class="hoja">
  <span class="marca">Explora RD · Reporte</span>
  <h1>Estadísticas de la plataforma</h1>
  <p class="sub">Generado el {e(datos['generado'])} con Python y matplotlib.</p>

  <div class="tarjetas">{html_tarjetas}</div>

  <h2>Conclusiones</h2>
  <ul class="conclusiones">{lista_conclusiones}</ul>

  <h2>Gráficas</h2>
  <div class="graficas">{''.join(graficas)}</div>

  <h2>Agencias</h2>
  <div class="tabla"><table>
    <thead><tr><th>Agencia</th><th>Excursiones</th><th>Reservas</th><th>Confirmadas</th><th>Ingresos estimados</th><th>Estrellas</th></tr></thead>
    <tbody>{filas_agencias}</tbody>
  </table></div>

  <h2>Asesorías y renta de vehículos</h2>
  <p>{servicios} · Pendientes: <strong>{t['solicitudes_pendientes']}</strong></p>

  <p class="pie">Los ingresos son estimados (precio por persona × personas de las reservas confirmadas): el pago se acuerda con cada agencia.
  Las reservas canceladas no se cuentan en las gráficas.</p>
</div>
</body>
</html>
"""


# ---------------------------------------------------------------- programa principal

def main(argumentos=None):
    carpeta = Path(__file__).resolve().parent.parent / "storage" / "app" / "reportes"
    lector = argparse.ArgumentParser(description="Crea el reporte de estadísticas de Explora RD.")
    lector.add_argument("--datos", default=str(carpeta / "datos.json"), help="archivo JSON que prepara Laravel")
    lector.add_argument("--salida", default=str(carpeta / "reporte.html"), help="dónde guardar la página del reporte")
    args = lector.parse_args(argumentos)

    try:
        datos = json.loads(Path(args.datos).read_text(encoding="utf-8"))
    except FileNotFoundError:
        print(f"No encontré {args.datos}. Primero ejecuta: php artisan explora:reporte", file=sys.stderr)
        return 1

    Path(args.salida).parent.mkdir(parents=True, exist_ok=True)
    Path(args.salida).write_text(generar_html(datos), encoding="utf-8")
    print(f"Reporte creado en {args.salida}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
