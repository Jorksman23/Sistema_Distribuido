<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CarritoModel;
use App\Services\ProformaGenerator;
use Throwable;

class PaymentService
{
    protected $carrito;
    protected $proforma;

    public function __construct()
    {
        $this->carrito  = new CarritoModel();
        $this->proforma = new ProformaGenerator();
    }

    public function procesarPago(array $data, string $codCliente, string $empresa): string
    {
        DB::connection('odbc')->beginTransaction();

        try {
            $items = $this->carrito->getCarritoByUser($codCliente);
            if (empty($items)) {
                throw new \Exception('El carrito está vacío');
            }

            $granTotal   = $this->carrito->getTotal($codCliente);
            $codigoOrden = $this->generarCodigoOrden($empresa);

            $secuenciaPago = match(strtolower($data['tipo_pago'])) {
                'payphone'      => 5,
                'transferencia' => 7,
                'contraentrega' => 1,
                default         => 1,
            };

            // Insertar orden

            DB::connection('odbc')->table('DBA.PW_ORDENES_WEB')->insert([
            'codigo'            => $codigoOrden,
            'cod_cliente'       => $codCliente,
            'n_documento'       => $codigoOrden,
            'tipo'              => 'TW',   // ← ESTA ES LA CLAVE
            'empresa'           => $empresa,
            'uuid_session'      => md5(uniqid(rand(), true)),
            'tipo_pago'         => $secuenciaPago,
            'items_carrito'     => count($items),
            'gran_total'        => $granTotal,
            'estatus'           => '1',
            'cedula_cliente'    => $data['cedula'],
            'nombre_cliente'    => $data['nombre'],
            'email_cliente'     => $data['email'],
            'telefono_cliente'  => $data['telefono'],
            'direccion_cliente' => $data['direccion'],
            'observacion_compra'=> $data['observacion'] ?? null,
            'fecha_creacion'    => now(),
            'fecha_modificacion'=> now(),
            ]);


            // Generar proforma
            $documento = $this->proforma->generarDesdeOrden(
                (object) ['codigo' => $codigoOrden, 'observacion_compra' => $data['observacion']],
                $items,
                $empresa
            );

            // Vaciar carrito
            $this->carrito->vaciar($codCliente);

            DB::connection('odbc')->commit();

            return $codigoOrden;
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            Log::error('ERROR PROCESAR PAGO: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generarCodigoOrden(string $empresa): string
    {
        $max = DB::connection('odbc')->selectOne("
            SELECT MAX(CAST(codigo AS INTEGER)) as maxc
            FROM DBA.PW_ORDENES_WEB WHERE empresa = ?
        ", [$empresa]);

        return str_pad(($max->maxc ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}
