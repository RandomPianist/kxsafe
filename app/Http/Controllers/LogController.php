<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Log;
use App\Models\Pessoas;

class LogController extends Controller {
    public function inserir($acao, $tabela, $fk) {
        $linha = new Log;
        $linha->acao = $acao;
        $linha->tabela = $tabela;
        $linha->fk = $fk;
        $linha->id_pessoa = Auth::user()->id_pessoa;
        $linha->save();
    }

    public function consultar($tabela, $alias = "") {
        $query = "
            SELECT
                nome,
                DATE_FORMAT(log.created_at, '%d/%m/%Y às %H:%i') AS data
            
            FROM log

            JOIN pessoas
                ON log.id_pessoa = pessoas.id
        ";
        $query .= $alias != "" ? "
            JOIN valores
                ON valores.id = log.fk

            WHERE alias = '".$alias."'
        " : "
            WHERE tabela = '".$tabela."'
        ";
        $query .= " ORDER BY log.id DESC";
        $consulta = DB::select(DB::raw($query));
        return !intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa) ? sizeof($consulta) ? "Última atualização feita por ".$consulta[0]->nome." em ".$consulta[0]->data : "Nenhuma atualização feita" : "";
    }
}