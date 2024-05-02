<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Atribuicoes;

class AtribuicoesController extends Controller {
    public function verMaximo($id) {
        return DB::select(DB::raw("
            SELECT IFNULL(SUM(qtd), 0) AS saldo
                
            FROM (
                SELECT
                    CASE
                        WHEN (es = 'E') THEN qtd
                        ELSE qtd * -1
                    END AS qtd,
                    id_produto

                FROM estoque
            ) AS estq

            WHERE id_produto = ".$id
        ))[0]->saldo;
    }

    public function salvar(Request $request) {
        $linha = new Atribuicoes;
        $linha->id_produto = $request->id_produto;
        $linha->fk = $request->fk;
        $linha->tabela = $request->tabela;
        $linha->qtd = $request->qtd;        
        $linha->save();
        $log = new LogController;
        $log->inserir("C", "atribuicoes", $linha->id);
    }

    public function excluir(Request $request) {
        $linha = Atribuicoes::find($request->id);
        $id = $linha->id;
        $linha->delete();
        $log = new LogController;
        $log->inserir("D", "atribuicoes", $id);
    }

    public function mostrar($id) {
        return json_encode(DB::select(DB::raw("
            SELECT
                atribuicoes.id,
                descr,
                qtd

            FROM atribuicoes

            JOIN produtos
                ON produtos.id = atribuicoes.id_produto

            WHERE fk = ".$id."
              AND produtos.lixeira = 0   
        ")));
    }
}