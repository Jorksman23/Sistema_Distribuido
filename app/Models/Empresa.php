<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Empresa extends Model
{
    protected $table = 'ge_empresa';
    public $timestamps = false;

    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $connection = 'odbc';

    protected $fillable = [
        'email',
        'direccion_tienda',
        'celular',
        'telefono2',
        'celular_rl'
    ];

    public static function getNombre(): string
    {
        $code = str_pad(config('app.company_code'), 3, '0', STR_PAD_LEFT);
        $row = DB::connection('odbc')
            ->selectOne(
                "SELECT TOP 1 empresa FROM DBA.ge_empresa WHERE codigo = ?",
                [$code]
            );
        return $row ? $row->empresa : config('app.name');
    }
}
