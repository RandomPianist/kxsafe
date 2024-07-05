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

    public function consultar($tabela, $param = "") {
        $query = "
            SELECT
                IFNULL(pessoas.nome, CONCAT(
                    'API',
                    IFNULL(CONCAT(' - ', log.nome), '')
                )) AS nome,
                DATE_FORMAT(log.created_at, '%d/%m/%Y às %H:%i') AS data

            FROM log

            LEFT JOIN pessoas
                ON pessoas.id = log.id_pessoa
        ";

        switch($tabela) {
            case "produtos":
                $query .= " WHERE log.tabela = 'produtos'";
                break;
            case "empresas":
                $query .= " WHERE log.tabela IN ('empresas', 'empresas_setores')";
                break;
            case "setores":
                $query .= "
                    LEFT JOIN (
                        SELECT id
                        FROM atribuicoes
                        WHERE pessoa_ou_setor_chave = 'setor'
                    ) AS atb ON atb.id = log.fk

                    WHERE log.tabela = 'setores'
                       OR (log.tabela = 'atribuicoes' AND atb.id IS NOT NULL)
                ";
                break;
            case "valores":
                $query .= "
                    LEFT JOIN (
                        SELECT id
                        FROM valores
                        WHERE alias = '".$param."'
                    ) AS main ON main.id = log.fk

                    LEFT JOIN maquinas_produtos AS mp
                        ON mp.id_maquina = main.id

                    LEFT JOIN estoque
                        ON estoque.id_mp = mp.id

                    WHERE (log.tabela = 'valores' AND main.id IS NOT NULL)
                       OR (log.tabela = 'maquinas_produtos' AND mp.id IS NOT NULL)
                       OR (log.tabela = 'estoque' AND estoque.id IS NOT NULL)
                ";
                break;
            case "pessoas":
                $param2 = str_replace("aux1", "aux2", $param);
                $param2 = str_replace("setores1", "setores2", $param2);
                $query .= "
                    LEFT JOIN pessoas AS aux1
                        ON aux1.id = log.fk

                    LEFT JOIN setores AS setores1
                        ON setores1.id = aux1.id_setor

                    LEFT JOIN (
                        SELECT
                            id,
                            pessoa_ou_setor_valor
                        FROM atribuicoes
                        WHERE pessoa_ou_setor_chave = 'pessoa'
                    ) AS atb ON atb.id = log.fk

                    LEFT JOIN pessoas AS aux2
                        ON aux2.id = atb.pessoa_ou_setor_valor

                    LEFT JOIN setores AS setores2
                        ON setores2.id = aux2.id_setor

                    LEFT JOIN retiradas
                        ON retiradas.id_atribuicao = atb.id AND retiradas.id_comodato = 0

                    WHERE (log.tabela = 'pessoas' AND ".$param.")
                       OR (".$param2." AND (log.tabela = 'atribuicoes' OR (log.tabela = 'retiradas' AND retiradas.id IS NOT NULL)))
                ";
                break;
        }

        $query .= " ORDER BY log.id DESC";

        $consulta = DB::select(DB::raw($query));
        return !intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa) ? sizeof($consulta) ? "Última atualização feita por ".$consulta[0]->nome." em ".$consulta[0]->data : "Nenhuma atualização feita" : "";
    }
}