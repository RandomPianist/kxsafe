<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Pessoas;

class DashboardController extends Controller {
    private function minhas_maquinas() {
        return DB::table("comodatos")
                    ->select(
                        "minhas_empresas.id_pessoa",
                        "comodatos.id_maquina"
                    )
                    ->joinsub(
                        DB::table("pessoas")
                            ->select(
                                "id AS id_pessoa",
                                "id_empresa"
                            )
                            ->unionAll(
                                DB::table("pessoas")
                                    ->select(
                                        "pessoas.id AS id_pessoa",
                                        "filiais.id AS id_empresa"
                                    )
                                    ->join("empresas AS filiais", "filiais.id_matriz", "pessoas.id_empresa")
                            ),
                        "minhas_empresas",
                        "minhas_empresas.id_empresa",
                        "comodatos.id_empresa"
                    )
                    ->whereRaw("comodatos.inicio <= CURDATE()")
                    ->whereRaw("comodatos.fim >= CURDATE()");
    }

    private function consulta($select, $where, $groupby) {
        return DB::table("pessoas")
                    ->select(DB::raw($select))
                    ->join("atribuicoes", function($join) {
                        $join->on(function($sql) {
                            $sql->on("atribuicoes.pessoa_ou_setor_valor", "pessoas.id")
                                ->where("atribuicoes.pessoa_ou_setor_chave", "P");
                        })->orOn(function($sql) {
                            $sql->on("atribuicoes.pessoa_ou_setor_valor", "pessoas.id_setor")
                                ->where("atribuicoes.pessoa_ou_setor_chave", "S");
                        });
                    })
                    ->join("produtos", function($join) {
                        $join->on(function($sql) {
                            $sql->on("atribuicoes.produto_ou_referencia_valor", "produtos.cod_externo")
                                ->where("atribuicoes.produto_ou_referencia_chave", "P");
                        })->orOn(function($sql) {
                            $sql->on("atribuicoes.produto_ou_referencia_valor", "produtos.referencia")
                                ->where("atribuicoes.produto_ou_referencia_chave", "R");
                        });
                    })
                    ->joinsub(
                        $this->minhas_maquinas(),
                        "minhas_maquinas",
                        "minhas_maquinas.id_pessoa",
                        "pessoas.id"
                    )
                    ->joinsub(
                        DB::table("maquinas_produtos AS mp")
                            ->select(
                                "mp.id_produto",
                                "mp.id_maquina",
                                DB::raw("
                                    IFNULL(SUM(
                                        CASE
                                            WHEN estoque.es = 'E' THEN estoque.qtd
                                            ELSE estoque.qtd * -1
                                        END
                                    ), 0) AS quantidade
                                ")
                            )
                            ->leftjoin("estoque", "estoque.id_mp", "mp.id")
                            ->groupby(
                                "id_produto",
                                "id_maquina"
                            ),
                        "estq",
                        function($join) {
                            $join->on("estq.id_maquina", "minhas_maquinas.id_maquina")
                                 ->on("estq.id_produto", "produtos.id");
                        }
                    )
                    ->leftjoinsub(
                        DB::table("retiradas")
                            ->select(
                                "id_pessoa",
                                "id_atribuicao",
                                DB::raw("MAX(data) AS data")
                            )
                            ->groupby(
                                "id_pessoa",
                                "id_atribuicao"
                            ),
                            "ret",
                            function($join) {
                                $join->on("ret.id_pessoa", "pessoas.id")
                                     ->on("ret.id_atribuicao", "atribuicoes.id");
                            }
                    )
                    ->whereRaw("(ret.id_pessoa IS NULL OR (DATE_ADD(ret.data, INTERVAL atribuicoes.validade DAY) >= CURDATE()))")
                    ->whereRaw($where)
                    ->where("atribuicoes.obrigatorio", 1)
                    ->where("produtos.lixeira", 0)
                    ->where("atribuicoes.lixeira", 0)
                    ->groupby(DB::raw($groupby))
                    ->havingRaw("SUM(estq.quantidade) > ?", [0])
                    ->get();
    }

    public function produtos($id_pessoa) {
        return json_encode($this->consulta("
            produtos.id,
            atribuicoes.validade,
            CASE
                WHEN atribuicoes.qtd < SUM(estq.quantidade) THEN atribuicoes.qtd
                ELSE SUM(estq.quantidade)
            END AS qtd,
            CASE
                WHEN atribuicoes.produto_ou_referencia_chave = 'P' THEN produtos.descr
                ELSE produtos.referencia
            END AS produto
        ", "pessoas.id = ".$id_pessoa, "
            produtos.id,
            atribuicoes.validade,
            atribuicoes.qtd,
            CASE
                WHEN atribuicoes.produto_ou_referencia_chave = 'P' THEN produtos.descr
                ELSE produtos.referencia
            END
        "));
    }

    public function pagina() {
        $where = "pessoas.lixeira = 0";
        $id_emp = Pessoas::find(Auth::user()->id_pessoa)->id_empresa;
        if (intval($id_emp)) {
            $where .= " AND pessoas.id_empresa IN (
                SELECT id
                FROM empresas
                WHERE empresas.id = ".$id_emp."
                UNION ALL (
                    SELECT filiais.id
                    FROM empresas AS filiais
                    WHERE filiais.id_matriz = ".$id_emp."
                )
            )";
        }
        $pessoas = $this->consulta("
            pessoas.id,
            pessoas.nome,
            pessoas.foto,
            COUNT(DISTINCT produtos.id) AS total
        ", $where, "
            pessoas.id,
            pessoas.nome,
            pessoas.foto 
        ");
        foreach ($pessoas as $pessoa) $pessoa->foto = asset("storage/".$pessoa->foto);
        $ultimas_retiradas = DB::table("pessoas")
                                ->select(
                                    "pessoas.foto",
                                    "pessoas.nome"
                                )
                                ->joinsub(
                                    DB::table("retiradas")
                                        ->select("id_pessoa")
                                        ->whereRaw("MONTH(data) = MONTH(CURDATE())")
                                        ->whereRaw("YEAR(data) = YEAR(CURDATE())")
                                        ->groupby("id_pessoa"),
                                    "ret",
                                    "ret.id_pessoa",
                                    "pessoas.id"
                                )
                                ->whereRaw($where)
                                ->get();
        foreach ($ultimas_retiradas as $retirada) $retirada->foto = asset("storage/".$retirada->foto);
        $retiradas_por_setor = DB::table("retiradas")
                                    ->select(
                                        "setores.id",
                                        "setores.descr",
                                        DB::raw("SUM(qtd) AS retirados")
                                    )
                                    ->join("pessoas", "pessoas.id", "retiradas.id_pessoa")
                                    ->join("setores", "setores.id", "pessoas.id_setor")
                                    ->whereRaw("MONTH(data) = MONTH(CURDATE())")
                                    ->whereRaw("YEAR(data) = YEAR(CURDATE())")
                                    ->where("setores.lixeira", 0)
                                    ->whereRaw($where)
                                    ->groupby(
                                        "setores.id",
                                        "setores.descr"
                                    )
                                    ->get();
        $total = 0;
        foreach ($retiradas_por_setor as $rps) $total += floatval($rps->retirados);
        $minhas_maquinas = DB::table("valores")
                                ->select(
                                    "id",
                                    "descr"
                                )
                                ->whereIn(
                                    "id",
                                    $this->minhas_maquinas()
                                         ->where("id_pessoa", Auth::user()->id_pessoa)
                                         ->pluck("id_maquina")
                                         ->toArray()
                                )
                                ->get();
        return view("dashboard", compact("pessoas", "ultimas_retiradas", "retiradas_por_setor", "total", "minhas_maquinas"));
    }
}