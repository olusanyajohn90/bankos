<?php

namespace App\Console\Commands;

use App\Services\Nip\NipService;
use Illuminate\Console\Command;

class SeedBankList extends Command
{
    protected $signature   = 'bankos:seed-bank-list';
    protected $description = 'Seed the bank_list table with Nigerian banks (CBN codes). Safe to re-run — uses upsert.';

    public function handle(NipService $nipService): int
    {
        $this->info('Seeding bank list…');
        $nipService->seedBankList();
        $this->info('Bank list seeded successfully.');
        return self::SUCCESS;
    }
}
