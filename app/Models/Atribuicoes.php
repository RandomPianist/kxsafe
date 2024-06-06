<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $created_at
 * @property int    $lixeira
 * @property int    $pessoa_ou_setor_valor
 * @property int    $updated_at
 * @property int    $validade
 * @property string $pessoa_ou_setor_chave
 * @property string $produto_ou_referencia_chave
 * @property string $produto_ou_referencia_valor
 */
class Atribuicoes extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'atribuicoes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_at', 'lixeira', 'pessoa_ou_setor_chave', 'pessoa_ou_setor_valor', 'produto_ou_referencia_chave', 'produto_ou_referencia_valor', 'qtd', 'updated_at', 'validade'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int', 'created_at' => 'timestamp', 'lixeira' => 'int', 'pessoa_ou_setor_chave' => 'string', 'pessoa_ou_setor_valor' => 'int', 'produto_ou_referencia_chave' => 'string', 'produto_ou_referencia_valor' => 'string', 'updated_at' => 'timestamp', 'validade' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
}
