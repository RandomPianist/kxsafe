<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Log;
use App\Models\Pessoas;

class LogController extends Controller {
    public function inserir($acao, $tabela, $fk, $api = false) {
        $linha = new Log;
        $linha->acao = $acao;
        $linha->tabela = $tabela;
        $linha->fk = $fk;
        if (!$api) $linha->id_pessoa = Auth::user()->id_pessoa;
        $linha->save();
        return $linha;
    }

    public function consultar($arr_tabelas, $alias = "") {
        $tabelas = "'".join("' OR '", $arr_tabelas)."'";
        $query = "
            SELECT
                IFNULL(pessoas.nome, CONCAT(
                    'API',
                    IFNULL(CONCAT(' - ', log.nome), '')
                )) AS nome,
                DATE_FORMAT(log.created_at, '%d/%m/%Y às %H:%i') AS data
            
            FROM log

            LEFT JOIN pessoas
                ON log.id_pessoa = pessoas.id
        ";
        $query .= $alias != "" ? "
            JOIN valores
                ON valores.id = log.fk

            WHERE alias = '".$alias."'
        " : "
            WHERE tabela IN (".$tabelas.")
        ";
        $query .= " ORDER BY log.id DESC";
        $consulta = DB::select(DB::raw($query));
        return !intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa) ? sizeof($consulta) ? "Última atualização feita por ".$consulta[0]->nome." em ".$consulta[0]->data : "Nenhuma atualização feita" : "";
    }
}