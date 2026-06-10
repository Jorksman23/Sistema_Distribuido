<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class WishListModel
{
    protected $connection = 'odbc';
    protected $table      = 'DBA.pw_wishlist';
    public $tiene_presentaciones;

    //Mapear fila a objeto
    public function mapRowToInstance($row): self
    {
        $instance               = new self();
        $instance->id_wish      = $row->id_wish;
        $instance->cod_cliente  = $row->cod_cliente;
        $instance->codigo_item  = $row->codigo_item;
        $instance->nombre       = ProductsModel::cleanString($row->nombre ?? null);
        $instance->pvp3         = number_format((float)($row->pvp3 ?? 0), 2, '.', '');
        $instance->stock_total = (float) ($row->stock_total ?? 0);
        $instance->imagen       = $row->imagen;
        $instance->imagen_url   = productImageUrl($row->imagen);
        $instance->empresa      = $row->empresa;
        $instance->tiene_presentaciones = (bool)($row->tiene_presentaciones ?? false);
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
    public $stock_total;
}
