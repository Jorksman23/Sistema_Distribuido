<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Throwable;

class ProformaGenerator
{
    protected $connection = 'odbc';
    protected $empresa;

    public function generarDesdeOrden($orden, array $itemsCarrito, string $empresa)
    {
        $this->empresa = $empresa;

        try {
            DB::connection($this->connection)->beginTransaction();

            $documento = $this->generarNumeroDocumento();

            // 1. Crear Cabecera de Proforma
            $this->crearCabeceraProforma($orden, $documento, $itemsCarrito);

            // 2. Crear Movimientos (Detalle)
            $this->crearMovimientosProforma($documento, $itemsCarrito);

            // 3. Actualizar Orden Web
            $this->actualizarOrdenWeb($orden, $documento);

            DB::connection($this->connection)->commit();

            return $documento;

        } catch (Throwable $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("ProformaGenerator Error: " . $e->getMessage());
            throw new \Exception("Error al generar proforma: " . $e->getMessage());
        }
    }

    private function generarNumeroDocumento(): string
    {
        $result = DB::connection($this->connection)->selectOne("
            SELECT MAX(CAST(documento AS INTEGER)) as ultimo
            FROM DBA.IN_CABECERA_PROFORMA
            WHERE tipo = 'FC' AND empresa = ?
        ", [$this->empresa]);

        $siguiente = ($result->ultimo ?? 0) + 1;
        return str_pad($siguiente, 6, '0', STR_PAD_LEFT);
    }

    private function crearCabeceraProforma($orden, string $documento, array $itemsCarrito)
    {
        $granTotal = 0;
        foreach ($itemsCarrito as $item) {
            $granTotal += (float)$item->pvp3 * (int)$item->cantidad;
        }

        DB::connection($this->connection)->table('DBA.IN_CABECERA_PROFORMA')->insert([
            'tipo'          => 'TW',
            'documento'     => $documento,
            'empresa'       => $this->empresa,
            'fecha'         => now()->format('Y-m-d'),
            'pro_cli'       => '1',
            'vendedor'      => '10',
            'descuento'     => 0,
            'impuesto'      => 0,
            'comentario'    => $orden->observacion_compra ?? 'Venta Web',
            'caja'          => '1',
            'fechav'        => now()->format('Y-m-d'),
            'referencia'    => $orden->codigo ?? $documento,
            'orden'         => $orden->codigo ?? $documento,
            'estado_fact'   => 'A',
            'created_at'    => now(),
            'update_at'     => now(),
        ]);
    }

    private function crearMovimientosProforma(string $documento, array $itemsCarrito)
    {
        foreach ($itemsCarrito as $item) {
            DB::connection($this->connection)->table('DBA.IN_MOVIMIENTO_PROFORMA')->insert([
                'empresa'      => $this->empresa,
                'tipo'         => 'TW',
                'documento'    => $documento,
                'cantidad'     => (int)$item->cantidad,
                'valor'        => (float)$item->pvp3,
                'descuento'    => 0,
                'impuesto'     => 0,
                'producto'     => $item->codigo_item,
                'presentacion' => isset($item->presentacion) && $item->presentacion !== 0? $item->presentacion: null,
                'ubicacion'    => '4',
                'numprecio'    => 1,
                'fechae'       => now()->format('Y-m-d'),
            ]);
        }
    }

    private function actualizarOrdenWeb($orden, string $documento)
    {
        DB::connection($this->connection)->table('DBA.PW_ORDENES_WEB')
            ->where('codigo', $orden->codigo)
            ->update([
                'n_documento'          => $documento,
                'estatus'              => '2',
                'n_confirmacion'       => $documento,
                'pt_referencia_compra' => $documento,
                'fecha_modificacion'   => now(),
            ]);
    }
}
