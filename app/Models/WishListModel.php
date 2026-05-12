<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class WishListModel
{
    protected $connection = 'odbc';
    protected $table      = 'DBA.pw_wishlist';

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
                w.created_at
            FROM {$this->table} w
            WHERE w.cod_cliente = ?
            AND   w.empresa     = ?
            ORDER BY w.created_at DESC
        ", [$codCliente, $empresa]);

        return array_map(fn($row) => $this->mapRowToInstance($row), $rows);
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

    //Mapear fila a objeto
    private function mapRowToInstance($row): self
    {
        $instance               = new self();
        $instance->id_wish      = $row->id_wish;
        $instance->cod_cliente  = $row->cod_cliente;
        $instance->codigo_item  = $row->codigo_item;
        $instance->nombre       = ProductsModel::cleanString($row->nombre ?? null);
        $instance->pvp3         = number_format((float)($row->pvp3 ?? 0), 2, '.', '');
        $instance->imagen       = $row->imagen;
        $instance->imagen_url   = productImageUrl($row->imagen);
        $instance->empresa      = $row->empresa;
        $instance->created_at   = $row->created_at;

        return $instance;
    }
    public $id_wish;
    public $cod_cliente;
    public $codigo_item;
    public $nombre;
    public $pvp3;
    public $imagen;
    public $imagen_url;
    public $empresa;
    public $created_at;
}
