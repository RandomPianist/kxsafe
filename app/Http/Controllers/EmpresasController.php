<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Models\EmpresasSetores;
use App\Models\Empresas;
use App\Models\Pessoas;

class EmpresasController extends Controller {
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
        $log = new LogController;
        $ultima_atualizacao = $log->consultar(["empresas", "empresas_setores"]);
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

    public function consultar_solo(Request $request) {
        return (!sizeof(
            DB::table("empresas")
                ->where("id", $request->id_empresa)
                ->where("nome_fantasia", $request->empresa)
                ->where("lixeira", 0)
                ->get()
        ));
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
        $log = new LogController;
        $log->inserir($request->id ? "E" : "C", "empresas", $linha->id);
        if (!$request->id) {
            $consulta = DB::table("setores")
                            ->select("id")
                            ->where("padrao", 1)
                            ->get();
            foreach ($consulta as $setor) {
                $modelo = new EmpresasSetores;
                $modelo->id_empresa = $linha->id;
                $modelo->id_setor = $setor->id;
                $modelo->save();
                $log = new LogController;
                $log->inserir("C", "empresas_setores", $modelo->id);
            }
        }
        return redirect("/empresas");
    }

    public function excluir(Request $request) {
        $linha = Empresas::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $log = new LogController;
        $log->inserir("D", "empresas", $linha->id);
    }

    public function listarSetores($id) {
        return json_encode(
            DB::table("empresas_setores")
                ->select(
                    "empresas_setores.id",
                    "setores.descr"
                )
                ->join("setores", "setores.id", "empresas_setores.id_setor")
                ->join("empresas", "empresas.id", "empresas_setores.id_empresa")
                ->where("setores.lixeira", 0)
                ->where("empresas.lixeira", 0)
                ->where("empresas_setores.lixeira", 0)
                ->where("empresas_setores.id_empresa", $id)
                ->get()
        );
    }

    public function salvarSetor(Request $request) {
        if (!sizeof(
            DB::table("setores")
                ->where("id", $request->id_setor)    
                ->where("descr", $request->setor)
                ->where("lixeira", 0)
                ->get()
        )) return 404;
        if (sizeof(
            DB::table("empresas_setores")
                ->where("id_empresa", $request->id_empresa)
                ->where("id_setor", $request->id_setor)
                ->where("lixeira", 0)
                ->get()
        )) return 403;
        $linha = new EmpresasSetores;
        $linha->id_empresa = $request->id_empresa;
        $linha->id_setor = $request->id_setor;
        $linha->save();
        $log = new LogController;
        $log->inserir("C", "empresas_setores", $linha->id);
        return 201;
    }

    public function excluirSetor(Request $request) {
        $linha = EmpresasSetores::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $log = new LogController;
        $log->inserir("D", "empresas_setores", $linha->id);
    }
}