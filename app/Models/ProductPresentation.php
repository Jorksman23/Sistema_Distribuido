<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPresentation extends Model
{
    protected $connection = 'odbc';
    protected $table = 'in_item_presentacion';

    protected $fillable = [
        'codigo', 'empresa', 'producto', 'nombre', 'foto', 'foto_id', 'mostrar'
    ];

    public function product()
    {
        return $this->belongsTo(products_model::class, 'producto', 'codigo');
    }

    /**
     * Scope para empresa 001
     */
    public function scopeForCompany($query, string $empresa = '005')
    {
        return $query->where('empresa', $empresa);
    }
}
