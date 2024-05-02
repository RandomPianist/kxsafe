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
                    referencia

                FROM estoque

                JOIN produtos
                    ON produtos.id = estoque.id_produto
            ) AS estq

            WHERE referencia IN (
                SELECT referencia
                FROM produtos
                WHERE id = ".$id."
            )
        "))[0]->saldo;
    }

    public function salvar(Request $request) {
        if (sizeof(
            DB::table("produtos")
                ->where("referencia", $request->referencia)
                ->where("lixeira", 0)
                ->get()
        )) {
            $linha = new Atribuicoes;
            $linha->referencia = $request->referencia;
            $linha->fk = $request->fk;
            $linha->tabela = $request->tabela;
            $linha->qtd = $request->qtd;        
            $linha->save();
            $log = new LogController;
            $log->inserir("C", "atribuicoes", $linha->id);
            return "1";
        }
        return "0";
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
                id,
                referencia,
                qtd

            FROM atribuicoes

            WHERE fk = ".$id
        )));
    }
}