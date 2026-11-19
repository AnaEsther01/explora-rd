# 🐍 Python en Explora RD — Reporte de estadísticas

Esta carpeta tiene la **función complementaria en Python** del proyecto: un programa que crea un
**reporte con gráficas** para el administrador.

## ¿Cómo funciona?

```
 Base de datos (MySQL)
        │
        ▼
 Laravel: php artisan explora:reporte
   app/Support/Estadisticas.php  → cuenta reservas, reseñas, favoritos...
        │  guarda
        ▼
 storage/app/reportes/datos.json
        │  lee
        ▼
 Python: python/reporte.py
   matplotlib dibuja las gráficas y el programa escribe las conclusiones
        │  guarda
        ▼
 storage/app/reportes/reporte.html  →  Panel del administrador → «Reporte (Python)»
```

**¿Por qué Laravel prepara los datos y no Python directamente?** Así Python no necesita conectarse
a MySQL (ni instalar un conector), y los números se calculan con las mismas reglas que usa la página
(por ejemplo, las reservas canceladas y las reseñas ocultas no cuentan).

El reporte se guarda en `storage/` (privado), no en `public/`: **solo el administrador lo puede ver**.

## Qué tiene el reporte

- Tarjetas con los totales: reservas, personas, ingresos estimados, excursiones, turistas, agencias, estrellas y favoritos.
- **Conclusiones automáticas** («La excursión más reservada es…», «El mes con más viajes es…»).
- 5 gráficas: viajes por mes, reservas por estado, excursiones más reservadas, mejor valoradas e interés por destino.
- Tabla con el resumen de cada agencia y las solicitudes de asesoría y renta de vehículos.

## Cómo usarlo

1. Desde el panel del administrador: **Reporte (Python)** → «Generar reporte».
2. O desde la terminal, en la carpeta del proyecto:
   ```bash
   php artisan explora:reporte
   ```

## Qué necesita

- **Python 3.10 o más reciente** (en esta computadora: Python 3.14).
- **matplotlib** (ya estaba instalado). Si en otra computadora falta: `pip install matplotlib`
- Si el programa de Python no se llama `python` (por ejemplo, `python3` en un servidor), se cambia en `.env`:
  ```
  EXPLORA_PYTHON=python3
  ```

## Pruebas

```bash
python -m unittest discover -s python -v
```

Son 8 pruebas: formato del dinero, conclusiones, gráficas, nombres con símbolos raros, plataforma sin datos
y lectura y guardado de archivos. Laravel tiene además sus propias pruebas del reporte en
`tests/Feature/ReporteTest.php`.

## Archivos

| Archivo | Para qué sirve |
|---|---|
| `reporte.py` | El programa: lee los datos, dibuja las gráficas y crea la página |
| `test_reporte.py` | Pruebas automáticas del programa (con `unittest`, que ya viene con Python) |
| `LEEME.md` | Esta explicación |
