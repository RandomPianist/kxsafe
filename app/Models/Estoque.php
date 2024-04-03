<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $updated_at
 * @property int    $created_at
 * @property int    $id_produto
 * @property int    $id_maquina
 * @property string $descr
 */
class Estoque extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'estoque';

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
        'updated_at', 'created_at', 'id_produto', 'qtd', 'descr', 'es', 'id_maquina'
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
        'id' => 'int', 'updated_at' => 'timestamp', 'created_at' => 'timestamp', 'id_produto' => 'int', 'descr' => 'string', 'id_maquina' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'updated_at', 'created_at'
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
