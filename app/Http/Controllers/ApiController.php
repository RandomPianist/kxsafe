<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaquinasController;
use App\Models\Valores;
use App\Models\Produtos;
use App\Models\Estoque;
use App\Models\Retiradas;
use App\Models\Atribuicoes;

class ApiController extends Controller {
    public function empresas() {
        return json_encode(DB::select(DB::raw("
            SELECT
                empresas.id,
                CONCAT(
                    empresas.nome_fantasia,
                    IFNULL(CONCAT(' - ', matriz.nome_fantasia), '')
                ) AS descr

            FROM empresas

            LEFT JOIN empresas AS matriz
                ON matriz.id = empresas.id_matriz

            WHERE empresas.lixeira = 0
              AND matriz.lixeira = 0
        ")));
    }

    public function maquinas(Request $request) {
        $query = "
            SELECT
                tab.id,
                tab.descr
            
            FROM (
                SELECT
                    id,
                    descr
                FROM valores
                WHERE alias = 'maquinas'
                  AND lixeira = 0
            ) AS tab
        ";
        if (isset($request->idEmp)) {
            $query .= "
                JOIN (
                    SELECT id_maquina
                    FROM comodatos
                    WHERE id_empresa = ".$request->idEmp."
                      AND CURDATE() >= inicio
                      AND CURDATE() < fim
                ) AS aux ON aux.id_maquina = tab.id
            ";
        }
        return DB::select(DB::raw($query));
    }

    public function produtos_por_maquina(Request $request) {
        $consulta = DB::select(DB::raw("
            SELECT
                produtos.id,
                produtos.descr,
                IFNULL(ge.preco, 0) AS preco,
                IFNULL(tab.saldo, 0) AS saldo,
                IFNULL(ge.minimo, 0) AS minimo,
                IFNULL(ge.maximo, 0) AS maximo

            FROM gestor_estoque AS ge
            
            LEFT JOIN (
                SELECT
                    IFNULL(SUM(qtd), 0) AS saldo,
                    id_maquina,
                    id_produto
                    
                FROM (
                    SELECT
                        CASE
                            WHEN (es = 'E') THEN qtd
                            ELSE qtd * -1
                        END AS qtd,
                        id_maquina,
                        id_produto
            
                    FROM estoque
                ) AS estq
            
                GROUP BY
                    id_maquina,
                    id_produto
            ) AS tab ON tab.id_maquina = ge.id_maquina AND tab.id_produto = ge.id_produto

            JOIN produtos
                ON produtos.id = ge.id_produto

            WHERE ge.id_maquina = ".$request->idMaquina."
              AND produtos.lixeira = 0
        "));
        foreach ($consulta as $linha) {
            $linha->saldo = floatval($linha->saldo);
            $linha->minimo = floatval($linha->minimo);
            $linha->maximo = floatval($linha->maximo);
        }
        return json_encode($consulta);
    }

    public function categorias(Request $request) {
        $linha = Valores::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->alias = "categorias";
        if (!$request->id) {
            $linha->seq = intval(DB::select(DB::raw("
                SELECT IFNULL(MAX(seq), 0) AS ultimo
                FROM valores
                WHERE alias = 'categorias'
            "))[0]->ultimo) + 1;
        }
        $linha->save();
        $log = new LogController;
        $modelo = $log->inserir($request->id ? "E" : "C", "valores", $linha->id, true);
        if (isset($request->usu)) $modelo->nome = $request->usu;
        $modelo->save();
        $resultado = new \stdClass;
        $resultado->id = $linha->id;
        $resultado->descr = $linha->descr;
        return json_encode($resultado);
    }

    public function produtos(Request $request) {
        $linha = Produtos::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->preco = $request->preco;
        $linha->validade = $request->validade;
        $linha->ca = $request->ca;
        $linha->cod_externo = $request->codExterno;
        $linha->id_categoria = $request->idCategoria;
        $linha->foto = $request->foto;
        $linha->lixeira = $request->lixeira;
        if (isset($request->refer)) $linha->referencia = $request->refer;
        $linha->save();
        $log = new LogController;
        $letra_log = $request->id ? "E" : "C";
        if (intval($request->lixeira)) $letra_log = "D";
        $modelo = $log->inserir($letra_log, "produtos", $linha->id, true);
        if (isset($request->usu)) $modelo->nome = $request->usu;
        $modelo->save();
        $maquinas = new MaquinasController;
        $maquinas->mov_estoque($linha->id, true);
        $consulta = DB::select(DB::raw("
            SELECT
                id,
                descr,
                preco,
                validade,
                IFNULL(ca, '') AS ca,
                IFNULL(foto, '') AS foto,
                lixeira,
                id_categoria AS idCategoria,
                cod_externo AS codExterno

            FROM produtos

            WHERE id = ".$linha->id."
        "))[0];
        $consulta->preco = floatval($consulta->preco);
        $consulta->lixeira = intval($consulta->lixeira);
        return json_encode($consulta);
    }

    public function movimentar_estoque(Request $request) {
        for ($i = 0; $i < sizeof($request->idProduto); $i++) {
            $linha = new Estoque;
            $linha->es = $request->es[$i];
            $linha->descr = $request->descr[$i];
            $linha->qtd = $request->qtd[$i];
            $linha->id_produto = $request->idProduto[$i];
            $linha->id_maquina = $request->idMaquina;
            $linha->save();
            $log = new LogController;
            $modelo = $log->inserir("C", "estoque", $linha->id, true);
            if (isset($request->usu)) $modelo->nome = $request->usu;
            $modelo->save();
        }
        return 200;
    }

    public function gerenciar_estoque(Request $request) {
        if (isset($request->preco)) $precoProd = floatval($request->preco) > 0 ? floatval($request->preco) : floatval(DB::select("produtos")->where("id", $request->idProduto)->value("preco"));
        else $precoProd = floatval(DB::select("produtos")->where("id", $request->idProduto)->value("preco"));
        $log = new LogController;
        DB::statement("
            UPDATE gestor_estoque SET
                minimo = ".$request->minimo.",
                maximo = ".$request->maximo.",
                preco = ".$precoProd."
            WHERE id_produto = ".$request->idProduto."
              AND id_maquina = ".$request->idMaquina
        );
        $consulta = DB::table("gestor_estoque")
                    ->select("id")
                    ->where("id_produto", $request->idProduto)
                    ->where("id_maquina", $request->idMaquina)
                    ->get();
        foreach ($consulta as $linha) {
            $modelo = $log->inserir("E", "gestor_estoque", $linha->id, true);
            if (isset($request->usu)) $modelo->nome = $request->usu;
            $modelo->save();
        }
    }

    public function validarApp(Request $request) {
        return sizeof(
            DB::table("pessoas")
                ->where("cpf", $request->cpf)
                ->where("senha", $request->senha)
                ->get()
        ) ? 1 : 0;
    }

    public function verPessoa(Request $request) {
        return json_encode(
            DB::table("pessoas")
                ->where("cpf", $request->cpf)
                ->first()
        );
    }

    public function produtosPorPessoa(Request $request) {
        $consulta = DB::select(DB::raw("
            SELECT * FROM (
                SELECT
                    atribuicoes.id AS id_atribuicao,
                    produtos.id,
                    produto_ou_referencia_valor AS descr,
                    qtd,
                    IFNULL(produtos.foto, '') AS foto
            
                FROM atribuicoes
            
                JOIN produtos
                    ON produtos.descr = atribuicoes.produto_ou_referencia_valor
            
                JOIN pessoas
                    ON pessoas.id = atribuicoes.pessoa_ou_setor_valor
                    
                WHERE pessoa_ou_setor_chave = 'pessoa'
                  AND cpf = '".$request->cpf."'
                  AND produto_ou_referencia_chave = 'produto'
                
                UNION ALL (
                    SELECT
                        atribuicoes.id AS id_atribuicao,
                        produtos.id,
                        produtos.descr,
                        qtd,
                        IFNULL(produtos.foto, '') AS foto
                    
                    FROM atribuicoes
                    
                    JOIN produtos
                        ON produtos.referencia = atribuicoes.produto_ou_referencia_valor
                        
                    JOIN pessoas
                        ON pessoas.id = atribuicoes.pessoa_ou_setor_valor
                        
                    WHERE pessoa_ou_setor_chave = 'pessoa'
                      AND cpf = '".$request->cpf."'
                      AND produto_ou_referencia_chave = 'referencia'

                    UNION ALL (
                        SELECT
                            atribuicoes.id AS id_atribuicao,
                            produtos.id,
                            produtos.descr,
                            qtd,
                            IFNULL(produtos.foto, '') AS foto
                        
                        FROM atribuicoes
                        
                        JOIN produtos
                            ON produtos.descr = atribuicoes.produto_ou_referencia_valor
                            
                        JOIN pessoas
                            ON pessoas.id_setor = atribuicoes.pessoa_ou_setor_valor
                            
                        WHERE pessoa_ou_setor_chave = 'setor'
                          AND cpf = '".$request->cpf."'
                          AND produto_ou_referencia_chave = 'produto'

                        UNION ALL (
                            SELECT
                                atribuicoes.id AS id_atribuicao,
                                produtos.id,
                                produtos.descr,
                                qtd,
                                IFNULL(produtos.foto, '') AS foto
                            
                            FROM atribuicoes
                            
                            JOIN produtos
                                ON produtos.referencia = atribuicoes.produto_ou_referencia_valor
                                
                            JOIN pessoas
                                ON pessoas.id_setor = atribuicoes.pessoa_ou_setor_valor
                                
                            WHERE pessoa_ou_setor_chave = 'setor'
                              AND cpf = '".$request->cpf."'
                              AND produto_ou_referencia_chave = 'referencia'
                        )
                    )
                )
            ) AS tab
            
            GROUP BY
                id,
                descr,
                qtd,
                foto
                
            ORDER BY descr
        "));
        $resultado = array();
        foreach ($consulta as $linha) {
            if ($linha->foto) {
                $foto = explode("/", $linha->foto);
                $linha->foto = $foto[sizeof($foto) - 1];
            }
            array_push($resultado, $linha);
        }
        return json_encode($resultado);
    }

    public function retirar(Request $request) {
        $resultado = new \stdClass;
        $atribuicao = Atribuicoes::find($request->id_atribuicao);
        if ($atribuicao == null) {
            $resultado->code = 404;
            $resultado->msg = "Atribuição não encontrada";
            return json_encode($resultado);
        }
        $maquina = Valores::find($request->id_maquina);
        if ($maquina == null) {
            $resultado->code = 404;
            $resultado->msg = "Máquina não encontrada";
            return json_encode($resultado);
        }
        if ($maquina->alias != "maquinas") {
            $resultado->code = 404;
            $resultado->msg = "Máquina não encontrada";
            return json_encode($resultado);
        }
        $comodato = DB::table("comodatos")
                        ->select("id")
                        ->where("id_maquina", $maquina->id)
                        ->whereRaw("inicio >= CURDATE()")
                        ->whereRaw("fim <= CURDATE()")
                        ->get();
        if (!sizeof($comodato)) {
            $resultado->code = 404;
            $resultado->msg = "Máquina não comodatada para nenhuma empresa";
            return json_encode($resultado);
        }
        if (floatval($atribuicao->qtd) < floatval($request->qtd)) {
            $resultado->code = 401;
            $resultado->msg = "Essa quantidade de produtos não é permitida para essa pessoa";
            return json_encode($resultado);
        }
        $id_comodato = "";
        $retirada = new Retiradas;
        $retirada->id_atribuicao = $request->id_atribuicao;
        $retirada->id_comodato = $comodato[0]->id;
        $retirada->qtd = $request->qtd;
        $retirada->save();
        $resutado->code = 201;
        $resultado->msg = "Sucesso";
        return json_encode($resultado);
    }
}