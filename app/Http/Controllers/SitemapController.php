<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Usuario;

/**
 * /sitemap.xml — lista de todas las páginas públicas para buscadores como Google.
 * Ayuda a que Explora RD aparezca en las búsquedas.
 *
 * El XML se arma aquí mismo (y no en una vista Blade) porque la primera línea «<?xml»
 * se confunde con código PHP cuando el servidor tiene activado short_open_tag.
 */
class SitemapController extends Controller
{
    public function __invoke()
    {
        $paginas = collect([
            ['url' => route('inicio'), 'prioridad' => '1.0'],
            ['url' => route('destinos.index'), 'prioridad' => '0.9'],
            ['url' => route('excursiones.index'), 'prioridad' => '0.9'],
            ['url' => route('agencias.index'), 'prioridad' => '0.7'],
            ['url' => route('galeria'), 'prioridad' => '0.6'],
            ['url' => route('servicios.asesorias'), 'prioridad' => '0.7'],
            ['url' => route('servicios.vehiculos'), 'prioridad' => '0.7'],
        ]);

        foreach (Destino::all() as $destino) {
            $paginas->push(['url' => route('destinos.show', $destino), 'prioridad' => '0.8', 'fecha' => $destino->updated_at]);
        }
        foreach (Excursion::publicas()->get() as $excursion) {
            $paginas->push(['url' => route('excursiones.show', $excursion), 'prioridad' => '0.8', 'fecha' => $excursion->updated_at]);
        }
        foreach (Usuario::agenciasPublicas()->get() as $agencia) {
            $paginas->push(['url' => route('agencias.show', $agencia), 'prioridad' => '0.6', 'fecha' => $agencia->updated_at]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($paginas as $pagina) {
            $xml .= "  <url>\n    <loc>".e($pagina['url'])."</loc>\n";
            if (! empty($pagina['fecha'])) {
                $xml .= '    <lastmod>'.$pagina['fecha']->toAtomString()."</lastmod>\n";
            }
            $xml .= '    <priority>'.$pagina['prioridad']."</priority>\n  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
