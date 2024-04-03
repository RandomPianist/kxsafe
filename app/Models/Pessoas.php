<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
<<<<<<< HEAD
 * @property int    $lixeira
 * @property int    $id_setor
 * @property int    $id_empresa
 * @property int    $created_at
 * @property int    $updated_at
 * @property int    $senha
 * @property string $nome
 * @property string $cpf
 * @property string $funcao
 * @property string $foto
 * @property Date   $admissao
=======
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
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
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
<<<<<<< HEAD
        'nome', 'cpf', 'lixeira', 'id_setor', 'id_empresa', 'created_at', 'updated_at', 'funcao', 'admissao', 'senha', 'foto'
=======
        'created_at', 'senha', 'admissao', 'funcao', 'updated_at', 'id_empresa', 'id_setor', 'lixeira', 'cpf', 'nome'
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
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
<<<<<<< HEAD
        'id' => 'int', 'nome' => 'string', 'cpf' => 'string', 'lixeira' => 'int', 'id_setor' => 'int', 'id_empresa' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'funcao' => 'string', 'admissao' => 'date', 'senha' => 'int', 'foto' => 'string'
=======
        'id' => 'int', 'created_at' => 'timestamp', 'senha' => 'int', 'admissao' => 'date', 'funcao' => 'string', 'updated_at' => 'timestamp', 'id_empresa' => 'int', 'id_setor' => 'int', 'lixeira' => 'int', 'cpf' => 'string', 'nome' => 'string'
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
<<<<<<< HEAD
        'created_at', 'updated_at', 'admissao'
=======
        'created_at', 'admissao', 'updated_at'
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
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
