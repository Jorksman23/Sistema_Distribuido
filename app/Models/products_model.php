<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class products_model
{
    protected  $connection = 'odbc';

    // public function getProducts()
    // {
    //     $items = DB::connection($this->connection)
    //         ->select("SELECT TOP 20 * FROM DBA.in_item WHERE activo = 'S'");

    //     return array_map(function ($item) {

    //         $item = (array) $item;

    //         // 🔥 Limpiar encoding
    //         $item = array_map(function ($value) {
    //             return is_string($value)
    //                 ? mb_convert_encoding($value, 'UTF-8', 'auto')
    //                 : $value;
    //         }, $item);

    //         // 🎯 MAPEO PARA EL FRONT
    //         return [
    //             'id' => $item['codigo'],
    //             'nombre' => $item['descripcion1'],
    //             'precio' => (float) $item['pvp1'],
    //             'imagen' => $item['imagen'],
    //             'categoria' => $item['observacion'],
    //         ];

    //     }, $items);
    // }
    public function getProducts()
    {
        $items = DB::connection($this->connection)
            ->select("SELECT TOP 20 * FROM DBA.in_item");

        // 🔥 Convertir a UTF-8
        return array_map(function ($item) {
            return array_map(function ($value) {
                return is_string($value)
                    ? mb_convert_encoding($value, 'UTF-8', 'auto')
                    : $value;
            }, (array) $item);
        }, $items);
    }
}
