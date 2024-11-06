<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class PassportClientSeeder extends Seeder
{
    public function run()
    {

        Artisan::call('passport:client', [
            '--password' => true,
            '--name' => 'Password Grant Client',
            '--provider' => 'users',
        ]);


        $output = Artisan::output();

        preg_match('/Client ID.*?(\d+)/', $output, $clientId);
        preg_match('/Client secret.*?([a-zA-Z0-9]+)/', $output, $clientSecret);

        if (isset($clientId[1]) && isset($clientSecret[1])) {
            $this->updateEnvFile('PASSPORT_CLIENT_ID', $clientId[1]);
            $this->updateEnvFile('PASSPORT_CLIENT_SECRET', $clientSecret[1]);

            $this->command->info("Client ID y Client Secret guardados en el archivo .env");
        } else {
            $this->command->error("No se pudo extraer el Client ID o Client Secret");
        }
    }

    protected function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
    }
}
