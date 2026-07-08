<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            [
                'name' => 'Port of Tanjung Priok',
                'country_name' => 'Indonesia',
                'country_code' => 'IDN',
                'port_code' => 'IDTPP',
                'latitude' => -6.1042,
                'longitude' => 106.8840,
                'congestion_level' => 45,
                'risk_level' => 'medium',
                'notes' => 'Major container port serving Jakarta.',
            ],
            [
                'name' => 'Port of Singapore',
                'country_name' => 'Singapore',
                'country_code' => 'SGP',
                'port_code' => 'SGSIN',
                'latitude' => 1.2644,
                'longitude' => 103.8400,
                'congestion_level' => 35,
                'risk_level' => 'medium',
                'notes' => 'Major transshipment hub in Southeast Asia.',
            ],
            [
                'name' => 'Port of Shanghai',
                'country_name' => 'China',
                'country_code' => 'CHN',
                'port_code' => 'CNSHA',
                'latitude' => 31.2304,
                'longitude' => 121.4737,
                'congestion_level' => 70,
                'risk_level' => 'high',
                'notes' => 'One of the busiest container ports globally.',
            ],
            [
                'name' => 'Port of Shenzhen',
                'country_name' => 'China',
                'country_code' => 'CHN',
                'port_code' => 'CNSZX',
                'latitude' => 22.5431,
                'longitude' => 114.0579,
                'congestion_level' => 62,
                'risk_level' => 'high',
                'notes' => 'Important manufacturing export gateway.',
            ],
            [
                'name' => 'Port of Rotterdam',
                'country_name' => 'Netherlands',
                'country_code' => 'NLD',
                'port_code' => 'NLRTM',
                'latitude' => 51.9244,
                'longitude' => 4.4777,
                'congestion_level' => 30,
                'risk_level' => 'low',
                'notes' => 'Major European logistics gateway.',
            ],
            [
                'name' => 'Port of Hamburg',
                'country_name' => 'Germany',
                'country_code' => 'DEU',
                'port_code' => 'DEHAM',
                'latitude' => 53.5511,
                'longitude' => 9.9937,
                'congestion_level' => 40,
                'risk_level' => 'medium',
                'notes' => 'Major port in Northern Europe.',
            ],
            [
                'name' => 'Port of Los Angeles',
                'country_name' => 'United States',
                'country_code' => 'USA',
                'port_code' => 'USLAX',
                'latitude' => 33.7405,
                'longitude' => -118.2775,
                'congestion_level' => 55,
                'risk_level' => 'medium',
                'notes' => 'Major US West Coast import gateway.',
            ],
            [
                'name' => 'Port of Long Beach',
                'country_name' => 'United States',
                'country_code' => 'USA',
                'port_code' => 'USLGB',
                'latitude' => 33.7542,
                'longitude' => -118.2165,
                'congestion_level' => 50,
                'risk_level' => 'medium',
                'notes' => 'Important container port near Los Angeles.',
            ],
            [
                'name' => 'Port of Jebel Ali',
                'country_name' => 'United Arab Emirates',
                'country_code' => 'ARE',
                'port_code' => 'AEJEA',
                'latitude' => 25.0118,
                'longitude' => 55.0619,
                'congestion_level' => 28,
                'risk_level' => 'low',
                'notes' => 'Major Middle East logistics hub.',
            ],
            [
                'name' => 'Port Botany',
                'country_name' => 'Australia',
                'country_code' => 'AUS',
                'port_code' => 'AUBTB',
                'latitude' => -33.9695,
                'longitude' => 151.2247,
                'congestion_level' => 32,
                'risk_level' => 'low',
                'notes' => 'Major container port serving Sydney.',
            ],
        ];

        foreach ($ports as $port) {
            Port::updateOrCreate(
                [
                    'name' => $port['name'],
                    'country_code' => $port['country_code'],
                ],
                $port
            );
        }
    }
}