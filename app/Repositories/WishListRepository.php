<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\WishListModel;


 class WishListRepository{
    protected $connection = 'odbc';
    protected $table = 'DBA.pw_wishlist';
    //Obtener wishlist de un cliente
    public function getByCliente(string $codCliente, string $empresa): array
    {
        $rows = DB::connection($this->connection)->select("
            SELECT
                w.id_wish,
                w.cod_cliente,
                w.codigo_item,
                w.nombre,
                w.pvp3,
                w.imagen,
                w.empresa,
                w.created_at,
                COALESCE(SUM(e.existencia), 0) AS stock_total,
                CASE WHEN EXISTS (
                    SELECT 1 FROM DBA.in_item_presentacion p
                    WHERE p.producto = w.codigo_item
                    AND p.empresa    = w.empresa
                    AND p.mostrar    = 'S'
                ) THEN 1 ELSE 0 END AS tiene_presentaciones
            FROM {$this->table} w
            LEFT JOIN DBA.in_existencia e
                ON e.producto = w.codigo_item
                AND e.empresa = w.empresa
            WHERE w.cod_cliente = ?
            AND   w.empresa     = ?
            GROUP BY
                w.id_wish,
                w.cod_cliente,
                w.codigo_item,
                w.nombre,
                w.pvp3,
                w.imagen,
                w.empresa,
                w.created_at
            ORDER BY w.created_at DESC
        ", [$codCliente, $empresa]);

            $model = new WishListModel();
            return array_map(fn($row) => $model->mapRowToInstance($row), $rows);
    }

    //Verificar si un producto ya está en la wishlist
    public function exists(string $codCliente, string $codigoItem, string $empresa): bool
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT TOP 1 id_wish
            FROM {$this->table}
            WHERE cod_cliente  = ?
            AND   codigo_item  = ?
            AND   empresa      = ?
        ", [$codCliente, $codigoItem, $empresa]);

        return $row !== null;
    }

    //Agregar producto a wishlist
    public function add(array $data): bool{
        return DB::connection($this->connection)->insert("
        INSERT INTO {$this->table}
        (cod_cliente, codigo_item, nombre, pvp3, imagen, empresa)
        VALUES (?, ?, ?, ?, ?, ?)",[
            $data['cod_cliente'],
            $data['codigo_item'],
            $data['nombre'],
            $data['pvp3']   ?? 0,
            $data['imagen'] ?? null,
            $data['empresa'],
        ]);
    }

    // ── Eliminar producto ────────────────────────────────
    public function remove(string $codCliente, string $codigoItem, string $empresa): int
    {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table}
            WHERE cod_cliente = ?
            AND   codigo_item = ?
            AND   empresa     = ?
        ", [$codCliente, $codigoItem, $empresa]);
    }
    // ── Contar items en wishlist ─────────────────────────
    public function count(string $codCliente, string $empresa): int
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE cod_cliente = ?
            AND   empresa     = ?
        ", [$codCliente, $empresa]);

        return (int) ($row->total ?? 0);
    }
 }
