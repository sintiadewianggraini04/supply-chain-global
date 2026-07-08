<?php

namespace App\Console\Commands;

use App\Services\GNewsService;
use Illuminate\Console\Command;
use Throwable;

class SyncNews extends Command
{
    protected $signature = 'news:sync {query?}';

    protected $description = 'Mengambil berita supply chain dari GNews API';

    public function handle(GNewsService $service): int
    {
        $query = $this->argument('query');

        $this->info('Memulai sinkronisasi berita...');

        try {
            $total = $service->sync($query);

            $this->newLine();

            $this->info("Sinkronisasi selesai. {$total} berita diproses.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();

            $this->error('Sinkronisasi gagal: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }
}