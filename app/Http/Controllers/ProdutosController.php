<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaquinasController;
use App\Models\Produtos;
use App\Models\Atribuicoes;

class ProdutosController extends Controller {
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
        $log = new LogController;
        $ultima_atualizacao = $log->consultar(["produtos"]);
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
        $resultado = new \stdClass;
        if (!sizeof(
            DB::table("valores")
                ->where("id", $request->id_categoria)
                ->where("descr", $request->categoria)
                ->get()
        )) {
            $resultado->tipo = "invalido";
            $resultado->dado = "Categoria";
        } else if (sizeof(
            DB::table("produtos")
                ->where("lixeira", 0)
                ->where("cod_externo", $request->cod_externo)
                ->get()
        )) {
            $resultado->tipo = "duplicado";
            $resultado->dado = "cod";
        }
        return json_encode($resultado);
    }

    public function mostrar($id) {
        $produto = DB::table("produtos")
                        ->select(
                            DB::raw("produtos.*"),
                            DB::raw("IFNULL(valores.descr, 'A CLASSIFICAR') AS categoria")
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

    public function salvar(Request $request) {
        $log = new LogController;
        $linha = Produtos::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->preco = $request->preco;
        $linha->validade = $request->validade;
        $linha->ca = $request->ca;
        $linha->cod_externo = $request->cod_externo;
        $linha->id_categoria = $request->id_categoria;
        $linha->referencia = $request->referencia;
        $linha->tamanho = $request->tamanho;
        $linha->detalhes = $request->detalhes;
        if ($request->file("foto")) $linha->foto = $request->file("foto")->store("uploads", "public");
        $linha->save();
        $log->inserir($request->id ? "E" : "C", "produtos", $linha->id);
        $maquinas = new MaquinasController;
        $maquinas->mov_estoque($linha->id, false);
        return redirect("/produtos");
    }

    public function excluir(Request $request) {
        $linha = Produtos::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $log = new LogController;
        $log->inserir("D", "produtos", $linha->id);
        $lista = array();
        $consulta = DB::table("atribuicoes")
                        ->select("id")
                        ->where(function($sql) {
                            $sql->whereIn("produto_ou_referencia_valor", DB::table("produtos")->where("id", $request->id)->pluck("cod_externo")->toArray())
                                ->where("produto_ou_referencia_chave", "produto");
                        })
                        ->orWhere(function($sql) {
                            $sql->whereIn("produto_ou_referencia_valor", DB::table("produtos")->where("id", $request->id)->pluck("referencia")->toArray())
                                ->where("produto_ou_referencia_chave", "referencia");
                        })
                        ->get();
        foreach ($consulta as $linha) {
            $modelo = Atribuicoes::find($linha->id);
            $modelo->lixeira = 1;
            $modelo->save();
            $log = new LogController;
            $log->inserir("D", "atribuicoes", $linha->id);
        }
    }
}