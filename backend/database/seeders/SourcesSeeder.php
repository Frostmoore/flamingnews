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
            // ── SINISTRA ─────────────────────────────────────────────────────
            ['domain' => 'ilmanifesto.it',        'name' => 'Il Manifesto',            'political_lean' => 'left',          'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://ilmanifesto.it/feed/'],
            ['domain' => 'micromega.net',         'name' => 'MicroMega',               'political_lean' => 'left',          'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.micromega.net/feed/'],
            ['domain' => 'unita.it',              'name' => 'L\'Unità',               'political_lean' => 'left',          'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.unita.it/feed/'],

            // ── CENTRO-SINISTRA ───────────────────────────────────────────────
            ['domain' => 'repubblica.it',        'name' => 'La Repubblica',           'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.repubblica.it/rss/homepage/rss2.0.xml'],
            ['domain' => 'ilfattoquotidiano.it',  'name' => 'Il Fatto Quotidiano',     'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.ilfattoquotidiano.it/feed/'],
            ['domain' => 'fanpage.it',            'name' => 'Fanpage',                 'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'editorialedomani.it',   'name' => 'Domani',                  'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://editorialedomani.it/feed'],
            ['domain' => 'huffingtonpost.it',     'name' => 'HuffPost Italia',         'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 2, 'feed_url' => null],
            ['domain' => 'linkiesta.it',          'name' => 'Linkiesta',               'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.linkiesta.it/feed/'],
            ['domain' => 'ilpost.it',             'name' => 'Il Post',                 'political_lean' => 'center-left',   'country' => 'IT', 'tier' => 1, 'feed_url' => null],

            // ── CENTRO ────────────────────────────────────────────────────────
            ['domain' => 'corriere.it',           'name' => 'Corriere della Sera',     'political_lean' => 'center',        'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://xml2.corriereobjects.it/rss/homepage.xml'],
            ['domain' => 'sole24ore.com',         'name' => 'Il Sole 24 Ore',          'political_lean' => 'center',        'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'ilsole24ore.com',       'name' => 'Il Sole 24 Ore',          'political_lean' => 'center',        'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'avvenire.it',           'name' => 'Avvenire',                'political_lean' => 'center',        'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.avvenire.it/rss/'],
            ['domain' => 'open.online',           'name' => 'Open',                    'political_lean' => 'center',        'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.open.online/feed/'],

            // ── CENTRO-DESTRA ─────────────────────────────────────────────────
            ['domain' => 'lastampa.it',           'name' => 'La Stampa',               'political_lean' => 'center-right',  'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.lastampa.it/rss/'],
            ['domain' => 'ilmessaggero.it',       'name' => 'Il Messaggero',           'political_lean' => 'center-right',  'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.ilmessaggero.it/rss/news.xml'],
            ['domain' => 'ilfoglio.it',           'name' => 'Il Foglio',               'political_lean' => 'center-right',  'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.ilfoglio.it/feed/'],
            ['domain' => 'panorama.it',           'name' => 'Panorama',                'political_lean' => 'center-right',  'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.panorama.it/feed'],

            // ── DESTRA ────────────────────────────────────────────────────────
            ['domain' => 'ilgiornale.it',         'name' => 'Il Giornale',             'political_lean' => 'right',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.ilgiornale.it/feed.xml'],
            ['domain' => 'liberoquotidiano.it',   'name' => 'Libero Quotidiano',       'political_lean' => 'right',         'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'libero.it',             'name' => 'Libero',                  'political_lean' => 'right',         'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'laverita.info',         'name' => 'La Verità',               'political_lean' => 'right',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.laverita.info/feeds/feed.rss'],
            ['domain' => 'iltempo.it',            'name' => 'Il Tempo',                'political_lean' => 'right',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.iltempo.it/rss.xml'],
            ['domain' => 'secoloditalia.it',      'name' => 'Secolo d\'Italia',        'political_lean' => 'right',         'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.secoloditalia.it/feed/'],
            ['domain' => 'lanuovabq.it',          'name' => 'La Nuova BQ',             'political_lean' => 'right',         'country' => 'IT', 'tier' => 2, 'feed_url' => null],
            ['domain' => 'lindipendente.online',  'name' => 'L\'Indipendente',         'political_lean' => 'right',         'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.lindipendente.online/feed/'],

            // ── ALTRO (agenzie, TV, lifestyle) ────────────────────────────────
            ['domain' => 'ansa.it',               'name' => 'ANSA',                    'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.ansa.it/sito/notizie/topnews/topnews_rss.xml'],
            ['domain' => 'adnkronos.com',         'name' => 'Adnkronos',               'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'rainews.it',            'name' => 'Rai News',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'skytg24.it',            'name' => 'Sky TG24',                'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://tg24.sky.it/rss/tg24.xml'],
            ['domain' => 'milanofinanza.it',      'name' => 'Milano Finanza',          'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => null],
            ['domain' => 'tgcom24.it',            'name' => 'TGCom24',                 'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.tgcom24.mediaset.it/rss/home.xml'],
            ['domain' => 'quotidiano.net',        'name' => 'Quotidiano.net',          'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.quotidiano.net/rss/'],

            // ── SPORT ─────────────────────────────────────────────────────────
            ['domain' => 'gazzetta.it',           'name' => 'La Gazzetta dello Sport', 'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.gazzetta.it/rss/home.xml'],
            ['domain' => 'corrieredellosport.it', 'name' => 'Corriere dello Sport',    'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.corrieredellosport.it/rss/news.xml'],
            ['domain' => 'tuttosport.com',        'name' => 'Tuttosport',              'political_lean' => 'altro',         'country' => 'IT', 'tier' => 1, 'feed_url' => 'https://www.tuttosport.com/rss/home.xml'],
            ['domain' => 'sportmediaset.it',      'name' => 'SportMediaset',           'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2, 'feed_url' => 'https://www.sportmediaset.mediaset.it/rss/'],
            ['domain' => 'fantacalcio.it',        'name' => 'Fantacalcio',             'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2, 'feed_url' => null],
            ['domain' => 'goal.com',              'name' => 'Goal',                    'political_lean' => 'altro',         'country' => 'IT', 'tier' => 2, 'feed_url' => null],

        ];

        $domains = array_column($sources, 'domain');

        foreach ($sources as $source) {
            Source::updateOrCreate(
                ['domain' => $source['domain']],
                array_merge($source, ['active' => true])
            );
        }

        // Disattiva fonti rimosse dalla lista (non le cancella per preservare gli FK sugli articoli)
        Source::whereNotIn('domain', $domains)->update(['active' => false]);
    }
}
