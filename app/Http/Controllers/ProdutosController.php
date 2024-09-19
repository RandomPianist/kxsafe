<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Produtos;
use App\Models\Atribuicoes;

class ProdutosController extends ControllerKX {
    private function busca($where) {
        return DB::table("produtos")
                    ->select(
                        DB::raw("produtos.*"),
                        DB::raw("
                            CASE
                                WHEN valores.descr IS NULL OR valores.descr = '' THEN 'A CLASSIFICAR'
                                ELSE valores.descr
                            END AS categoria
                        ")
                    )
                    ->leftjoin("valores", "valores.id", "produtos.id_categoria")
                    ->whereRaw($where)
                    ->where("produtos.lixeira", 0)
                    ->get();
    }

    public function ver() {
        $ultima_atualizacao = $this->log_consultar("produtos");
        return view("produtos", compact("ultima_atualizacao"));
    }

    public function listar(Request $request) {
        $filtro = trim($request->filtro);
        if ($filtro) {
            $busca = $this->busca("produtos.descr LIKE '".$filtro."%'");
            if (sizeof($busca) < 3) $busca = $this->busca("produtos.descr LIKE '%".$filtro."%'");
            if (sizeof($busca) < 3) $busca = $this->busca("(produtos.descr LIKE '%".implode("%' AND produtos.descr LIKE '%", explode(" ", str_replace("  ", " ", $filtro)))."%')");
        } else $busca = $this->busca("1");
        foreach($busca as $linha) $linha->foto = asset("storage/".$linha->foto);
        return json_encode($busca);
    }

    public function consultar(Request $request) {
        if (!sizeof(
            DB::table("valores")
                ->where("id", $request->id_categoria)
                ->where("descr", $request->categoria)
                ->get()
        )) return "invalido";
        if (sizeof(
            DB::table("produtos")
                ->where("lixeira", 0)
                ->where("cod_externo", $request->cod_externo)
                ->get()
        ) && !$request->id) return "duplicado";
        if (sizeof(
            DB::table("atribuicoes")
                ->where("produto_ou_referencia_valor", Produtos::find($request->id)->referencia)
                ->where("produto_ou_referencia_chave", "R")
                ->get()
        ) && !trim($request->referencia)) return "aviso";
        return "";
    }

    public function mostrar($id) {
        $produto = DB::table("produtos")
                        ->select(
                            DB::raw("produtos.*"),
                            DB::raw("IFNULL(valores.descr, 'A CLASSIFICAR') AS categoria"),
                            DB::raw("IFNULL(produtos.consumo, 0) AS e_consumo")
                        )
                        ->leftjoin("valores", "valores.id", "produtos.id_categoria")
                        ->where("produtos.id", $id)
                        ->first();
        if ($produto->foto == null) $produto->foto = "";
        else if (!stripos($produto->foto, "//")) $produto->foto = asset("storage/".$produto->foto);
        return json_encode($produto);
    }

    public function aviso($id) {
        $resultado = new \stdClass;
        $nome = Produtos::find($id)->descr;
        $resultado->aviso = "Tem certeza que deseja excluir ".$nome."?";
        $resultado->permitir = 1;
        return json_encode($resultado);
    }

    public function validade(Request $request) {
        return DB::table("produtos")
                ->selectRaw($request->tipo == "produto" ? "validade" : "MAX(validade) AS validade")
                ->whereRaw(
                    $request->tipo == "produto" ? "id = ".$request->id : "referencia IN (
                        SELECT referencia
                        FROM produtos
                        WHERE id = ".$request->id."
                    )"
                )
                ->value("validade");
    }

    public function salvar(Request $request) {
        $linha = Produtos::firstOrNew(["id" => $request->id]);
        $this->atribuicao_atualiza_ref($request->id, $linha->referencia, $request->referencia, "NULL");
        $linha->descr = mb_strtoupper($request->descr);
        $linha->preco = $request->preco;
        $linha->validade = $request->validade;
        $linha->ca = $request->ca;
        $linha->cod_externo = $request->cod_externo;
        $linha->id_categoria = $request->id_categoria;
        $linha->referencia = $request->referencia;
        $linha->tamanho = $request->tamanho;
        $linha->detalhes = $request->detalhes;
        $linha->consumo = $request->consumo;
        if ($request->file("foto")) $linha->foto = $request->file("foto")->store("uploads", "public");
        $linha->save();
        $this->log_inserir($request->id ? "E" : "C", "produtos", $linha->id);
        $this->mov_estoque($linha->id, false);
        return redirect("/produtos");
    }

    public function excluir(Request $request) {
        $linha = Produtos::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $this->log_inserir("D", "produtos", $linha->id);
        $lista = array();
        $consulta = DB::table("atribuicoes")
                        ->where(function($sql) use ($request) {
                            $sql->whereIn("produto_ou_referencia_valor", DB::table("produtos")->where("id", $request->id)->pluck("cod_externo")->toArray())
                                ->where("produto_ou_referencia_chave", "P");
                        })
                        ->orWhere(function($sql) use ($request) {
                            $sql->whereIn("produto_ou_referencia_valor", DB::table("produtos")->where("id", $request->id)->pluck("referencia")->toArray())
                                ->where("produto_ou_referencia_chave", "R");
                        })
                        ->pluck("id");
        foreach ($consulta as $atb) {
            $modelo = Atribuicoes::find($atb);
            $modelo->lixeira = 1;
            $modelo->save();
            $this->log_inserir("D", "atribuicoes", $atb);
        }
    }
}