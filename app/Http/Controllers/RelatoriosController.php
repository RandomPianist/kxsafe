<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\EmpresasController;

class RelatoriosController extends Controller {
    private function consultar_maquina(Request $request) {
        return (!sizeof(
            DB::table("valores")
                ->where("id", $request->id_maquina)
                ->where("descr", $request->maquina)
                ->where("lixeira", 0)
                ->get()
        ));
    }

    private function comum() {
        return "
            FROM comodatos

            JOIN valores
                ON valores.id = comodatos.id_maquina

            JOIN empresas
                ON empresas.id = comodatos.id_empresa

            WHERE valores.lixeira = 0
              AND empresas.lixeira = 0
        ";
    }

    private function bilateral_construtor(Request $request, $grupo) {
        $filtro = array();
        if ($request->id_empresa) array_push($filtro, "id_empresa = ".$request->id_empresa);
        if ($request->id_maquina) array_push($filtro, "id_maquina = ".$request->id_maquina);
        $filtro = join(" AND ", $filtro);
        if (!$filtro) $filtro = "1";
        return collect(DB::select(DB::raw("
            SELECT
                empresas.nome_fantasia AS col1,
                valores.descr AS col2
            
            ".$this->comum()."
            
            AND ".$filtro."
            AND CURDATE() >= inicio
            AND CURDATE() < fim

            ORDER BY valores.descr
        ")))->groupBy($grupo);
    }

    private function maquinas_por_empresa(Request $request) {
        $resultado = $this->bilateral_construtor($request, "col1")->map(function($itens) {
            return [
                "col1" => $itens[0]->col1,
                "col2" => $itens->map(function($col2) {
                    return $col2->col2;
                })->values()->all()
            ];
        })->sortBy("col1")->values()->all();
        $criterios = array();
        if ($request->id_maquina) array_push($criterios, "Máquina: ".$request->maquina);
        if ($request->id_empresa) array_push($criterios, "Empresa: ".$request->empresa);
        $criterios = join(" | ", $criterios);
        $titulo = "Máquinas por empresa";
        return sizeof($resultado) ? view("reports/bilateral", compact("resultado", "criterios", "titulo")) : view("nada");
    }

    private function empresas_por_maquina(Request $request) {
        $resultado = $this->bilateral_construtor($request, "col2")->map(function($itens) {
            return [
                "col1" => $itens[0]->col2,
                "col2" => $itens->map(function($col2) {
                    return $col2->col1;
                })->values()->all()
            ];
        })->sortBy("col1")->values()->all();
        $criterios = array();
        if ($request->id_maquina) array_push($criterios, "Máquina: ".$request->maquina);
        if ($request->id_empresa) array_push($criterios, "Empresa: ".$request->empresa);
        $criterios = join(" | ", $criterios);
        $titulo = "Empresas por máquina";
        return sizeof($resultado) ? view("reports/bilateral", compact("resultado", "criterios", "titulo")) : view("nada");
    }

    public function bilateral(Request $request) {
        if ($request->rel_grupo == "empresas-por-maquina") return $this->empresas_por_maquina($request);
        else return $this->maquinas_por_empresa($request);
    }

    public function bilateral_consultar(Request $request) {
        $erro = "";
        $emp_controller = new EmpresasController;
        if ($request->prioridade == "empresas") {
            if ($emp_controller->consultar_solo($request) && trim($request->empresa)) $erro = "empresa";
            if (!$erro && $this->consultar_maquina($request) && trim($request->maquina)) $erro = "maquina";
        } else {
            if ($this->consultar_maquina($request) && trim($request->maquina)) $erro = "maquina";
            if (!$erro && $emp_controller->consultar_solo($request) && trim($request->empresa)) $erro = "empresa";
        }
        return $erro;
    }

    public function comodatos() {
        $resultado = DB::select(DB::raw("
            SELECT
                valores.descr AS maquina,
                empresas.nome_fantasia AS empresa,
                DATE_FORMAT(comodatos.inicio, '%d/%m/%Y') AS inicio,
                DATE_FORMAT(comodatos.fim, '%d/%m/%Y') AS fim

            ".$this->comum()."

            ORDER BY comodatos.inicio
        "));
        return sizeof($resultado) ? view("reports/comodatos", compact("resultado")) : view("nada");
    }

    public function extrato(Request $request) {
        $filtro = array();
        $criterios = array();
        $periodo = "";
        if ($request->inicio || $request->fim) $periodo = "Período";
        if ($request->inicio) {
            $inicio = Carbon::createFromFormat('d/m/Y', $request->inicio)->format('Y-m-d');
            array_push($filtro, "DATE(log.created_at) >= '".$inicio."'");
            $periodo .= " de ".$request->inicio;
        }
        if ($request->fim) {
            $fim = Carbon::createFromFormat('d/m/Y', $request->fim)->format('Y-m-d');
            array_push($filtro, "DATE(log.created_at) <= '".$fim."'");
            $periodo .= " até ".$request->fim;
        }
        if ($periodo) array_push($criterios, $periodo);
        if ($request->id_maquina) {
            array_push($criterios, "Máquina: ".$request->maquina);
            array_push($filtro, "estoque.id_maquina = ".$request->id_maquina);
        }
        if ($request->id_produto) {
            array_push($criterios, "Produto: ".$request->produto);
            array_push($filtro, "estoque.id_produto = ".$request->id_produto);
        }
        $filtro = join(" AND ", $filtro);
        if (!$filtro) $filtro = "1";
        $lm = $request->lm == "S";
        $resultado = collect(DB::select(DB::raw("
            SELECT
                /* GRUPO 1 */
                valores.descr AS maquina,

                /* GRUPO 2 */
                produtos.descr AS produto,
                IFNULL(ge.preco, produtos.preco) AS preco,

                /* DETALHES */
                DATE_FORMAT(log.created_at, '%d/%m/%Y %H:%i') AS data,
                estoque.es,
                estoque.descr AS estoque_descr,
                CASE
                    WHEN (es = 'E') THEN qtd
                    ELSE qtd * -1
                END AS qtd,
                IFNULL(pessoas.nome, CONCAT(
                    'API',
                    IFNULL(CONCAT(' - ', log.nome), '')
                )) AS autor

            FROM log

            JOIN estoque
                ON estoque.id = log.fk

            JOIN produtos
                ON produtos.id = estoque.id_produto

            JOIN valores
                ON valores.id = estoque.id_maquina

            JOIN gestor_estoque AS ge
                ON ge.id_maquina = estoque.id_maquina AND ge.id_produto = estoque.id_produto

            LEFT JOIN pessoas
                ON pessoas.id = log.id_pessoa

            WHERE log.tabela = 'estoque'
              AND ".$filtro."
              AND produtos.lixeira = 0
              AND valores.lixeira = 0

            ORDER BY log.created_at
        ")))->groupBy("maquina")->map(function($itens1) {
            return [
                "maquina" => [
                    "descr" => $itens1[0]->maquina,
                    "produtos" => collect($itens1)->groupBy("produto")->map(function($itens2) {
                        return [
                            "descr" => $itens2[0]->produto,
                            "preco" => $itens2[0]->preco,
                            "saldo" => $itens2->sum("qtd"),
                            "movimentacao" => $itens2->map(function($movimento) {
                                $qtd = floatval($movimento->qtd);
                                return [
                                    "data"  => $movimento->data,
                                    "es"    => $movimento->es,
                                    "descr" => $movimento->estoque_descr,
                                    "qtd"   => ($qtd < 0 ? ($qtd * -1) : $qtd),
                                    "autor" => $movimento->autor
                                ];
                            })->values()->all()
                        ];
                    })->sortBy("descr")->values()->all()
                ]
            ];
        })->sortBy("descr")->values()->all();
        $criterios = join(" | ", $criterios);
        return sizeof($resultado) ? view("reports/extrato", compact("resultado", "lm", "criterios")) : view("nada");
    }

    public function extrato_consultar(Request $request) {
        $erro = "";
        if ($this->consultar_maquina($request) && trim($request->maquina)) $erro = "maquina";
        if (!$erro && trim($request->produto) && (
            DB::table("produtos")
                ->where("id", $request->id_produto)
                ->where("descr", $request->produto)
                ->where("lixeira", 0)
                ->get()
        )) $erro = "produto";
        return $erro;
    }

    public function retiradas(Request $request) {
        $filtro = array();
        $criterios = array();
        $periodo = "";
        if ($request->inicio || $request->fim) $periodo = "Período";
        if ($request->inicio) {
            $inicio = Carbon::createFromFormat('d/m/Y', $request->inicio)->format('Y-m-d');
            array_push($filtro, "DATE(log.created_at) >= '".$inicio."'");
            $periodo .= " de ".$request->inicio;
        }
        if ($request->fim) {
            $fim = Carbon::createFromFormat('d/m/Y', $request->fim)->format('Y-m-d');
            array_push($filtro, "DATE(log.created_at) <= '".$fim."'");
            $periodo .= " até ".$request->fim;
        }
        if ($periodo) array_push($criterios, $periodo);
        if ($request->id_pessoa) {
            array_push($criterios, "Colaborador: ".$request->pessoa);
            array_push($filtro, "retiradas.id_pessoa = ".$request->id_pessoa);
        }
        $filtro = join(" AND ", $filtro);
        if (!$filtro) $filtro = "1";
        
        $resultado = collect(DB::select(DB::raw("
            SELECT
                retiradas.id_pessoa,
                pessoas.nome,
                produtos.descr AS produto,
                valores.descr AS maquina,
                DATE_FORMAT(retiradas.created_at, '%d/%m/%Y') AS data,
                IFNULL(CONCAT('Liberado por ', supervisor.nome, IFNULL(CONCAT(' - ', retiradas.obs), '')), '') AS obs

            FROM retiradas

            JOIN produtos
                ON produtos.id = retiradas.id_produto

            JOIN pessoas
                ON pessoas.id = retiradas.id_pessoa

            JOIN comodatos
                ON comodatos.id = retiradas.id_comodato

            JOIN valores
                ON valores.id = comodatos.id_maquina

            LEFT JOIN pessoas AS supervisor
                ON supervisor.id = retiradas.id_supervisor

            WHERE ".$filtro."

            ORDER BY retiradas.id
        ")))->groupBy("id_pessoa")->map(function($itens) {
            return [
                "nome" => $itens[0]->nome,
                "retiradas" => $itens->map(function($retirada) {
                    return [
                        "produto" => $retirada->produto,
                        "maquina" => $retirada->maquina,
                        "data"    => $retirada->data,
                        "obs"     => $retirada->obs
                    ];
                })->values()->all()
            ];
        })->sortBy("nome")->values()->all();
        $criterios = join(" | ", $criterios);
        return sizeof($resultado) ? view("reports/retiradas", compact("resultado", "criterios")) : view("nada");
    }

    public function retiradas_consultar(Request $request) {
        return (!sizeof(
            DB::table("pessoas")
                ->where("id", $request->id_pessoa)
                ->where("nome", $request->pessoa)
                ->where("lixeira", 0)
                ->get()
        )) ? "erro" : "";
    }
}
