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

            $this->crearCabeceraProforma($orden, $documento, $itemsCarrito);
            $this->crearMovimientosProforma($documento, $itemsCarrito);
            $this->actualizarOrdenWeb($orden, $documento);

            DB::connection($this->connection)->commit();

            Log::info("Proforma generada exitosamente", [
                'documento' => $documento,
                'items'     => count($itemsCarrito)
            ]);

            return $documento;

        } catch (Throwable $e) {
            DB::connection($this->connection)->rollBack();
            // Log::error("ProformaGenerator Error: " . $e->getMessage());
            // throw new \Exception("Error al generar proforma: " . $e->getMessage());
            dd([
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
            ]);
        }
    }

    private function generarNumeroDocumento(): string
    {
        $result = DB::connection($this->connection)->selectOne("
            SELECT MAX(CAST(documento AS INTEGER)) as ultimo
            FROM DBA.IN_CABECERA_PROFORMA
            WHERE tipo = 'TW' AND empresa = ?
        ", [$this->empresa]);

        $siguiente = ($result->ultimo ?? 0) + 1;
        return str_pad($siguiente, 6, '0', STR_PAD_LEFT);
    }

    private function crearCabeceraProforma($orden, string $documento, array $itemsCarrito)
    {
        $granTotal = 0;
        foreach ($itemsCarrito as $item) {
            $granTotal += (float)($item->pvp3 ?? 0) * (int)($item->cantidad ?? 1);
        }

        DB::connection($this->connection)->table('DBA.IN_CABECERA_PROFORMA')->insert([
            'tipo'          => companyDefaultOrderType('proforma_web'),
            'documento'     => $documento,
            'empresa'       => $this->empresa,
            'fecha'         => now()->format('Y-m-d'),
            'pro_cli'       => '1',
            'vendedor'      => '1',
            'descuento'     => 0,
            'impuesto'      => 0,
            'comentario'    => $orden->observacion_compra ?? 'TRANSFERENCIA DE ORDEN DE COMPRA REGISTRADA EN LA PAGINA WEB',
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
            $codigoItem     = $item->codigo_item;
            $cantidad       = (int)$item->cantidad;
            $presentacion   = $item->presentacion ?? 0;
            $nombre         = $item->nombre ?? 'Sin nombre';

            //$ubicacion = $this->buscarUbicacionConStock($codigoItem, $presentacion, $cantidad);
            $ubicacion = $this->buscarUbicacionConStock(
            $codigoItem,
            $presentacion,
            $cantidad
            );
            Log::info('UBICACION ENCONTRADA', [
                'producto' => $codigoItem,
                'cantidad' => $cantidad,
                'ubicacion' => $ubicacion,
            ]);
            if (!$ubicacion) {
                throw new \Exception("No hay stock suficiente para: {$nombre}");
            }
            Log::info('ANTES INSERT', [
                'producto' => $codigoItem,
                'documento' => $documento,
                'ubicacion' => $ubicacion,
            ]);
            DB::connection($this->connection)->table('DBA.IN_MOVIMIENTO_PROFORMA')->insert([
                'empresa'      => $this->empresa,
                'tipo'         => companyDefaultOrderType('proforma_web'),
                'documento'    => $documento,
                'cantidad'     => $cantidad,
                'valor'        => (float)$item->pvp3,
                'descuento'    => 0,
                'impuesto'     => 0,
                'lista'        => '1',
                'producto'     => $codigoItem,
                'presentacion' => $presentacion > 0 ? $presentacion : null,
                'ubicacion'    => $ubicacion,
                'numprecio'    => 1,
                'fechae'       => now()->format('Y-m-d'),
                'valor1'       => (float)$item->pvp3,
                'bonificacion' => 0,
            ]);
            Log::info('PASO 1', [
                'codigoItem' => $codigoItem,
                'ubicacion' => $ubicacion
            ]);
            // Descuento de stock
            if ($presentacion > 0) {
                DB::connection($this->connection)->update("
                    UPDATE DBA.in_existencia_presentacion
                    SET cantidad = cantidad - ?
                    WHERE empresa = ? AND item_presentacion = ? AND ubicacion = ?
                ", [$cantidad, $this->empresa, $presentacion, $ubicacion]);
                DB::connection($this->connection)->update("
                    UPDATE DBA.in_existencia
                    SET existencia = existencia - ?
                    WHERE empresa = ? AND producto = ? AND ubicacion = ?
                ", [$cantidad, $this->empresa, $codigoItem, $ubicacion]);}
            else {
                // Solo descuento general
                DB::connection($this->connection)->update("
                    UPDATE DBA.in_existencia
                    SET existencia = existencia - ?
                    WHERE empresa = ? AND producto = ? AND ubicacion = ?
                ", [$cantidad, $this->empresa, $codigoItem, $ubicacion]);
            }
        }
    }

    private function buscarUbicacionConStock(string $codigoItem, int $presentacion, int $cantidadNecesaria): ?string
    {
        if ($presentacion > 0) {
            // Stock por Presentación
            $result = DB::connection($this->connection)->selectOne("
                SELECT TOP 1 e.ubicacion
                FROM DBA.in_existencia_presentacion e
                INNER JOIN DBA.in_item_presentacion p ON p.codigo = e.item_presentacion
                WHERE e.empresa = ?
                  AND p.producto = ?
                  AND p.codigo = ?
                  AND e.cantidad >= ?
                ORDER BY e.cantidad DESC
            ", [$this->empresa, $codigoItem, $presentacion, $cantidadNecesaria]);
        } else {
            // Stock Normal
            $result = DB::connection($this->connection)->selectOne("
                SELECT TOP 1 ubicacion
                FROM DBA.in_existencia
                WHERE empresa = ?
                  AND producto = ?
                  AND existencia >= ?
                ORDER BY existencia DESC
            ", [$this->empresa, $codigoItem, $cantidadNecesaria]);
        }
            return $result->ubicacion ?? null;
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
