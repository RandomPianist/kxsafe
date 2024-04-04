<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Valores;
use App\Models\Produtos;
use App\Models\Estoque;

class ApiController extends Controller {
    public function empresas() {
        return json_encode(DB::select(DB::raw("
            SELECT
                empresas.id,
                CONCAT(
                    empresas.razao_social,
                    IFNULL(CONCAT(' - ', matriz.razao_social), '')
                ) AS descr

            FROM empresas

            LEFT JOIN empresas AS matriz
                ON matriz.id = empresas.id_matriz

            WHERE empresas.lixeira = 0
              AND matriz.lixeira = 0
        ")));
    }

    public function maquinas(Request $request) {
        $query = "
            SELECT
                tab.id,
                tab.descr
            
            FROM (
                SELECT
                    id,
                    descr
                FROM valores
                WHERE alias = 'maquinas'
                  AND lixeira = 0
            ) AS tab
        ";
        if (isset($request->idEmp)) {
            $query .= "
                JOIN (
                    SELECT id_maquina
                    FROM comodatos
                    WHERE id_empresa = ".$request->idEmp."
                      AND CURDATE() >= inicio
                      AND CURDATE() < fim
                ) AS aux ON aux.id_maquina = tab.id
            ";
        }
        return DB::select(DB::raw($query));
    }

    public function produtos_por_maquina(Request $request) {
        $consulta = DB::select(DB::raw("
            SELECT
                produtos.id,
                produtos.descr,
                tab.saldo

            FROM (
                SELECT
                    IFNULL(SUM(qtd), 0) AS saldo,
                    id_maquina,
                    id_produto
                    
                FROM (
                    SELECT
                        CASE
                            WHEN (es = 'E') THEN qtd
                            ELSE qtd * -1
                        END AS qtd,
                        id_maquina,
                        id_produto
            
                    FROM estoque
                ) AS estq
            
                GROUP BY
                    id_maquina,
                    id_produto
            ) AS tab

            JOIN produtos
                ON produtos.id = tab.id_produto

            WHERE tab.saldo > 0
              AND tab.id_maquina = ".$request->idMaquina."
              AND produtos.lixeira = 0
        "));
        foreach ($consulta as $linha) $linha->saldo = floatval($linha->saldo);
        return json_encode($consulta);
    }

    public function categorias(Request $request) {
        $linha = Valores::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->alias = "categorias";
        if (!$request->id) {
            $linha->seq = intval(DB::select(DB::raw("
                SELECT IFNULL(MAX(seq), 0) AS ultimo
                FROM valores
                WHERE alias = 'categorias'
            "))[0]->ultimo) + 1;
        }
        $linha->save();
        $log = new LogController;
        $log->inserir($request->id ? "E" : "C", "valores", $linha->id, true);
        $resultado = new \stdClass;
        $resultado->id = $linha->id;
        $resultado->descr = $linha->descr;
        return json_encode($resultado);
    }

    public function produtos(Request $request) {
        $linha = Produtos::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->preco = $request->preco;
        $linha->validade = $request->validade;
        $linha->ca = $request->ca;
        $linha->cod_externo = $request->codExterno;
        $linha->id_categoria = $request->idCategoria;
        $linha->foto = $request->foto;
        $linha->lixeira = $request->lixeira;
        $linha->save();
        $log = new LogController;
        $letra_log = $request->id ? "E" : "C";
        if (intval($request->lixeira)) $letra_log = "D";
        $log->inserir($letra_log, "produtos", $linha->id, true);
        $consulta = DB::table("produtos")
            ->select(
                "id",
                "descr",
                "preco",
                "validade",
                "ca",
                "lixeira",
                "id_categoria AS idCategoria",
                "cod_externo AS codExterno",
                "foto"
            )
            ->where("id", $linha->id)
            ->first();
        $consulta->preco = floatval($consulta->preco);
        $consulta->lixeira = intval($consulta->lixeira);
        return json_encode($consulta);
    }

    public function estoque(Request $request) {
        for ($i = 0; $i < sizeof($request->idProduto); $i++) {
            $linha = new Estoque;
            $linha->es = $request->es[$i];
            $linha->descr = $request->obs[$i];
            $linha->qtd = $request->qtd[$i];
            $linha->id_produto = $request->idProduto[$i];
            $linha->id_maquina = $request->idMaquina;
            $linha->save();
            $log = new LogController;
            $log->inserir("C", "estoque", $linha->id, true);
        }
        return 200;
    }
}