<?php
use Illuminate\Support\Facades\DB;

if (!function_exists('currentCompany')) {
    function currentCompany(): string
    {
        // Devuelve el código de empresa actual desde config
        return config('app.company_code', '?' );
    }
}

if (!function_exists('companyRuc')) {
    function companyRuc(?string $companyCode = null): string
    {
        $companyCode = $companyCode ?? currentCompany();

        return cache()->remember("ruc_{$companyCode}", now()->addHours(12), function () use ($companyCode) {
            // En SQL Anywhere se usa TOP en lugar de LIMIT
            $row = DB::connection('odbc')
                ->selectOne("SELECT TOP 1 ruc FROM GE_EMPRESA WHERE codigo = ?", [$companyCode]);

            return $row?->ruc ?: $companyCode;
        });
    }
}

if (!function_exists('companyImageBaseUrl')) {
    function companyImageBaseUrl(): string
    {
        $base = config('app.image_server_base_url', 'http://186.101.203.76:10555/');
        $ruc  = companyRuc();
        $path = config('app.image_server_path_products', 'product');

        return rtrim($base, '/') . '/' . $ruc . '/' . $path . '/';
    }
}

if (!function_exists('companyLogoUrl')) {
    function companyLogoUrl(): ?string
    {
        $companyCode = currentCompany();

        $row = DB::connection('odbc')->selectOne("
            SELECT TOP 1 logo_tienda, ruc
            FROM DBA.ge_empresa
            WHERE codigo = ?
        ", [$companyCode]);

        if (!$row || empty($row->logo_tienda)) {
            return null;
        }

        $base = config('app.image_server_base_url', 'http://186.101.203.76:10555/');
        $path = config('app.image_server_path_logos', 'logo_tienda');

        return rtrim($base, '/') . '/' . $row->ruc . '/' . $path . '/' . ltrim($row->logo_tienda, '/');
    }
}


if (!function_exists('productImageUrl')) {
    function productImageUrl(?string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }

        return companyImageBaseUrl() . ltrim($filename, '/');
    }
}

if (!function_exists('presentationImageUrl')) {
    function presentationImageUrl(?string $filename, ?string $codigoProducto = null): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $base = companyImageBaseUrl();

        if ($codigoProducto) {
            return rtrim($base, '/') . '/' . $codigoProducto . '/' . ltrim($filename, '/');
        }

        return $base . ltrim($filename, '/');
    }


    if (!function_exists('companyDefaultOrderType')) {
    function companyDefaultOrderType(string $tipo = 'proforma_web'): string
    {
        $documentTypes = [
            'proforma_web' => 'TW',
            'invoice'      => 'FC',
        ];

        return $documentTypes[$tipo] ?? 'TW';
    }
    }


}
