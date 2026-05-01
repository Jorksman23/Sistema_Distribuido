<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Empresa{
    protected static $connection = 'odbc';

    // public static function getNombre(): string
    // {
    //     $row = DB::connection(static::$connection)
    //         ->selectOne("SELECT TOP 1 empresa FROM DBA.ge_empresa WHERE codigo = 001", [
    //             env('COMPANY_CODE')
    //         ]);

    //     return $row ? $row->empresa : config('app.name');
    // }

public static function getNombre(): string
    {
        $code = str_pad(config('app.company_code'), 3, '0', STR_PAD_LEFT);

        $row = DB::connection(static::$connection)
            ->selectOne(
                "SELECT TOP 1 empresa FROM DBA.ge_empresa WHERE codigo = ?",
                [$code]
            );

        return $row ? $row->empresa : config('app.name');
    }

}


