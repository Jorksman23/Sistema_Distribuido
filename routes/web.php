<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/items', function () {
    $items = DB::connection('odbc')
        ->select("SELECT TOP 10 * FROM DBA.in_item");

    // 🔥 Convertir a UTF-8
    $items = array_map(function ($item) {
        return array_map(function ($value) {
            return is_string($value)
                ? utf8_encode($value)
                : $value;
        }, (array) $item);
    }, $items);

    return response()->json($items);
});
