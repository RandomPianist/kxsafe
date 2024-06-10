<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Hash;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SetoresController;
use App\Http\Controllers\EmpresasController;
use App\Models\Pessoas;

class PessoasController extends Controller {
    private function busca($param, $tipo) {
        if (in_array($tipo, ["A", "U"])) {
            $param .= " AND setores.cria_usuario = 1";
            if ($tipo == "A") $param .= " AND pessoas.id_empresa = 0";
        } else $param .= " AND setores.cria_usuario = 0 AND pessoas.supervisor = ".($tipo == "S" ? "1" : "0");
        $query = "
            SELECT
                pessoas.id,
                CONCAT(
                    pessoas.nome,
                    CASE
                        WHEN pessoas.id = ".Auth::user()->id_pessoa." THEN ' (você)'
                        ELSE ''
                    END
                ) AS nome,
                IFNULL(setores.descr,          'A CLASSIFICAR') AS setor,
                IFNULL(empresas.nome_fantasia, 'A CLASSIFICAR') AS empresa,
                CASE
                    WHEN ret.id_pessoa IS NULL THEN 0
                    ELSE 1
                END AS possui_retiradas
            
            FROM pessoas

            LEFT JOIN setores
                ON setores.id = pessoas.id_setor

            LEFT JOIN empresas
                ON empresas.id = pessoas.id_empresa

            LEFT JOIN (
                SELECT id_pessoa
                FROM retiradas
                GROUP BY id_pessoa
            ) AS ret ON ret.id_pessoa = pessoas.id

            WHERE ".$param."
              AND pessoas.lixeira = 0
        ";
        $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
        if ($id_emp) $query .= " AND ".$id_emp." IN (empresas.id, empresas.id_matriz)";
        return DB::select(DB::raw($query));
    }

    private function criar_usuario($id_pessoa, Request $request) {
        $log = new LogController;
        $senha = Hash::make($request->password);
        DB::statement("INSERT INTO users (name, email, password, id_pessoa) VALUES ('".trim($request->nome)."', '".trim($request->email)."', '".$senha."', ".$id_pessoa.")");
        $log->inserir("C", "users", DB::select(DB::raw("
            SELECT MAX(id) AS id
            FROM users
        "))[0]->id);
    }

    private function deletar_usuario($id_pessoa) {
        $log = new LogController;
        $fk = DB::table("users")
                ->where("id_pessoa", $id_pessoa)
                ->value("id");
        DB::statement("DELETE FROM users WHERE id_pessoa = ".$id_pessoa);
        $log->inserir("D", "users", $fk);
    }

    private function salvar_main($modelo, Request $request) {
        $log = new LogController;
        $modelo->nome = mb_strtoupper($request->nome);
        $modelo->cpf = $request->cpf;
        $modelo->funcao = mb_strtoupper($request->funcao);
        if ($request->admissao) $modelo->admissao = Carbon::createFromFormat('d/m/Y', $request->admissao)->format('Y-m-d');
        $modelo->id_empresa = $request->id_empresa;
        $modelo->id_setor = $request->id_setor;
        if (trim($request->senha)) $modelo->senha = $request->senha;
        $modelo->supervisor = $request->supervisor;
        if ($request->file("foto")) $modelo->foto = $request->file("foto")->store("uploads", "public");
        $modelo->save();
        $log->inserir($request->id ? "E" : "C", "pessoas", $modelo->id);
        return $modelo;
    }

    private function cria_usuario($id) {
        $setor = new SetoresController;
        return intval($setor->mostrar($id)->cria_usuario);
    }

    public function ver($tipo) {
        switch($tipo) {
            case "A":
                $titulo = "Administradores";
                break;
            case "F":
                $titulo = "Funcionários";
                break;
            case "S":
                $titulo = "Supervisores";
                break;
            case "U":
                $titulo = "Usuários";
                break;
        }
        $log = new LogController;
        $where = "";
        if (in_array($tipo, ["A", "U"])) {
            $where = " AND setores.cria_usuario = 1";
            if ($tipo == "A") $where .= " AND aux.id_empresa = 0";
        } else $where = " AND setores.cria_usuario = 0 AND aux.supervisor = ".($tipo == "S" ? "1" : "0");
        $ultima_atualizacao = $log->consultar(["pessoas"], "", $where);
        return view("pessoas", compact("ultima_atualizacao", "titulo", "tipo"));
    }

    public function listar(Request $request) {
        $filtro = trim($request->filtro);
        if (strlen($filtro)) {
            $busca = $this->busca("nome LIKE '".$filtro."%'", $request->tipo);
            if (sizeof($busca) < 3) $busca = $this->busca("nome LIKE '%".$filtro."%'", $request->tipo);
            if (sizeof($busca) < 3) $busca = $this->busca("(nome LIKE '%".implode("%' AND nome LIKE '%", explode(" ", str_replace("  ", " ", $filtro)))."%')", $request->tipo);
        } else $busca = $this->busca("1", $request->tipo);
        return json_encode($busca);
    }

    public function consultar(Request $request) {
        $emp_controller = new EmpresasController;
        $resultado = new \stdClass;
        if (!sizeof(
            DB::table("setores")
                ->where("id", $request->id_setor)
                ->where("descr", $request->setor)
                ->get()
        )) {
            $resultado->tipo = "invalido";
            $resultado->dado = "Setor";
        } else if ($emp_controller->consultar_solo($request)) {
            $resultado->tipo = "invalido";
            $resultado->dado = "Empresa";
        } else if (sizeof(
            DB::table("pessoas")
                ->where("lixeira", 0)
                ->where("cpf", $request->cpf)
                ->get()
        ) && trim($request->cpf)) {
            $resultado->tipo = "duplicado";
            $resultado->dado = "CPF";
        } else if (sizeof(
            DB::table("pessoas")
                ->join("users", "users.id_pessoa", "pessoas.id")
                ->where("lixeira", 0)
                ->where("email", $request->email)
                ->get()
        )) {
            $resultado->tipo = "duplicado";
            $resultado->dado = "e-mail";
        } else {
            $resultado->tipo = "ok";
            $resultado->dado = "";
        }
        return json_encode($resultado);
    }

    public function mostrar($id) {
        return json_encode(DB::select(DB::raw("
            SELECT
                pessoas.id,
                pessoas.cpf,
                pessoas.id_setor,
                pessoas.id_empresa,
                pessoas.funcao,
                pessoas.supervisor,
                pessoas.foto,
                DATE_FORMAT(pessoas.admissao, '%d/%m/%Y') AS admissao,
                setores.descr AS setor,
                empresas.nome_fantasia AS empresa,
                IFNULL(users.name, pessoas.nome) AS nome,
                users.email
            
            FROM pessoas

            LEFT JOIN empresas
                ON empresas.id = pessoas.id_empresa

            LEFT JOIN setores
                ON setores.id = pessoas.id_setor

            LEFT JOIN users
                ON users.id_pessoa = pessoas.id
            
            WHERE pessoas.id = ".$id
        ))[0]);
    }

    public function aviso($id) {
        $resultado = new \stdClass;
        if ($id != Auth::user()->id_pessoa) {
            $nome = Pessoas::find($id)->nome;
            $resultado->permitir = 1;
            $resultado->aviso = "Tem certeza que deseja excluir ".$nome."?";
        } else {
            $resultado->permitir = 0;
            $resultado->aviso = "Não é possível excluir a si mesmo";
        }
        return json_encode($resultado);
    }

    public function salvar(Request $request) {
        $log = new LogController;
        if ($request->id) {
            $setor_ant = Pessoas::find($request->id)->id_setor;
            if ($setor_ant != $request->id_setor) {
                if ($this->cria_usuario($setor_ant)) $this->deletar_usuario($request->id);    
                else if ($this->cria_usuario($request->id_setor)) $this->criar_usuario($request->id, $request);
            } else if (
                $this->cria_usuario($request->id_setor) && (
                    $request->password ||
                    mb_strtoupper(trim(Auth::user()->email)) != mb_strtoupper(trim($request->email)) ||
                    mb_strtoupper(trim(Auth::user()->name))  != mb_strtoupper(trim($request->nome))
                )
            ) {
                $senha = Hash::make($request->password);
                $atualiza_senha = $request->password ? "password = '".$senha."'," : "";
                DB::statement("
                    UPDATE users SET
                        ".$atualiza_senha."
                        name = '".trim($request->nome)."',
                        email = '".trim($request->email)."'
                    WHERE id_pessoa = ".$request->id
                );
                $log->inserir("E", "users", DB::table("users")
                                                ->where("id_pessoa", $request->id)
                                                ->value("id")             
                );
            }
            $modelo = Pessoas::find($request->id);
            $this->salvar_main($modelo, $request);
        } else {
            $modelo = new Pessoas;
            $linha = $this->salvar_main($modelo, $request);
            if ($this->cria_usuario($linha->id_setor)) $this->criar_usuario($linha->id, $request);
        }
        $tipo = $request->tipo;
        if (!$tipo) {
            $tipo = "U";
            if (intval(Pessoas::find($modelo->id)->supervisor)) $tipo = "S";
            if (!intval(Pessoas::find($modelo->id)->id_empresa)) $tipo = "A";
        }
        return redirect("/colaboradores/pagina/".$tipo);
    }

    public function excluir(Request $request) {
        $linha = Pessoas::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $log = new LogController;
        $log->inserir("D", "pessoas", $linha->id);
        if ($this->cria_usuario($linha->id_setor)) $this->deletar_usuario($linha->id);
    }
}