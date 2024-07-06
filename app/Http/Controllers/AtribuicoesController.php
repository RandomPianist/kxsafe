<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Atribuicoes;
use App\Models\Retiradas;

class AtribuicoesController extends Controller {
    public function verMaximo(Request $request) {
        $resultado = new \stdClass;

        $subquery = "(
            SELECT
                CASE
                    WHEN (es = 'E') THEN qtd
                    ELSE qtd * -1
                END AS qtd,
        ";
        $subquery .= $request->tipo == "produto" ? "id_produto" : "referencia";
        $subquery .= " FROM estoque";
        $subquery .= " JOIN maquinas_produtos AS mp ON mp.id = estoque.id";
        if ($request->tipo == "referencia") $subquery .= " JOIN produtos ON produtos.id = mp.id_produto";
        $subquery .= ") AS estq";

        $where = $request->tipo == "produto" ? "id_produto = ".$request->id : "referencia IN (
            SELECT referencia
            FROM produtos
            WHERE id = ".$request->id."
        )";

        $resultado->maximo = DB::table(DB::raw($subquery))
                                    ->selectRaw("IFNULL(SUM(qtd), 0) AS saldo")
                                    ->whereRaw($where)
                                    ->value("saldo");
        
        $resultado->validade = DB::table("produtos")
                                    ->selectRaw($request->tipo == "produto" ? "validade" : "MAX(validade) AS validade")
                                    ->whereRaw(str_replace("id_produto", "id", $where))
                                    ->value("validade");

        return json_encode($resultado);
    }

    public function salvar(Request $request) {
        if (!sizeof(
            DB::table("produtos")
                ->where($request->produto_ou_referencia_chave == "produto" ? "descr" : "referencia", $request->produto_ou_referencia_valor)
                ->where("lixeira", 0)
                ->get()
        )) return 404;
        $produto_ou_referencia_valor = $request->produto_ou_referencia_chave == "produto" ?
            DB::table("produtos")
                ->where("descr", $request->produto_ou_referencia_valor)
                ->where("lixeira", 0)
                ->value("cod_externo")
        : $request->produto_ou_referencia_valor;
        if (sizeof(
            DB::table("atribuicoes")
                ->where("pessoa_ou_setor_chave", $request->pessoa_ou_setor_chave)
                ->where("pessoa_ou_setor_valor", $request->pessoa_ou_setor_valor)
                ->where("produto_ou_referencia_valor", $produto_ou_referencia_valor)
                ->where("produto_ou_referencia_chave", $request->produto_ou_referencia_chave)
                ->where("lixeira", 0)
                ->get()
        )) return 403;
        $linha = new Atribuicoes;
        $linha->pessoa_ou_setor_chave = $request->pessoa_ou_setor_chave;
        $linha->pessoa_ou_setor_valor = $request->pessoa_ou_setor_valor;
        $linha->produto_ou_referencia_chave = $request->produto_ou_referencia_chave;
        $linha->produto_ou_referencia_valor = $produto_ou_referencia_valor;
        $linha->qtd = $request->qtd;
        $linha->validade = $request->validade;
        $linha->save();
        $log = new LogController;
        $log->inserir("C", "atribuicoes", $linha->id);
        return 201;
    }

    public function excluir(Request $request) {
        $linha = Atribuicoes::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $log = new LogController;
        $log->inserir("D", "atribuicoes", $linha->id);
    }

    public function mostrar(Request $request) {
        $query = "SELECT atribuicoes.id, ";
        if ($request->tipo == "produto") $query .= "produtos.descr AS ";
        $query .= "produto_ou_referencia_valor, ";
        $query .= "
                atribuicoes.qtd,
                atribuicoes.validade

            FROM atribuicoes
        ";
        if ($request->tipo == "produto") $query .= " JOIN produtos ON produtos.cod_externo = produto_ou_referencia_valor ";
        $query .= "WHERE
                pessoa_ou_setor_valor = ".$request->id."
            AND produto_ou_referencia_chave = '".$request->tipo."'
            AND pessoa_ou_setor_chave = '".$request->tipo2."'
            AND atribuicoes.lixeira = 0
        ";
        return json_encode(DB::select(DB::raw($query)));
    }

    public function podeRetirar($id, $qtd) {
        $atribuicao = Atribuicoes::find($id);
        $ja_retirados = DB::table("retiradas")
                            ->selectRaw("IFNULL(SUM(retiradas.qtd), 0) AS qtd")
                            ->join("atribuicoes", "atribuicoes.id", "retiradas.id_atribuicao")
                            ->whereRaw("DATE_ADD(retiradas.data, INTERVAL atribuicoes.validade DAY) >= CURDATE()")
                            ->where("atribuicoes.id", $id)
                            ->get();
        if (floatval($atribuicao->qtd) < (floatval($qtd) + (sizeof($ja_retirados) ? floatval($ja_retirados[0]->qtd) : 0))) return 0;
        return 1;
    }

    public function produtos($id) {
        return json_encode(
            DB::table("produtos")
                ->select(
                    "produtos.id",
                    DB::raw("CASE
                        WHEN produto_ou_referencia_chave = 'referencia' THEN CONCAT(produtos.descr, ' ', tamanho)
                        ELSE produtos.descr
                    END AS descr"),
                    DB::raw("CASE
                        WHEN produto_ou_referencia_chave = 'referencia' THEN produtos.referencia
                        ELSE produtos.descr
                    END AS titulo")
                )
                ->join("atribuicoes", function($join) {
                    $join->on(function($sql) {
                        $sql->on("atribuicoes.produto_ou_referencia_valor", "produtos.cod_externo")
                            ->where("atribuicoes.produto_ou_referencia_chave", "produto");
                    })->orOn(function($sql) {
                        $sql->on("atribuicoes.produto_ou_referencia_valor", "produtos.referencia")
                            ->where("atribuicoes.produto_ou_referencia_chave", "referencia");
                    });
                })
                ->where("atribuicoes.id", $id)
                ->where("produtos.lixeira", 0)
                ->get()
        );
    }

    public function retirar(Request $request) {
        $linha = new Retiradas;
        $atribuicao = Atribuicoes::find($request->atribuicao);
        $linha->id_pessoa = $atribuicao->pessoa_ou_setor_valor;
        if (intval($request->supervisor)) $linha->id_supervisor = $request->supervisor;
        $linha->id_atribuicao = $request->atribuicao;
        $linha->id_produto = $request->produto;
        $linha->id_comodato = 0;
        $linha->qtd = $request->quantidade;
        $linha->data = Carbon::createFromFormat('d/m/Y', $request->data)->format('Y-m-d');
        $linha->save();
        $log = new LogController;
        $log->inserir("C", "retiradas", $linha->id, false);
    }
}