<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Pruebas de los idiomas: español (por defecto), inglés y francés.
 */
class IdiomasTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_por_defecto_la_pagina_esta_en_espanol(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSee('Descubre República Dominicana');
    }

    public function test_se_puede_cambiar_a_ingles_y_se_recuerda(): void
    {
        $this->from('/destinos')->get('/idioma/en')->assertRedirect('/destinos');

        $this->get('/')
            ->assertSee('<html lang="en">', false)
            ->assertSee('Discover the Dominican Republic')
            ->assertSee('Experiences you will love')
            ->assertDontSee('Experiencias que enamoran');

        // La siguiente página sigue en inglés (el idioma se guarda en la sesión)
        $this->get('/excursiones')->assertSee('Apply filters')->assertSee('Catamaran');
    }

    public function test_en_frances_se_traducen_el_menu_las_paginas_y_los_destinos(): void
    {
        $this->get('/idioma/fr');

        $this->get('/')->assertSee('Découvrez la République dominicaine')->assertSee('Connexion');
        $this->get('/login')->assertSee('Mot de passe oublié ?', false);

        // El contenido del destino también sale en francés
        $this->get('/destinos/jarabacoa')
            ->assertSee('Que faire à Jarabacoa')
            ->assertSee('Rafting sur la rivière Yaque del Norte')
            ->assertSee('Parapente au-dessus de la vallée')
            ->assertDontSee('Parapente sobre el valle'); // (los títulos de las excursiones los escribe la agencia en español)
    }

    public function test_un_idioma_que_no_existe_se_ignora(): void
    {
        $this->get('/idioma/de');
        $this->get('/')->assertSee('<html lang="es">', false);
    }

    public function test_los_errores_de_los_formularios_salen_en_el_idioma_elegido(): void
    {
        $this->get('/idioma/en');
        $this->post('/login', ['correo' => '', 'contrasena' => ''])
            ->assertSessionHasErrors(['correo' => 'Please enter your email.']);

        $this->get('/idioma/fr');
        $this->post('/login', ['correo' => 'maria@correo.test', 'contrasena' => 'mala'])
            ->assertSessionHasErrors(['correo' => "L'e-mail ou le mot de passe est incorrect."]);
    }

    public function test_la_ficha_avisa_que_la_descripcion_de_la_agencia_esta_en_espanol(): void
    {
        $this->get('/idioma/en');

        $this->get('/excursiones/catamaran-a-isla-saona')
            ->assertSee('Log in to book') // sin sesión iniciada
            ->assertSee('Estimated total')
            ->assertSee('The agency wrote this description in Spanish.');

        // Con sesión de turista, el botón es «Request booking»
        $this->actingAs(Usuario::where('correo', 'maria@correo.test')->firstOrFail())
            ->get('/excursiones/catamaran-a-isla-saona')
            ->assertSee('Request booking');
    }

    public function test_los_paneles_de_agencia_siguen_funcionando_en_otro_idioma(): void
    {
        $this->get('/idioma/fr');
        $agencia = Usuario::where('correo', 'info@brisatropical.test')->firstOrFail();

        $this->actingAs($agencia)->get('/agencia/panel')->assertOk()->assertSee('Mon espace', false);
    }

    /**
     * Revisa que TODAS las frases marcadas con __('...') en las páginas públicas
     * tengan su traducción en lang/en.json y lang/fr.json.
     * Si alguien agrega una frase nueva y se olvida de traducirla, esta prueba falla y dice cuál.
     */
    public function test_todas_las_frases_tienen_traduccion_en_ingles_y_frances(): void
    {
        $excluir = ['/views/agencia/', '/views/admin/', '/views/layouts/agencia', '/views/layouts/admin',
            '/views/panel/agencia', '/views/panel/admin', '/views/welcome', '/Controllers/Admin/', '/Controllers/Agencia/'];

        $claves = [];
        foreach ([resource_path('views'), app_path()] as $carpeta) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($carpeta)) as $archivo) {
                $ruta = str_replace('\\', '/', $archivo->getPathname());
                if (! $archivo->isFile() || ! str_ends_with($ruta, '.php')) {
                    continue;
                }
                foreach ($excluir as $x) {
                    if (str_contains($ruta, $x)) {
                        continue 2;
                    }
                }
                preg_match_all("/(?:__|trans_choice)\(\s*'((?:[^'\\\\]|\\\\.)*)'/u", file_get_contents($ruta), $m);
                foreach ($m[1] as $clave) {
                    $claves[stripslashes($clave)] = $ruta;
                }
            }
        }
        unset($claves['...'], $claves['']); // «__('...')» aparece en un comentario de ejemplo

        foreach (['en', 'fr'] as $idioma) {
            $traducciones = json_decode(file_get_contents(lang_path("$idioma.json")), true);
            $this->assertIsArray($traducciones, "lang/$idioma.json no es un JSON válido");
            $faltan = array_diff(array_keys($claves), array_keys($traducciones));
            $this->assertSame([], array_values($faltan), "Faltan traducciones en lang/$idioma.json");
        }
    }
}
