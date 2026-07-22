<?php

namespace App\Console\Commands;

use App\Models\Port;
use App\Services\WorldPortIndexService;
use Illuminate\Console\Command;
use Throwable;

class SyncPorts extends Command
{
    /**
     * Nama command yang dijalankan di terminal.
     */
    protected $signature = 'ports:sync';

    /**
     * Keterangan command.
     */
    protected $description =
        'Mengambil dan menyimpan data pelabuhan global dari World Port Index';

    /**
     * Menjalankan sinkronisasi pelabuhan.
     */
    public function handle(
        WorldPortIndexService $worldPortIndexService
    ): int {
        $this->info(
            'Memulai sinkronisasi data pelabuhan global...'
        );

        try {
            $result =
                $worldPortIndexService->fetchAllPorts();

            $ports = $result['ports'] ?? [];

            $countryNames =
                $result['country_names'] ?? [];

            if (empty($ports)) {
                $this->error(
                    'Tidak ada data pelabuhan yang diterima.'
                );

                return self::FAILURE;
            }

            $rows = [];
            $now = now();

            foreach ($ports as $port) {
                $name = trim(
                    (string) ($port['PORT_NAME'] ?? '')
                );

                $countryCode = strtoupper(
                    trim((string) ($port['COUNTRY'] ?? ''))
                );

                $latitude =
                    $port['LATITUDE'] ?? null;

                $longitude =
                    $port['LONGITUDE'] ?? null;

                if (
                    $name === '' ||
                    $countryCode === '' ||
                    ! is_numeric($latitude) ||
                    ! is_numeric($longitude)
                ) {
                    continue;
                }

                $indexNumber =
                    $port['INDEX_NO'] ?? null;

                $harborSize = trim(
                    (string) ($port['HARBORSIZE'] ?? '')
                );

                $harborType = trim(
                    (string) ($port['HARBORTYPE'] ?? '')
                );

                $notes = [];

                if ($harborSize !== '') {
                    $notes[] =
                        'Harbor size: '.$harborSize;
                }

                if ($harborType !== '') {
                    $notes[] =
                        'Harbor type: '.$harborType;
                }

                $key = strtolower($name)
                    .'|'
                    .$countryCode;

                $rows[$key] = [
                    'name' => $name,

                    'country_name' =>
                        $countryNames[$countryCode]
                        ?? $countryCode,

                    'country_code' => $countryCode,

                    'port_code' => is_numeric($indexNumber)
                        ? (string) (int) $indexNumber
                        : null,

                    'port_type' => 'seaport',

                    'latitude' => (float) $latitude,

                    'longitude' => (float) $longitude,

                    'congestion_level' => 0,

                    'risk_level' => 'low',

                    'notes' => empty($notes)
                        ? null
                        : implode(' | ', $notes),

                    'source' =>
                        'World Port Index ArcGIS',

                    'created_at' => $now,

                    'updated_at' => $now,
                ];
            }

            $rows = array_values($rows);

            if (empty($rows)) {
                $this->error(
                    'Semua data pelabuhan tidak valid.'
                );

                return self::FAILURE;
            }

            $bar = $this->output->createProgressBar(
                count($rows)
            );

            $bar->start();

            foreach (array_chunk($rows, 500) as $chunk) {
                Port::upsert(
                    $chunk,
                    [
                        'name',
                        'country_code',
                    ],
                    [
                        'country_name',
                        'port_code',
                        'port_type',
                        'latitude',
                        'longitude',
                        'notes',
                        'source',
                        'updated_at',
                    ]
                );

                $bar->advance(count($chunk));
            }

            $bar->finish();

            $this->newLine(2);

            $this->info(
                count($rows)
                .' data pelabuhan berhasil diproses.'
            );

            $this->info(
                'Total pelabuhan di database: '
                .Port::count()
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->newLine();

            $this->error(
                'Sinkronisasi pelabuhan gagal: '
                .$exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}