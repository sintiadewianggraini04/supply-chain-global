<?php

namespace App\Console\Commands;

use App\Services\GNewsService;
use Illuminate\Console\Command;
use Throwable;

class SyncNews extends Command
{
    protected $signature = 'news:sync
                            {query? : Query khusus opsional}
                            {--category=custom : Kategori untuk query khusus}';

    protected $description =
        'Mengambil berita global dari GNews API berdasarkan kategori';

    public function handle(GNewsService $service): int
    {
        $query = $this->argument('query');

        $category = (string) $this->option('category');

        $this->info('Memulai sinkronisasi berita global...');

        try {
            $result = $service->sync(
                $query ? (string) $query : null,
                $category
            );

            $this->newLine();

            $rows = [];

            foreach ($result['categories'] as $categoryName => $total) {
                $rows[] = [
                    ucfirst($categoryName),
                    $total,
                ];
            }

            if ($rows !== []) {
                $this->table(
                    ['Kategori', 'Artikel Diproses'],
                    $rows
                );
            }

            if ($result['errors'] !== []) {
                $this->newLine();

                foreach ($result['errors'] as $categoryName => $message) {
                    $this->warn(
                        "{$categoryName}: {$message}"
                    );
                }
            }

            $this->newLine();

            $this->info(
                "Sinkronisasi selesai. "
                . "{$result['processed']} berita unik diproses."
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