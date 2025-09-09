<?php

namespace App\Console\Commands;

use App\Models\Ruche;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateQrTokens extends Command
{
    protected $signature = 'ruches:generate-qr-tokens';
    protected $description = 'Generate QR tokens for existing ruches';

    public function handle()
    {
        $ruches = Ruche::whereNull('qr_token')->get();
        
        foreach ($ruches as $ruche) {
            $ruche->qr_token = Str::random(32);
            $ruche->save();
        }
        
        $this->info("QR tokens generated for {$ruches->count()} ruches");
    }
}