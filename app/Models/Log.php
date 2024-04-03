<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
<<<<<<< HEAD
 * @property int    $id_pessoa
 * @property int    $fk
 * @property int    $created_at
 * @property int    $updated_at
=======
 * @property int    $updated_at
 * @property int    $created_at
 * @property int    $fk
 * @property int    $id_pessoa
>>>>>>> eec9a4c74804f2cd9f120aa6d244370223272954
 * @property string $tabela
 */
class Log extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'log';

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
        'id_pessoa', 'acao', 'tabela', 'fk', 'created_at', 'updated_at'
=======
        'acao', 'updated_at', 'created_at', 'fk', 'tabela', 'id_pessoa'
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
        'id' => 'int', 'id_pessoa' => 'int', 'tabela' => 'string', 'fk' => 'int', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'
=======
        'id' => 'int', 'updated_at' => 'timestamp', 'created_at' => 'timestamp', 'fk' => 'int', 'tabela' => 'string', 'id_pessoa' => 'int'
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
