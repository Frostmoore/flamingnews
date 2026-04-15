<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class FetchPrimePagineCommand extends Command
{
    protected $signature   = 'primepagine:fetch';
    protected $description = 'Scarica le prime pagine dei principali quotidiani italiani';

    public function handle(): int
    {
        $this->info('Avvio FetchPrimePagineJob...');
        (new \App\Jobs\FetchPrimePagineJob())->handle();
        $this->info('Fatto.');
        return self::SUCCESS;
    }
}
