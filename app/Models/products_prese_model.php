<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class products_prese_model{
    public function getPresentaciones(){
        $items = DB::connection('odbc')
            ->select("SELECT TOP 20 * FROM DBA.in_item_presentacion");

        return array_map(function ($item) {
            return array_map(function ($value) {
                return is_string($value)
                    ? mb_convert_encoding($value, 'UTF-8', 'auto')
                    : $value;
            }, (array) $item);
        }, $items);
    }
}
