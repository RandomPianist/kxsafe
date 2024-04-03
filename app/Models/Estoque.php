<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
<<<<<<< HEAD
 * @property int    $id_maquina
 * @property int    $id_produto
 * @property int    $created_at
 * @property int    $updated_at
=======
 * @property int    $updated_at
 * @property int    $created_at
 * @property int    $id_produto
 * @property int    $id_maquina
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
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
<<<<<<< HEAD
        'es', 'descr', 'qtd', 'id_maquina', 'id_produto', 'created_at', 'updated_at'
=======
        'updated_at', 'created_at', 'id_produto', 'qtd', 'descr', 'es', 'id_maquina'
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
        'id' => 'int', 'descr' => 'string', 'id_maquina' => 'int', 'id_produto' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
=======
        'id' => 'int', 'updated_at' => 'timestamp', 'created_at' => 'timestamp', 'id_produto' => 'int', 'descr' => 'string', 'id_maquina' => 'int'
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
<<<<<<< HEAD
        'created_at', 'updated_at'
=======
        'updated_at', 'created_at'
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
