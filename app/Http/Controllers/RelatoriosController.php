<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Pessoas;

class RelatoriosController extends ControllerKX {
    private function consultar_maquina(Request $request) {
        return (!sizeof(
            DB::table("valores")
                ->where("id", $request->id_maquina)
                ->where("descr", $request->maquina)
                ->where("lixeira", 0)
                ->get()
        ));
    }

    private function comum($select) {
        return DB::table("comodatos")
                    ->join("valores", "valores.id", "comodatos.id_maquina")
                    ->join("empresas", "empresas.id", "comodatos.id_empresa")
                    ->select(DB::raw($select))
                    ->where("valores.lixeira", 0)
                    ->where("empresas.lixeira", 0);
    }

    private function bilateral_construtor(Request $request, $grupo) {
        $filtro = array();
        if ($request->id_empresa) array_push($filtro, "id_empresa = ".$request->id_empresa);
        if ($request->id_maquina) array_push($filtro, "id_maquina = ".$request->id_maquina);
        $filtro = join(" AND ", $filtro);
        if (!$filtro) $filtro = "1";
        return collect(
            $this->comum("
                empresas.nome_fantasia AS col1,
                valores.descr AS col2
            ")->whereRaw($filtro."
                AND CURDATE() >= inicio
                AND CURDATE() < fim
            ")->orderby("valores.descr")->get()
        )->groupBy($grupo);
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

    private function retiradasMain(Request $request) {
        $retorno = new \stdClass;
        $criterios = array();
        $retorno->resultado = collect(
            DB::table("retiradas")
                ->select(
                    "retiradas.id_pessoa",
                    "pessoas.nome",
                    "pessoas.cpf",
                    "pessoas.admissao",
                    "pessoas.funcao",
                    "setores.descr AS setor",
                    "produtos.descr AS produto",
                    "produtos.ca",
                    "empresas.razao_social",
                    "empresas.cnpj",
                    "produtos.validade_ca",
                    "retiradas.qtd",
                    DB::raw("DATE_FORMAT(retiradas.data, '%d/%m/%Y') AS data"),
                    DB::raw("IFNULL(CONCAT('Liberado por ', supervisor.nome, IFNULL(CONCAT(' - ', retiradas.observacao), '')), '') AS obs")
                )
                ->join("produtos", "produtos.id", "retiradas.id_produto")
                ->join("pessoas", "pessoas.id", "retiradas.id_pessoa")
                ->leftjoin("comodatos", "comodatos.id", "retiradas.id_comodato")
                ->leftjoin("valores", "valores.id", "comodatos.id_maquina")
                ->leftjoin("pessoas AS supervisor", "supervisor.id", "retiradas.id_supervisor")
                ->leftjoin("empresas", "empresas.id", "pessoas.id_empresa")
                ->leftjoin("setores", "setores.id", "pessoas.id_setor")
                ->where(function($sql) use($request, &$criterios) {
                    if ($request->inicio || $request->fim) {
                        $periodo = "Período";
                        if ($request->inicio) {
                            $inicio = Carbon::createFromFormat('d/m/Y', $request->inicio)->format('Y-m-d');
                            $sql->whereDate("retiradas.data", ">=", $inicio);
                            $periodo .= " de ".$request->inicio;
                        }
                        if ($request->fim) {
                            $fim = Carbon::createFromFormat('d/m/Y', $request->fim)->format('Y-m-d');
                            $sql->whereDate("retiradas.data", "<=", $fim);
                            $periodo .= " até ".$request->fim;
                        }
                        array_push($criterios, $periodo);
                    }
                    $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
                    if ($request->id_pessoa) {
                        $pessoa = DB::table("pessoas")
                                        ->where("id", $request->id_pessoa)
                                        ->value("nome");
                        array_push($criterios, "Colaborador: ".$pessoa);
                        $sql->where("retiradas.id_pessoa", $request->id_pessoa);
                    } else if ($id_emp) {
                        $sql->where(function($query) use($id_emp) {
                            $query->where("pessoas.id_empresa", $id_emp)
                                ->orWhere("empresas.id_matriz", $id_emp)
                                ->orWhere("empresas.id", $id_emp);
                        });
                    }
                    if ($request->consumo != "todos") $sql->where("produtos.consumo", $request->consumo == "epi" ? 0 : 1);
                })
                ->orderby("retiradas.id")
                ->get()
        )->groupBy("id_pessoa")->map(function($itens) {
            return [
                "nome" => $itens[0]->nome,
                "cpf" => $itens[0]->cpf,
                "admissao" => $itens[0]->admissao,
                "funcao" => $itens[0]->funcao,
                "setor" => $itens[0]->setor,
                "empresa" => $itens[0]->razao_social,
                "cnpj" => $itens[0]->cnpj,
                "retiradas" => $itens->map(function($retirada) {
                    return [
                        "produto"     => $retirada->produto,
                        "data"        => $retirada->data,
                        "obs"         => $retirada->obs,
                        "ca"          => $retirada->ca,
                        "validade_ca" => $retirada->validade_ca,
                        "qtd"         => $retirada->qtd,
                    ];
                })->values()->all()
            ];
        })->sortBy("nome")->values()->all();
        $retorno->criterios = join(" | ", $criterios);
        $retorno->cidade = "Barueri";
        $retorno->data_extenso = ucfirst(strftime("%d de %B de %Y"));
        return $retorno;
    }

    public function bilateral(Request $request) {
        if ($request->rel_grupo == "empresas-por-maquina") return $this->empresas_por_maquina($request);
        return $this->maquinas_por_empresa($request);
    }

    public function bilateral_consultar(Request $request) {
        $erro = "";
        if ($request->prioridade == "empresas") {
            if (($this->empresa_consultar($request) && trim($request->empresa)) || (trim($request->id_empresa) && !trim($request->empresa))) $erro = "empresa";
            if (!$erro && (($this->consultar_maquina($request) && trim($request->maquina)) || (trim($request->id_maquina) && !trim($request->maquina)))) $erro = "maquina";
        } else {
            if (($this->consultar_maquina($request) && trim($request->maquina)) || (trim($request->id_maquina) && !trim($request->maquina))) $erro = "maquina";
            if (!$erro && ($this->empresa_consultar($request) && trim($request->empresa)) || (trim($request->id_empresa) && !trim($request->empresa))) $erro = "empresa";
        }
        return $erro;
    }

    public function comodatos() {
        $resultado = $this->comum("
            valores.descr AS maquina,
            empresas.nome_fantasia AS empresa,
            DATE_FORMAT(comodatos.inicio, '%d/%m/%Y') AS inicio,
            DATE_FORMAT(comodatos.fim, '%d/%m/%Y') AS fim
        ")->orderby("comodatos.inicio")->get();
        return sizeof($resultado) ? view("reports/comodatos", compact("resultado")) : view("nada");
    }

    public function extrato(Request $request) {
        $criterios = array();
        $lm = $request->lm == "S";
        $resultado = collect(
            DB::table("log")
                ->select(
                    // GRUPO 1
                    "valores.id AS id_maquina",
                    "valores.descr AS maquina",

                    // GRUPO 2
                    "produtos.id AS id_produto",
                    "produtos.descr AS produto",
                    DB::raw("IFNULL(mp.preco, produtos.preco) AS preco"),

                    // DETALHES
                    DB::raw("DATE_FORMAT(log.created_at, '%d/%m/%Y %H:%i') AS data"),
                    "estoque.es",
                    "estoque.descr AS estoque_descr",
                    DB::raw("
                        CASE
                            WHEN (es = 'E') THEN qtd
                            ELSE qtd * -1
                        END AS qtd
                    "),
                    DB::raw("
                        IFNULL(pessoas.nome, CONCAT(
                            'API',
                            IFNULL(CONCAT(' - ', log.nome), '')
                        )) AS autor
                    ")
                )
                ->join("estoque", "estoque.id", "log.fk")
                ->join("maquinas_produtos AS mp", "mp.id", "estoque.id_mp")
                ->join("produtos", "produtos.id", "mp.id_produto")
                ->join("valores", "valores.id", "mp.id_maquina")
                ->leftjoin("pessoas", "pessoas.id", "log.id_pessoa")
                ->where(function($sql) use($request, &$criterios) {
                    if ($request->inicio || $request->fim) {
                        $periodo = "Período";
                        if ($request->inicio) {
                            $inicio = Carbon::createFromFormat('d/m/Y', $request->inicio)->format('Y-m-d');
                            $sql->whereRaw("DATE(log.created_at) >= '".$inicio."'");
                            $periodo .= " de ".$request->inicio;
                        }
                        if ($request->fim) {
                            $fim = Carbon::createFromFormat('d/m/Y', $request->fim)->format('Y-m-d');
                            $sql->whereRaw("DATE(log.created_at) <= '".$fim."'");
                            $periodo .= " até ".$request->fim;
                        }
                        array_push($criterios, $periodo);
                    }
                    if ($request->id_maquina) {
                        $maquina = DB::table("valores")
                                        ->where("id", $request->id_maquina)
                                        ->value("descr");
                        array_push($criterios, "Máquina: ".$maquina);
                        $sql->where("mp.id_maquina", $request->id_maquina);
                    }
                    if ($request->id_produto) {
                        $produto = DB::table("produtos")
                                        ->where("id", $request->id_produto)
                                        ->value("descr");
                        array_push($criterios, "Produto: ".$produto);
                        $sql->where("mp.id_produto", $request->id_produto);
                    }
                })
                ->where("log.tabela", "estoque")
                ->where("produtos.lixeira", 0)
                ->where("valores.lixeira", 0)
                ->orderby("log.id")
                ->get()
        )->groupBy("id_maquina")->map(function($itens1) {
            return [
                "maquina" => [
                    "descr" => $itens1[0]->maquina,
                    "produtos" => collect($itens1)->groupBy("id_produto")->map(function($itens2) {
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
        if (!$erro && trim($request->produto) && !sizeof(
            DB::table("produtos")
                ->where("id", $request->id_produto)
                ->where("descr", $request->produto)
                ->where("lixeira", 0)
                ->get()
        )) $erro = "produto";
        if (trim($request->id_maquina) && !trim($request->maquina)) $erro = "maquina";
        if (trim($request->id_produto) && !trim($request->produto)) $erro = "produto";
        return $erro;
    }

    public function retiradas(Request $request) {
        $principal = $this->retiradasMain($request);
        $resultado = $principal->resultado;
        $criterios = $principal->criterios;
        $cidade = $principal->cidade;
        $data_extenso = $principal->data_extenso;
        return sizeof($resultado) ? view("reports/retiradas", compact("resultado", "criterios", "cidade", "data_extenso")) : view("nada");
    }

    public function retiradas_consultar(Request $request) {
        return ((!sizeof(
            DB::table("pessoas")
                ->where("id", $request->id_pessoa)
                ->where("nome", $request->pessoa)
                ->where("lixeira", 0)
                ->get()
        ) && trim($request->pessoa)) || (!trim($request->pessoa) && trim($request->id_pessoa))) ? "erro" : "";
    }

    public function retiradas_existe(Request $request) {
        return sizeof($this->retiradasMain($request)->resultado) ? "1" : "0";
    }

    public function retiradas_pessoas() {
        return json_encode(
            DB::table("pessoas")
                ->where(function($sql) {
                    $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
                    if ($id_emp) {
                        $sql->where(function($query) use($id_emp) {
                            $query->where(function($query2) use($id_emp) {
                                $query2->whereIn("id_empresa", DB::table("empresas")->where("id", $id_emp)->pluck("id_matriz")->toArray());
                            })->orWhere("id_empresa", $id_emp);
                        })->orWhere(function($query) use($id_emp) {
                            $query->whereIn("id_empresa", DB::table("empresas")->where("id_matriz", $id_emp)->pluck("id")->toArray());
                        });
                    }
                })
                ->pluck("id")
        );
    }
}
