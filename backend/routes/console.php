<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:bizpilot', function (): void {
    $this->info('BizPilot AI backend is ready for module scaffolding.');
})->purpose('Display BizPilot AI backend information');
