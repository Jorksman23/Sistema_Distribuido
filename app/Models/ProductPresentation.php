<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductPresentation extends Model
{
    protected $connection = 'odbc';
    protected $table = 'in_item_presentacion';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'empresa', 'producto', 'nombre', 'foto', 'foto_id', 'mostrar'
    ];

    /**
     * Devuelve todas las presentaciones activas de una empresa.
     * No depende de products_model, solo devuelve datos crudos.
     */
    public static function getPresentations(string $empresa): array
    {
        $rows = DB::connection('odbc')->select("
            SELECT producto, nombre, foto
            FROM in_item_presentacion
            WHERE empresa = ?
              AND mostrar = 'S'
        ", [$empresa]);

        return array_map(function ($row) {
            return [
                'producto' => $row->producto,
                'foto'     => $row->foto,
                'nombre'   => $row->nombre,
            ];
        }, $rows);
    }

    /**
     * Devuelve las presentaciones de un producto específico.
     */
    public static function getByProduct(string $empresa, string $codigoProducto): array
    {
        $rows = DB::connection('odbc')->select("
            SELECT producto, nombre, foto
            FROM in_item_presentacion
            WHERE empresa = ?
              AND producto = ?
              AND mostrar = 'S'
        ", [$empresa, $codigoProducto]);

        return array_map(function ($row) {
            return [
                'producto' => $row->producto,
                'foto'     => $row->foto,
                'nombre'   => $row->nombre,
            ];
        }, $rows);
    }
}
