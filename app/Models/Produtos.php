<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $created_at
 * @property int    $updated_at
 * @property int    $id_categoria
 * @property int    $lixeira
 * @property int    $validade
 * @property string $cod_externo
 * @property string $foto
 * @property string $descr
 * @property string $ca
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
        'created_at', 'updated_at', 'id_categoria', 'cod_externo', 'foto', 'lixeira', 'validade', 'preco', 'descr', 'ca'
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
        'id' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'id_categoria' => 'int', 'cod_externo' => 'string', 'foto' => 'string', 'lixeira' => 'int', 'validade' => 'int', 'descr' => 'string', 'ca' => 'string'
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
