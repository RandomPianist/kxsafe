<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $created_at
 * @property int    $senha
 * @property int    $updated_at
 * @property int    $id_empresa
 * @property int    $id_setor
 * @property int    $lixeira
 * @property Date   $admissao
 * @property string $funcao
 * @property string $cpf
 * @property string $nome
 */
class Pessoas extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pessoas';

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
        'created_at', 'senha', 'admissao', 'funcao', 'updated_at', 'id_empresa', 'id_setor', 'lixeira', 'cpf', 'nome'
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
        'id' => 'int', 'created_at' => 'timestamp', 'senha' => 'int', 'admissao' => 'date', 'funcao' => 'string', 'updated_at' => 'timestamp', 'id_empresa' => 'int', 'id_setor' => 'int', 'lixeira' => 'int', 'cpf' => 'string', 'nome' => 'string'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'admissao', 'updated_at'
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
