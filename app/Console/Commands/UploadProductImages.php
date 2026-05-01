<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UploadProductImages extends Command
{
    protected $signature = 'images:upload {empresa=001}';
    protected $description = 'Sube todas las imágenes de productos de una empresa al servidor de imágenes';

    public function handle()
    {
        $empresa = $this->argument('empresa');
        $ruc = $this->ask('Ingresa el RUC de la empresa ' . $empresa . ' (0920285202001)', '0920285202001');

        $this->info("Buscando imágenes de empresa: {$empresa} (RUC: {$ruc})...");

        $productos = DB::connection('odbc')
            ->table('DBA.in_item')
            ->where('empresa', $empresa)
            ->whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->select('codigo', 'imagen')
            ->get();

        $this->info("Se encontraron {$productos->count()} productos con imagen.");

        $uploaded = 0;
        $failed = 0;

        foreach ($productos as $prod) {
            $localPath = public_path('images/productos/' . $prod->imagen); // ajusta la ruta local donde tienes las imágenes

            if (!file_exists($localPath)) {
                $this->warn("Imagen no encontrada: " . $prod->imagen);
                $failed++;
                continue;
            }

            $remotePath = "{$ruc}/producto/{$prod->imagen}";

            try {
                $response = Http::attach(
                    'file',
                    fopen($localPath, 'r'),
                    $prod->imagen
                )->post("http://186.101.203.76:10555/upload?path={$remotePath}");   // ← Cambia esta URL según el endpoint real de subida

                if ($response->successful()) {
                    $uploaded++;
                    $this->info("✓ Subida: {$prod->imagen}");
                } else {
                    $this->error("✗ Falló: {$prod->imagen} - " . $response->body());
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Error: {$prod->imagen} - " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Proceso terminado. Subidas: {$uploaded} | Fallidas: {$failed}");
    }
}
