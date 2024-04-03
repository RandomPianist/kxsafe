<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
<<<<<<< HEAD
 * @property int    $validade
 * @property int    $lixeira
 * @property int    $id_categoria
 * @property int    $created_at
 * @property int    $updated_at
 * @property string $descr
 * @property string $ca
 * @property string $foto
 * @property string $cod_externo
=======
 * @property int    $created_at
 * @property int    $updated_at
 * @property int    $id_categoria
 * @property int    $lixeira
 * @property int    $validade
 * @property string $cod_externo
 * @property string $foto
 * @property string $descr
 * @property string $ca
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
 */
class Produtos extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'produtos';

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
        'descr', 'preco', 'validade', 'lixeira', 'ca', 'foto', 'cod_externo', 'id_categoria', 'created_at', 'updated_at'
=======
        'created_at', 'updated_at', 'id_categoria', 'cod_externo', 'foto', 'lixeira', 'validade', 'preco', 'descr', 'ca'
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
        'id' => 'int', 'descr' => 'string', 'validade' => 'int', 'lixeira' => 'int', 'ca' => 'string', 'foto' => 'string', 'cod_externo' => 'string', 'id_categoria' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
=======
        'id' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'id_categoria' => 'int', 'cod_externo' => 'string', 'foto' => 'string', 'lixeira' => 'int', 'validade' => 'int', 'descr' => 'string', 'ca' => 'string'
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
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
