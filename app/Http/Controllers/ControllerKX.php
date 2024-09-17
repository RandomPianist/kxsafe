<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Pessoas;
use App\Models\Atribuicoes;
use App\Models\Retiradas;
use App\Models\MaquinasProdutos;

class ControllerKX extends Controller {
    protected function empresa_consultar(Request $request) {
        return (!sizeof(
            DB::table("empresas")
                ->where("id", $request->id_empresa)
                ->where("nome_fantasia", $request->empresa)
                ->where("lixeira", 0)
                ->get()
        ));
    }

    protected function log_inserir($acao, $tabela, $fk, $api = false) {
        $linha = new Log;
        $linha->acao = $acao;
        $linha->tabela = $tabela;
        $linha->fk = $fk;
        if (!$api) $linha->id_pessoa = Auth::user()->id_pessoa;
        $linha->save();
        return $linha;
    }

    protected function log_inserir2($acao, $tabela, $where, $nome, $api = false) {
        if ($nome != "NULL") $nome = "'".$nome."'";
        $sql = "INSERT INTO log (acao, tabela, nome, ";
        if (!$api) $sql .= "id_pessoa, ";
        $sql .= "fk) SELECT
            '".$acao."',
            '".$tabela."',
            ".$nome.",
        ";
        if (!$api) $sql .= Auth::user()->id_pessoa.",";
        $sql .= "
            id

            FROM ".$tabela."

            WHERE ".$where;
        DB::statement($sql);
    }

    protected function log_consultar($tabela, $param = "") {
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

        if ($tabela == "pessoas") {
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
                    WHERE pessoa_ou_setor_chave = 'P'
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
        } else if ($tabela == "valores") {
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
        } else if ($tabela == "setores") {
            $query .= "
                LEFT JOIN (
                    SELECT id
                    FROM atribuicoes
                    WHERE pessoa_ou_setor_chave = 'S'
                ) AS atb ON atb.id = log.fk

                WHERE log.tabela = 'setores'
                  OR (log.tabela = 'atribuicoes' AND atb.id IS NOT NULL)
            ";
        } else $query .= " WHERE log.tabela = '".$tabela."'";

        $query .= " ORDER BY log.id DESC";

        $consulta = DB::select(DB::raw($query));
        return !intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa) ? sizeof($consulta) ? "Última atualização feita por ".$consulta[0]->nome." em ".$consulta[0]->data : "Nenhuma atualização feita" : "";
    }

    protected function retirada_consultar($id_atribuicao, $qtd) {
        $atribuicao = Atribuicoes::find($id_atribuicao);
        $ja_retirados = DB::table("retiradas")
                            ->selectRaw("IFNULL(SUM(retiradas.qtd), 0) AS qtd")
                            ->join("atribuicoes", "atribuicoes.id", "retiradas.id_atribuicao")
                            ->whereRaw("DATE_ADD(retiradas.data, INTERVAL atribuicoes.validade DAY) >= CURDATE()")
                            ->where("atribuicoes.id", $id_atribuicao)
                            ->get();
        if (floatval($atribuicao->qtd) < (floatval($qtd) + (sizeof($ja_retirados) ? floatval($ja_retirados[0]->qtd) : 0))) return 0;
        return 1;
    }

    protected function retirada_salvar($json) {
        $linha = new Retiradas;
        if (isset($json["obs"])) $linha->obs = $json["obs"];
        if (isset($json["id_supervisor"])) {
            if (intval($json["id_supervisor"])) $linha->id_supervisor = $json["id_supervisor"];
        }
        $linha->id_pessoa = $json["id_pessoa"];
        $linha->id_atribuicao = $json["id_atribuicao"];
        $linha->id_produto = $json["id_produto"];
        $linha->id_comodato = $json["id_comodato"];
        $linha->qtd = $json["qtd"];
        $linha->data = $json["data"];
        $linha->save();
        $api = $json["id_comodato"] > 0;
        $modelo = $this->log_inserir("C", "retiradas", $linha->id, $api);
        if ($api) {
            $modelo->nome = "APP";
            $modelo->save();
        }
    }

    protected function supervisor_consultar(Request $request) {
        $consulta = DB::table("pessoas")
                        ->where("cpf", $request->cpf)
                        ->where("senha", $request->senha)
                        ->where("supervisor", 1)
                        ->where("lixeira", 0)
                        ->get();
        return sizeof($consulta) ? $consulta[0]->id : 0;
    }

    protected function setor_mostrar($id) {
        if (intval($id)) {
            return DB::table("setores")
                        ->select(
                            "descr",
                            "cria_usuario"
                        )
                        ->where("id", $id)
                        ->first();
        }
        $resultado = new \stdClass;
        $resultado->cria_usuario = 0;
        return $resultado;
    }

    protected function mov_estoque($id_produto, $api) {
        $maquinas = DB::table("valores")
                        ->where("alias", "maquinas")
                        ->pluck("id");
        foreach ($maquinas as $maquina) {
            if (!sizeof(
                DB::table("maquinas_produtos")
                    ->where("id_produto", $id_produto)
                    ->where("id_maquina", $maquina)
                    ->get()
            )) {
                $gestor = new MaquinasProdutos;
                $gestor->id_maquina = $maquina;
                $gestor->id_produto = $id_produto;
                $gestor->save();
                $this->log_inserir("C", "maquinas_produtos", $gestor->id, $api);
            }
        }
    }

    protected function atribuicao_atualiza_ref($id, $antigo, $novo, $nome, $api = false) {
        if ($id) {
            $novo = trim($novo);
            $where = "produto_ou_referencia_valor = '".$antigo."' AND produto_ou_referencia_chave = 'R'";
            DB::statement("
                UPDATE atribuicoes
                SET ".($novo ? "produto_ou_referencia_valor = '".$novo."'" : "lixeira = 1")."
                WHERE ".$where
            );
            $this->log_inserir2($novo ? "E" : "D", "atribuicoes", $where, $nome, $api);
        }
    }
}