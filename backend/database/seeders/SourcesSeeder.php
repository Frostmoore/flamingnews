<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourcesSeeder extends Seeder
{
    public function run(): void
    {
        // tier 1 = testata nazionale principale
        // tier 2 = regionale, niche, aggregatore, internazionale
        $sources = [
            // ── CENTRO-SINISTRA / PROGRESSISTI ───────────────────────────────
            ['domain' => 'repubblica.it',        'name' => 'La Repubblica',           'political_lean' => 'left',          'country' => 'IT', 'tier' => 1],
            ['domain' => 'lastampa.it',           'name' => 'La Stampa',               'political_lean' => 'left',          'country' => 'IT', 'tier' => 1],
            ['domain' => 'ilfattoquotidiano.it',  'name' => 'Il Fatto Quotidiano',     'political_lean' => 'left',          'country' => 'IT', 'tier' => 1],
            ['domain' => 'fanpage.it',            'name' => 'Fanpage',                 'political_lean' => 'left',          'country' => 'IT', 'tier' => 1],
            ['domain' => 'domani.it',             'name' => 'Domani',                  'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],
            ['domain' => 'editorialedomani.it',   'name' => 'Domani',                  'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],
            ['domain' => 'ilmanifesto.it',        'name' => 'Il Manifesto',            'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],
            ['domain' => 'huffingtonpost.it',     'name' => 'HuffPost Italia',         'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],
            ['domain' => 'linkiesta.it',          'name' => 'Linkiesta',               'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],
            ['domain' => 'micromega.net',         'name' => 'MicroMega',               'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],
            ['domain' => 'unita.it',              'name' => 'L\'Unità',               'political_lean' => 'left',          'country' => 'IT', 'tier' => 2],

            // ── CENTRO / LIBERALI ─────────────────────────────────────────────
            ['domain' => 'corriere.it',           'name' => 'Corriere della Sera',     'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'sole24ore.com',         'name' => 'Il Sole 24 Ore',          'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'ilsole24ore.com',       'name' => 'Il Sole 24 Ore',          'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'ilmessaggero.it',       'name' => 'Il Messaggero',           'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'ilfoglio.it',           'name' => 'Il Foglio',               'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'lanazione.it',          'name' => 'La Nazione',              'political_lean' => 'center',        'country' => 'IT', 'tier' => 2],
            ['domain' => 'ilmattino.it',          'name' => 'Il Mattino',              'political_lean' => 'center',        'country' => 'IT', 'tier' => 2],

            // ── ALTRO (agenzie, TV, lifestyle, aggregatori) ───────────────────
            ['domain' => 'avvenire.it',           'name' => 'Avvenire',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'ansa.it',               'name' => 'ANSA',                    'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'adnkronos.com',         'name' => 'Adnkronos',               'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'rainews.it',            'name' => 'Rai News',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'skytg24.it',            'name' => 'Sky TG24',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'ilpost.it',             'name' => 'Il Post',                 'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'milanofinanza.it',      'name' => 'Milano Finanza',          'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'tgcom24.it',            'name' => 'TGCom24',                 'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'mediaset.it',           'name' => 'Mediaset',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'sky.it',                'name' => 'Sky TG24',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'open.online',           'name' => 'Open',                    'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'virgilio.it',           'name' => 'Virgilio News',           'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'leggo.it',              'name' => 'Leggo',                   'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'quotidiano.net',        'name' => 'Quotidiano.net',          'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'vanityfair.it',         'name' => 'Vanity Fair',             'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'money.it',              'name' => 'Money.it',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'static.milanofinanza.it','name' => 'Milano Finanza',         'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2],

            // ── CENTRO-DESTRA / CONSERVATORI ──────────────────────────────────
            ['domain' => 'ilgiornale.it',         'name' => 'Il Giornale',             'political_lean' => 'right',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'liberoquotidiano.it',   'name' => 'Libero Quotidiano',       'political_lean' => 'right',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'libero.it',             'name' => 'Libero',                  'political_lean' => 'right',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'laverita.info',         'name' => 'La Verità',               'political_lean' => 'right',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'iltempo.it',            'name' => 'Il Tempo',                'political_lean' => 'right',         'country' => 'IT', 'tier' => 1],
            ['domain' => 'secoloditalia.it',      'name' => 'Secolo d\'Italia',        'political_lean' => 'right',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'lanuovabq.it',          'name' => 'La Nuova BQ',             'political_lean' => 'right',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'lindipendente.online',  'name' => 'L\'Indipendente',         'political_lean' => 'right',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'panorama.it',           'name' => 'Panorama',                'political_lean' => 'right',         'country' => 'IT', 'tier' => 2],
            ['domain' => 'affaritaliani.it',      'name' => 'Affari Italiani',         'political_lean' => 'right',         'country' => 'IT', 'tier' => 2],

            // ── SPORT ─────────────────────────────────────────────────────────
            ['domain' => 'gazzetta.it',           'name' => 'La Gazzetta dello Sport', 'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'corrieredellosport.it', 'name' => 'Corriere dello Sport',    'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'tuttosport.com',        'name' => 'Tuttosport',              'political_lean' => 'center',        'country' => 'IT', 'tier' => 1],
            ['domain' => 'sportmediaset.it',      'name' => 'SportMediaset',           'political_lean' => 'center',        'country' => 'IT', 'tier' => 2],
            ['domain' => 'fantacalcio.it',        'name' => 'Fantacalcio',             'political_lean' => 'center',        'country' => 'IT', 'tier' => 2],
            ['domain' => 'goal.com',              'name' => 'Goal',                    'political_lean' => 'center',        'country' => 'IT', 'tier' => 2],

            // ── INTERNAZIONALE ────────────────────────────────────────────────
            ['domain' => 'euronews.com',          'name' => 'Euronews',                'political_lean' => 'international', 'country' => 'EU', 'tier' => 2],
            ['domain' => 'bbc.com',               'name' => 'BBC',                     'political_lean' => 'international', 'country' => 'GB', 'tier' => 2],
            ['domain' => 'reuters.com',           'name' => 'Reuters',                 'political_lean' => 'international', 'country' => 'GB', 'tier' => 2],
            ['domain' => 'theguardian.com',       'name' => 'The Guardian',            'political_lean' => 'international', 'country' => 'GB', 'tier' => 2],
            ['domain' => 'apnews.com',            'name' => 'AP News',                 'political_lean' => 'international', 'country' => 'US', 'tier' => 2],
            ['domain' => 'bloomberg.com',         'name' => 'Bloomberg',               'political_lean' => 'international', 'country' => 'US', 'tier' => 2],
            ['domain' => 'nytimes.com',           'name' => 'New York Times',          'political_lean' => 'international', 'country' => 'US', 'tier' => 2],
        ];

        foreach ($sources as $source) {
            Source::updateOrCreate(
                ['domain' => $source['domain']],
                array_merge($source, ['active' => true])
            );
        }
    }
}
