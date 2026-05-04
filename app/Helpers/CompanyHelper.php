cat > app/Helpers/CompanyHelper.php << 'EOF'
<?php
use Illuminate\Support\Facades\DB;

if (!function_exists('currentCompany')) {
    function currentCompany(): string
    {
        return config('app.company_code', '005');
    }
}

if (!function_exists('companyRuc')) {
    function companyRuc(?string $companyCode = null): string
    {
        $companyCode = $companyCode ?? currentCompany();

        return cache()->remember("ruc_{$companyCode}", now()->addHours(12), function () use ($companyCode) {
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
        $path = config('app.image_server_path_products', 'product');   // "product" como pediste

        return rtrim($base, '/') . '/' . $ruc . '/' . $path . '/';
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

