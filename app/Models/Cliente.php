<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome',
        'cpf',
        'email',
        'telefone',
        'endereco',
        'cidade',
        'estado',
        'cep',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
