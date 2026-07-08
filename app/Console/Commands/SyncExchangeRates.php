<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;
use Throwable;

class SyncExchangeRates extends Command
{
    protected $signature = 'currency:sync {base=USD}';

    protected $description = 'Mengambil data kurs dari ExchangeRate API';

    public function handle(ExchangeRateService $service): int
    {
        $baseCurrency = strtoupper((string) $this->argument('base'));

        $this->info("Memulai sinkronisasi kurs {$baseCurrency}...");

        try {
            $total = $service->sync($baseCurrency);

            $this->newLine();

            $this->info(
                "Sinkronisasi selesai. {$total} kurs diproses."
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();

            $this->error(
                'Sinkronisasi gagal: ' . $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}