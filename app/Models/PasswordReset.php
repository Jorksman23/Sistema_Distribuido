<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'pw_password_resets';
    protected $primaryKey = ['empresa', 'codigo'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'empresa',
        'codigo',
        'email',
        'token_hash',
        'created_at',
    ];
}
