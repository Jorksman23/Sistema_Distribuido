<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CarritoModel;
use App\Services\ProformaGenerator;
use App\Models\FormaPago;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use Throwable;

class PaymentService
{
    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected ProformaGenerator $proforma;
    protected OrderRepository $orderRepository;
    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        ProformaGenerator $proforma,
        OrderRepository $orderRepository

    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->proforma = $proforma;
        $this->orderRepository = $orderRepository;
    }

    public function procesarPago(array $data,string $codCliente,string $empresa): bool {
        $items = $this->carrito->getCarritoByUser($codCliente);
        if (empty($items)) {
            throw new \Exception('El carrito está vacío');
        }
        return true;
    }


    public function obtenerOrden(string $codigo,string $empresa){
        return DB::connection('odbc')->selectOne("
            SELECT *
            FROM DBA.PW_ORDENES_WEB
            WHERE codigo = ?
            AND empresa = ?
        ", [
            $codigo,
            $empresa
        ]);
    }
}
