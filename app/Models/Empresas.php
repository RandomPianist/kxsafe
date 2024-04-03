<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
<<<<<<< HEAD
 * @property int    $lixeira
 * @property int    $id_matriz
 * @property int    $created_at
 * @property int    $updated_at
 * @property string $razao_social
 * @property string $nome_fantasia
 * @property string $cnpj
=======
 * @property int    $updated_at
 * @property int    $created_at
 * @property int    $id_matriz
 * @property int    $lixeira
 * @property string $nome_fantasia
 * @property string $cnpj
 * @property string $razao_social
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
 */
class Empresas extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'empresas';

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
        'razao_social', 'nome_fantasia', 'cnpj', 'lixeira', 'id_matriz', 'created_at', 'updated_at'
=======
        'nome_fantasia', 'updated_at', 'created_at', 'id_matriz', 'lixeira', 'cnpj', 'razao_social'
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
        'id' => 'int', 'razao_social' => 'string', 'nome_fantasia' => 'string', 'cnpj' => 'string', 'lixeira' => 'int', 'id_matriz' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
=======
        'id' => 'int', 'nome_fantasia' => 'string', 'updated_at' => 'timestamp', 'created_at' => 'timestamp', 'id_matriz' => 'int', 'lixeira' => 'int', 'cnpj' => 'string', 'razao_social' => 'string'
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
