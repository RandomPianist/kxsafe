<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Atribuicoes;

class AtribuicoesController extends Controller {
    public function verMaximo(Request $request) {
        $resultado = new \stdClass;

        $query = $request->tipo == "produto" ? "
            SELECT IFNULL(SUM(qtd), 0) AS saldo
                    
            FROM (
                SELECT
                    CASE
                        WHEN (es = 'E') THEN qtd
                        ELSE qtd * -1
                    END AS qtd,
                    id_produto

                FROM estoque

                JOIN maquinas_produtos AS mp
                    ON mp.id = estoque.id_mp
            ) AS estq

            WHERE id_produto = ".$request->id
        : "
            SELECT IFNULL(SUM(qtd), 0) AS saldo
                        
            FROM (
                SELECT
                    CASE
                        WHEN (es = 'E') THEN qtd
                        ELSE qtd * -1
                    END AS qtd,
                    referencia

                FROM estoque

                JOIN maquinas_produtos AS mp
                    ON mp.id = estoque.id_mp

                JOIN produtos
                    ON produtos.id = mp.id_produto
            ) AS estq

            WHERE referencia IN (
                SELECT referencia
                FROM produtos
                WHERE id = ".$request->id."
            )
        ";
        $resultado->maximo = DB::select(DB::raw($query))[0]->saldo;

        $query = $request->tipo == "produto" ? "
            SELECT validade

            FROM produtos

            WHERE id_produto = ".$request->id
        : "
            SELECT MAX(validade) AS validade
                        
            FROM produtos

            WHERE referencia IN (
                SELECT referencia
                FROM produtos
                WHERE id = ".$request->id."
            )
        ";
        $resultado->validade = DB::select(DB::raw($query))[0]->validade;

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
        return json_encode(
            $request->tipo == "produto" ?
                DB::table("atribuicoes")
                    ->select(
                        "atribuicoes.id",
                        "produtos.descr AS produto_ou_referencia_valor",
                        "atribuicoes.qtd",
                        "atribuicoes.validade"
                    )
                    ->join("produtos", "produtos.cod_externo", "atribuicoes.produto_ou_referencia_valor")
                    ->where("pessoa_ou_setor_valor", $request->id)
                    ->where("produto_ou_referencia_chave", $request->tipo)
                    ->where("pessoa_ou_setor_chave", $request->tipo2)
                    ->where("atribuicoes.lixeira", 0)
                    ->get()
            :
                DB::table("atribuicoes")
                    ->select(
                        "id",
                        "produto_ou_referencia_valor",
                        "qtd",
                        "validade"
                    )
                    ->where("pessoa_ou_setor_valor", $request->id)
                    ->where("produto_ou_referencia_chave", $request->tipo)
                    ->where("pessoa_ou_setor_chave", $request->tipo2)
                    ->where("lixeira", 0)
                    ->get()
        );
    }
}