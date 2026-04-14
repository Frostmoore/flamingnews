<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourcesSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            // ── CENTRO-SINISTRA / PROGRESSISTI ───────────────────────────────
            ['domain' => 'repubblica.it',        'name' => 'La Repubblica',           'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'lastampa.it',           'name' => 'La Stampa',               'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'domani.it',             'name' => 'Domani',                  'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'editorialedomani.it',   'name' => 'Domani',                  'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'ilmanifesto.it',        'name' => 'Il Manifesto',            'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'ilfattoquotidiano.it',  'name' => 'Il Fatto Quotidiano',     'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'fanpage.it',            'name' => 'Fanpage',                 'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'huffingtonpost.it',     'name' => 'HuffPost Italia',         'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'linkiesta.it',          'name' => 'Linkiesta',               'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'micromega.net',         'name' => 'MicroMega',               'political_lean' => 'left',          'country' => 'IT'],
            ['domain' => 'unita.it',              'name' => 'L\'Unità',               'political_lean' => 'left',          'country' => 'IT'],

            // ── CENTRO / LIBERALI ─────────────────────────────────────────────
            ['domain' => 'corriere.it',           'name' => 'Corriere della Sera',     'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'ilfoglio.it',           'name' => 'Il Foglio',               'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'sole24ore.com',         'name' => 'Il Sole 24 Ore',          'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'ilsole24ore.com',       'name' => 'Il Sole 24 Ore',          'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'ilmessaggero.it',       'name' => 'Il Messaggero',           'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'lanazione.it',          'name' => 'La Nazione',              'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'ilmattino.it',          'name' => 'Il Mattino',              'political_lean' => 'center',        'country' => 'IT'],

            // ── ALTRO (religiosi, agenzie, lifestyle, sport, aggregatori) ──────
            // Avvenire: CEI — dottrina sociale della Chiesa, non inquadrabile
            ['domain' => 'avvenire.it',           'name' => 'Avvenire',                'political_lean' => 'altro',         'country' => 'IT'],
            // Agenzie di stampa
            ['domain' => 'ansa.it',               'name' => 'ANSA',                    'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'adnkronos.com',         'name' => 'Adnkronos',               'political_lean' => 'altro',         'country' => 'IT'],
            // Televisioni generaliste
            ['domain' => 'rainews.it',            'name' => 'Rai News',                'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'tgcom24.it',            'name' => 'TGCom24',                 'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'mediaset.it',           'name' => 'Mediaset',                'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'skytg24.it',            'name' => 'Sky TG24',                'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'sky.it',                'name' => 'Sky TG24',                'political_lean' => 'altro',         'country' => 'IT'],
            // Indipendenti / non allineati
            ['domain' => 'ilpost.it',             'name' => 'Il Post',                 'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'open.online',           'name' => 'Open',                    'political_lean' => 'altro',         'country' => 'IT'],
            // Aggregatori e tabloid
            ['domain' => 'virgilio.it',           'name' => 'Virgilio News',           'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'leggo.it',              'name' => 'Leggo',                   'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'quotidiano.net',        'name' => 'Quotidiano.net',          'political_lean' => 'altro',         'country' => 'IT'],
            // Lifestyle / finanza
            ['domain' => 'vanityfair.it',         'name' => 'Vanity Fair',             'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'money.it',              'name' => 'Money.it',                'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'milanofinanza.it',      'name' => 'Milano Finanza',          'political_lean' => 'altro',         'country' => 'IT'],
            ['domain' => 'static.milanofinanza.it','name' => 'Milano Finanza',         'political_lean' => 'altro',         'country' => 'IT'],

            // ── CENTRO-DESTRA / CONSERVATORI ──────────────────────────────────
            ['domain' => 'ilgiornale.it',         'name' => 'Il Giornale',             'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'liberoquotidiano.it',   'name' => 'Libero Quotidiano',       'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'libero.it',             'name' => 'Libero',                  'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'laverita.info',         'name' => 'La Verità',               'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'iltempo.it',            'name' => 'Il Tempo',                'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'secoloditalia.it',      'name' => 'Secolo d\'Italia',        'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'lanuovabq.it',          'name' => 'La Nuova BQ',             'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'lindipendente.online',  'name' => 'L\'Indipendente',         'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'panorama.it',           'name' => 'Panorama',                'political_lean' => 'right',         'country' => 'IT'],
            ['domain' => 'affaritaliani.it',      'name' => 'Affari Italiani',         'political_lean' => 'right',         'country' => 'IT'],

            // ── SPORT (nessun orientamento politico rilevante) ────────────────
            ['domain' => 'gazzetta.it',           'name' => 'La Gazzetta dello Sport', 'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'corrieredellosport.it', 'name' => 'Corriere dello Sport',    'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'tuttosport.com',        'name' => 'Tuttosport',              'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'sportmediaset.it',      'name' => 'SportMediaset',           'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'fantacalcio.it',        'name' => 'Fantacalcio',             'political_lean' => 'center',        'country' => 'IT'],
            ['domain' => 'goal.com',              'name' => 'Goal',                    'political_lean' => 'center',        'country' => 'IT'],

            // ── INTERNAZIONALE ────────────────────────────────────────────────
            ['domain' => 'euronews.com',          'name' => 'Euronews',                'political_lean' => 'international', 'country' => 'EU'],
            ['domain' => 'bbc.com',               'name' => 'BBC',                     'political_lean' => 'international', 'country' => 'GB'],
            ['domain' => 'reuters.com',           'name' => 'Reuters',                 'political_lean' => 'international', 'country' => 'GB'],
            ['domain' => 'theguardian.com',       'name' => 'The Guardian',            'political_lean' => 'international', 'country' => 'GB'],
            ['domain' => 'apnews.com',            'name' => 'AP News',                 'political_lean' => 'international', 'country' => 'US'],
            ['domain' => 'bloomberg.com',         'name' => 'Bloomberg',               'political_lean' => 'international', 'country' => 'US'],
            ['domain' => 'nytimes.com',           'name' => 'New York Times',          'political_lean' => 'international', 'country' => 'US'],
        ];

        foreach ($sources as $source) {
            Source::updateOrCreate(
                ['domain' => $source['domain']],
                array_merge($source, ['active' => true])
            );
        }
    }
}
