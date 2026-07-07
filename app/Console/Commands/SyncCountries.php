<?php

namespace App\Console\Commands;

use App\Services\RestCountriesService;
use Illuminate\Console\Command;
use Throwable;

class SyncCountries extends Command
{
    /**
     * Nama command yang dijalankan di terminal.
     */
    protected $signature = 'countries:sync';

    /**
     * Deskripsi command.
     */
    protected $description =
        'Mengambil data negara dari REST Countries API';

    /**
     * Menjalankan proses sinkronisasi.
     */
    public function handle(
        RestCountriesService $service
    ): int {
        $this->info(
            'Memulai sinkronisasi data negara...'
        );

        try {
            $total = $service->sync();

            $this->newLine();

            $this->info(
                "Sinkronisasi selesai. {$total} negara diproses."
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();

            $this->error(
                'Sinkronisasi gagal: '
                . $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}