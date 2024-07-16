<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Models\Empresas;
use App\Models\Pessoas;

class EmpresasController extends ControllerKX {
    private function busca($param) {
        $id_emp = Pessoas::find(Auth::user()->id_pessoa)->id_empresa;
        return DB::table("empresas")
                ->select(
                    "id",
                    "nome_fantasia",
                    "id_matriz"
                )
                ->where(function($query) use($id_emp, $param) {
                    if (intval($id_emp) && $param == ">") {
                        $query->where("id", $id_emp)
                                ->orWhere("id_matriz", $id_emp);
                    }
                })
                ->where("lixeira", 0)
                ->where("id_matriz", $param, 0)
                ->orderby("nome_fantasia")
                ->get();
    }

    public function ver() {
        $ultima_atualizacao = $this->log_consultar("empresas");
        $pode_criar_matriz = !intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
        return view("empresas", compact("ultima_atualizacao", "pode_criar_matriz"));
    }

    public function listar() {
        $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
        $resultado = new \stdClass;
        $resultado->inicial = $this->busca("=");
        $resultado->final = $this->busca(">");
        $resultado->matriz_editavel = $id_emp ? sizeof(DB::table("empresas")->where("id_matriz", $id_emp)->where("lixeira", 0)->get()) > 0 ? 1 : 0 : 1;
        return json_encode($resultado);
    }

    public function consultar(Request $request) {
        if (sizeof(
            DB::table("empresas")
                ->where("lixeira", 0)
                ->where("cnpj", $request->cnpj)
                ->get()
        )) return "1";
        return "0";
    }

    public function mostrar($id) {
        return json_encode(Empresas::find($id));
    }

    public function aviso($id) {
        $resultado = new \stdClass;
        $nome = Empresas::find($id)->nome_fantasia;
        if (sizeof(
            DB::table("pessoas")
                ->where("id_empresa", $id)
                ->where("lixeira", 0)
                ->get()
        )) {
            $resultado->aviso = "Não é possível excluir ".$nome." porque existem pessoas vinculadas a essa empresa.";
            $resultado->permitir = 0;
        } else if (sizeof(
            DB::table("comodatos")
                ->whereRaw("CURDATE() >= inicio AND CURDATE() < fim")
                ->where("id_empresa", $id)
                ->get()
        )) {
            $resultado->aviso = "Não é possível excluir ".$nome." porque existem máquinas comodatadas para essa empresa.";
            $resultado->permitir = 0;
        } else {
            $resultado->aviso = "Tem certeza que deseja excluir ".$nome."?";
            $resultado->permitir = 1;
        }
        return json_encode($resultado);
    }

    public function salvar(Request $request) {
        $linha = Empresas::firstOrNew(["id" => $request->id]);
        $linha->nome_fantasia = mb_strtoupper($request->nome_fantasia);
        $linha->razao_social = mb_strtoupper($request->razao_social);
        $linha->cnpj = $request->cnpj;
        $linha->id_matriz = $request->id_matriz ? $request->id_matriz : 0;
        $linha->save();
        $this->log_inserir($request->id ? "E" : "C", "empresas", $linha->id);
        return redirect("/empresas");
    }

    public function excluir(Request $request) {
        $linha = Empresas::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $this->log_inserir("D", "empresas", $linha->id);
    }
}