<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaquinasController;
use App\Models\Produtos;

class ProdutosController extends Controller {
    private function busca($param) {
        return DB::select(DB::raw("
            SELECT
                produtos.*,
                CASE
                    WHEN valores.descr IS NULL OR valores.descr = '' THEN 'A CLASSIFICAR'
                    ELSE valores.descr
                END AS categoria

            FROM produtos

            LEFT JOIN valores
                ON valores.id = produtos.id_categoria

            WHERE ".$param."
              AND produtos.lixeira = 0
        "));
    }

    public function ver() {
        $log = new LogController;
        $ultima_atualizacao = $log->consultar("produtos");
        return view("produtos", compact("ultima_atualizacao"));
    }

    public function listar(Request $request) {
        $filtro = trim($request->filtro);
        if (strlen($filtro)) {
            $busca = $this->busca("descr LIKE '".$filtro."%'");
            if (sizeof($busca) < 3) $busca = $this->busca("descr LIKE '%".$filtro."%'");
            if (sizeof($busca) < 3) $busca = $this->busca("(descr LIKE '%".implode("%' AND descr LIKE '%", explode(" ", str_replace("  ", " ", $filtro)))."%')");
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
        $consulta = DB::select(DB::raw("
            SELECT
                produtos.*,
                IFNULL(valores.descr, 'A CLASSIFICAR') AS categoria
            
            FROM produtos

            LEFT JOIN valores
                ON valores.id = produtos.id_categoria

            WHERE produtos.id = ".$id
        ));
        foreach($consulta as $linha) {
            if ($linha->foto == null) $linha->foto = "";
            else if (!stripos($linha->foto, "//")) $linha->foto = asset("storage/".$linha->foto);
        }
        return json_encode($consulta[0]);
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
    }
}