<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Pessoas;
use App\Models\Valores;

class ValoresController extends Controller {
    private function busca($alias, $param) {
        $query = "
            SELECT
                valores.id,
                valores.seq,
                valores.descr,
                valores.alias,
                CASE
                    WHEN aux3.id_maquina IS NOT NULL THEN 'S'
                    ELSE 'N'
                END AS tem_mov,
                CASE
                    WHEN aux2.id IS NOT NULL THEN CONCAT(
                        aux2.nome_fantasia,
                        ' até ',
                        aux1.fim_formatado
                    ) ELSE '---'
                END AS comodato

            FROM valores

            LEFT JOIN (
                SELECT
                    id_maquina,
                    id_empresa,
                    DATE_FORMAT(fim, '%d/%m/%Y') AS fim_formatado

                FROM comodatos

                WHERE CURDATE() >= inicio
                  AND CURDATE() < fim
            ) AS aux1 ON aux1.id_maquina = valores.id

            LEFT JOIN (
                SELECT
                    id,
                    id_matriz,
                    nome_fantasia
                FROM empresas
                WHERE lixeira = 0
            ) AS aux2 ON aux2.id = aux1.id_empresa

            LEFT JOIN (
                SELECT DISTINCTROW id_maquina
                FROM estoque
            ) AS aux3 ON aux3.id_maquina = valores.id

            WHERE ".$param."
              AND lixeira = 0
              AND alias = '".$alias."'
        ";
        if ($alias == "maquinas") {
            $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
            if ($id_emp) $query .= " AND ".$id_emp." IN (aux2.id, aux2.id_matriz)";
        }
        return DB::select(DB::raw($query));
    }

    public function ver($alias) {
        $comodato = false;
        if ($alias == "maquinas") {
            $busca = $this->busca($alias, "1");
            foreach($busca as $linha) {
                if ($linha->comodato != "---") $comodato = true;
            }
        }
        $log = new LogController;
        $ultima_atualizacao = $log->consultar("valores", $alias);
        $titulo = $alias == "maquinas" ? "Máquinas" : "Categorias";
        return view("valores", compact("alias", "titulo", "ultima_atualizacao", "comodato"));
    }

    public function listar($alias, Request $request) {
        $filtro = trim($request->filtro);
        if (strlen($filtro)) {
            $busca = $this->busca($alias, "descr LIKE '".$filtro."%'");
            if (sizeof($busca) < 3) $busca = $this->busca($alias, "descr LIKE '%".$filtro."%'");
            if (sizeof($busca) < 3) $busca = $this->busca($alias, "(descr LIKE '%".implode("%' AND descr LIKE '%", explode(" ", str_replace("  ", " ", $filtro)))."%')");
        } else $busca = $this->busca($alias, "1");
        return json_encode($busca);
    }

    public function consultar($alias, Request $request) {
        if (sizeof(
            DB::table("valores")
                ->where("alias", $alias)
                ->where("lixeira", 0)
                ->where("descr", $request->descr)
                ->get()
        )) return "1";
        return "0";
    }

    public function mostrar($alias, $id) {
        return Valores::find($id)->descr;
    }

    public function aviso($alias, $id) {
        $aviso = "";
        if ($alias == "maquinas") {
            $aviso = DB::select(DB::raw("
                SELECT
                    CASE
                        WHEN (tab_comodatos.id_maquina IS NOT NULL) THEN CONCAT('está comodatada para ', tab_comodatos.empresa, ' até ', tab_comodatos.fim)
                        WHEN (tab_estoque.saldo <> 0) THEN 'possui saldo diferente de zero'
                        ELSE ''
                    END AS aviso
                
                FROM valores
                
                LEFT JOIN (
                    SELECT
                        IFNULL(SUM(qtd), 0) AS saldo,
                        id_maquina
                        
                    FROM (
                        SELECT
                            CASE
                                WHEN (es = 'E') THEN qtd
                                ELSE qtd * -1
                            END AS qtd,
                            id_maquina
                
                        FROM estoque
                    ) AS estq
                
                    GROUP BY id_maquina
                ) AS tab_estoque ON tab_estoque.id_maquina = valores.id
                
                LEFT JOIN (
                    SELECT
                        id_maquina,
                        empresas.nome_fantasia AS empresa,
                        DATE_FORMAT(fim, '%d/%m/%Y') AS fim
                    
                    FROM comodatos
                    
                    JOIN empresas
                        ON empresas.id = comodatos.id_empresa
                    
                    WHERE CURDATE() >= inicio
                      AND CURDATE() < fim
                ) AS tab_comodatos ON tab_comodatos.id_maquina = valores.id

                WHERE valores.id = ".$id
            ))[0]->aviso;
            $vinculo = $aviso != "";
        } else {
            $vinculo = sizeof(
                DB::table("produtos")
                    ->where("id_categoria", $id)
                    ->where("lixeira", 0)
                    ->get()
            ) > 0;
        }
        $resultado = new \stdClass;
        $nome = Valores::find($id)->descr;
        $resultado->permitir = !$vinculo || $alias == "categorias" ? 1 : 0;
        $resultado->aviso = $vinculo ?
            $alias == "categorias" ?
                "Não é recomendado excluir ".$nome." porque existem produtos vinculados a essa categoria.<br>Deseja prosseguir assim mesmo?"
            :
                "Não é possível excluir ".$nome." porque essa máquina ".$aviso
        : "Tem certeza que deseja excluir ".$nome."?";
        return json_encode($resultado);
    }

    public function salvar($alias, Request $request) {
        $linha = Valores::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->alias = $alias;
        if (!$request->id) {
            $linha->seq = intval(DB::select(DB::raw("
                SELECT IFNULL(MAX(seq), 0) AS ultimo
                FROM valores
                WHERE alias = '".$alias."'
            "))[0]->ultimo) + 1;
        }
        $linha->save();
        $log = new LogController;
        $log->inserir($request->id ? "E" : "C", "valores", $linha->id);
        return redirect("/valores/$alias");
    }

    public function excluir($alias, Request $request) {
        $linha = Valores::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        DB::statement("
            UPDATE produtos
            SET id_categoria = NULL
            WHERE id_categoria = ".$request->id
        );
        $log = new LogController;
        $log->inserir("D", "valores", $linha->id);
    }
}