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

    public function consultar($arr_tabelas, $alias = "", $where = "") {
        $consulta = DB::table("log")
                        ->select(
                            DB::raw("
                                IFNULL(pessoas.nome, CONCAT(
                                    'API',
                                    IFNULL(CONCAT(' - ', log.nome), '')
                                )) AS nome
                            "),
                            DB::raw("DATE_FORMAT(log.created_at, '%d/%m/%Y às %H:%i') AS data")
                        )
                        ->leftjoin("pessoas", "pessoas.id", "log.id_pessoa")
                        ->leftjoin("pessoas AS aux", "aux.id", "log.fk")
                        ->leftjoin("setores", "setores.id", "aux.id_setor")
                        ->leftjoin("atribuicoes", function($join) {
                            $join->on(function($sql) {
                                $sql->on("atribuicoes.pessoa_ou_setor_valor", "aux.id")
                                    ->whereRaw("atribuicoes.pessoa_ou_setor_chave = 'pessoa'");
                            })->orOn(function($sql) {
                                $sql->on("atribuicoes.pessoa_ou_setor_valor", "setores.id")
                                    ->whereRaw("atribuicoes.pessoa_ou_setor_chave = 'setor'");
                            });
                        })
                        ->leftjoin("valores", "valores.id", "log.fk")
                        ->where(function($sql) use($arr_tabelas, $alias, $where) {
                            if (in_array("pessoas", $arr_tabelas)) {
                                $sql->whereNotNull("aux.id")
                                    ->whereNotNull("setores.id");
                                array_push($arr_tabelas, "atribuicoes");
                            }
                            if ($alias) $sql->where("alias", $alias);
                            else $sql->whereRaw("tabela IN ('".join("', '", $arr_tabelas)."')");
                            if ($where) $sql->whereRaw($where);
                        })
                        ->orderby("log.id", "desc")
                        ->get();
        return !intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa) ? sizeof($consulta) ? "Última atualização feita por ".$consulta[0]->nome." em ".$consulta[0]->data : "Nenhuma atualização feita" : "";
    }
}