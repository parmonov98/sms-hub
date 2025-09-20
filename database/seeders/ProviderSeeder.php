<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'display_name' => 'eskiz',
                'description' => 'Eskiz SMS Gateway - Uzbekistan',
                'capabilities' => ['dlr' => true, 'unicode' => true, 'concat' => true, 'flash' => false],
                'is_enabled' => true,
                'priority' => 1,
            ],
        ];

        foreach ($providers as $provider) {
            Provider::updateOrCreate(
                ['display_name' => $provider['display_name']],
                $provider
            );
        }
    }
}
