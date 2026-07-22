<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WorldPortIndexService
{
    private const LAYER_URL =
        'https://services9.arcgis.com/j1CY4yzWfwptbTWN/arcgis/rest/services/WorldPortIndex_WFL1/FeatureServer/0';

    private const PAGE_SIZE = 2000;

    /**
     * Mengambil seluruh pelabuhan dari World Port Index.
     */
    public function fetchAllPorts(): array
    {
        $ports = [];
        $offset = 0;

        do {
            $payload = $this->request(
                self::LAYER_URL.'/query',
                [
                    'where' => '1=1',

                    'outFields' => implode(',', [
                        'OBJECTID',
                        'INDEX_NO',
                        'PORT_NAME',
                        'COUNTRY',
                        'LATITUDE',
                        'LONGITUDE',
                        'HARBORSIZE',
                        'HARBORTYPE',
                    ]),

                    'returnGeometry' => 'false',
                    'orderByFields' => 'OBJECTID ASC',
                    'resultOffset' => $offset,
                    'resultRecordCount' => self::PAGE_SIZE,
                    'f' => 'json',
                ]
            );

            $features = $payload['features'] ?? [];

            foreach ($features as $feature) {
                $attributes = $feature['attributes'] ?? [];

                $name = trim(
                    (string) ($attributes['PORT_NAME'] ?? '')
                );

                $latitude = $attributes['LATITUDE'] ?? null;
                $longitude = $attributes['LONGITUDE'] ?? null;

                if (
                    $name === '' ||
                    ! is_numeric($latitude) ||
                    ! is_numeric($longitude)
                ) {
                    continue;
                }

                $ports[] = $attributes;
            }

            $offset += count($features);

            $hasMore = (bool) (
                $payload['exceededTransferLimit'] ?? false
            );
        } while ($hasMore && count($features) > 0);

        return [
            'ports' => $ports,
            'country_names' => $this->fetchCountryNames(),
        ];
    }

    /**
     * Mengambil pasangan kode dan nama negara
     * dari metadata field COUNTRY.
     */
    private function fetchCountryNames(): array
    {
        $payload = $this->request(
            self::LAYER_URL,
            [
                'f' => 'json',
            ]
        );

        $countryNames = [];

        foreach ($payload['fields'] ?? [] as $field) {
            if (($field['name'] ?? '') !== 'COUNTRY') {
                continue;
            }

            $codedValues =
                $field['domain']['codedValues'] ?? [];

            foreach ($codedValues as $codedValue) {
                $code = strtoupper(
                    trim((string) ($codedValue['code'] ?? ''))
                );

                $name = trim(
                    (string) ($codedValue['name'] ?? '')
                );

                if ($code !== '' && $name !== '') {
                    $countryNames[$code] = $name;
                }
            }
        }

        return $countryNames;
    }

    /**
     * Mengirim request HTTP ke ArcGIS.
     */
    private function request(
        string $url,
        array $query
    ): array {
        $response = Http::acceptJson()
            ->timeout(90)
            ->retry(3, 1000)
            ->get($url, $query);

        $response->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException(
                'Respons World Port Index tidak valid.'
            );
        }

        if (isset($payload['error'])) {
            $message =
                $payload['error']['message']
                ?? 'World Port Index mengembalikan error.';

            throw new RuntimeException($message);
        }

        return $payload;
    }
}