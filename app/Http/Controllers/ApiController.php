<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\Produtos;
use App\Models\Estoque;

class ApiController extends Controller {
    public function salvar_produtos(Request $request) {
        $linha = Produtos::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->preco = $request->preco;
        $linha->validade = $request->validade;
        $linha->ca = $request->ca;
        $linha->cod_externo = $request->cod_externo;
        $linha->id_categoria = $request->id_categoria;
        $linha->foto = $request->foto;
        $linha->save();
        $log = new LogController;
        $log->inserir($request->id ? "E" : "C", "produtos", $linha->id, true);
        return $linha->id;
    }

    public function estoque(Request $request) {
        for ($i = 0; $i < sizeof($request->id_produto); $i++) {
            $linha = new Estoque;
            $linha->es = $request->es[$i];
            $linha->descr = $request->obs[$i];
            $linha->qtd = $request->qtd[$i];
            $linha->id_produto = $request->id_produto[$i];
            $linha->id_maquina = $request->id_maquina;
            $linha->save();
            $log = new LogController;
            $log->inserir("C", "estoque", $linha->id, true);
        }
        return 200;
    }
}