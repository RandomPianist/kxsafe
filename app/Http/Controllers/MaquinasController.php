<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Http\Controllers\EmpresasController;
use App\Models\Comodatos;
use App\Models\Estoque;
use App\Models\MaquinasProdutos;

class MaquinasController extends Controller {
    public function estoque(Request $request) {
        for ($i = 0; $i < sizeof($request->id_produto); $i++) {
            $linha = new Estoque;
            $linha->es = $request->es[$i];
            $linha->descr = $request->obs[$i];
            $linha->qtd = $request->qtd[$i];
            $linha->id_mp = DB::table("maquinas_produtos")
                                ->where("id_produto", $request->id_produto[$i])
                                ->where("id_maquina", $request->id_maquina)
                                ->value("id");
            $linha->save();
            $log = new LogController;
            $log->inserir("C", "estoque", $linha->id);
        }
        return redirect("/valores/maquinas");
    }

    public function consultar_estoque(Request $request) {
        $texto = "";
        $campos = array();
        $valores = array();

        $produtos_id = explode(",", $request->produtos_id);
        $produtos_descr = explode(",", $request->produtos_descr);
        $quantidades = explode(",", $request->quantidades);
        $es = explode(",", $request->es);

        for ($i = 0; $i < sizeof($produtos_id); $i++) {
            if (!sizeof(
                DB::table("produtos")
                    ->where("id", $produtos_id[$i])
                    ->where("descr", $produtos_descr[$i])
                    ->where("lixeira", 0)
                    ->get()
            )) {
                array_push($campos, "produto-".($i + 1));
                array_push($valores, $produtos_descr[$i]);
                $texto = !$texto ? "Produtos não encontrados" : "Produto não encontrado";
            }
        }

        if (!$texto) {
            for ($i = 0; $i < sizeof($produtos_id); $i++) {
                $consulta = DB::select(DB::raw("
                    SELECT IFNULL(SUM(qtd), 0) AS saldo
                        
                    FROM (
                        SELECT
                            CASE
                                WHEN (es = 'E') THEN qtd
                                ELSE qtd * -1
                            END AS qtd,
                            id_mp
                
                        FROM estoque
                    ) AS estq

                    JOIN maquinas_produtos AS mp
                        ON mp.id = estq.id_mp

                    WHERE mp.id_maquina = ".$request->id_maquina."
                      AND mp.id_produto = ".$produtos_id[$i]
                ));
                $erro = !sizeof($consulta);
                if (!$erro) {
                    $valor = floatval($quantidades[$i]);
                    if ($es[$i] == "S") $valor *= -1;
                    $erro = (floatval($consulta[0]->saldo) + $valor) < 0;
                }
                if ($erro) {
                    array_push($campos, "qtd-".($i + 1));
                    array_push($valores, floatval($consulta[0]->saldo) * 1);
                    $linha2 = !$texto ? "Os campos foram corrigidos" : "O campo foi corrigido";
                    $linha2 .= " para zerar o estoque.<br>Por favor, verifique e tente novamente.";
                    $texto = "Essa movimentação de estoque provocaria estoque negativo.<br>".$linha2;
                }
            }
        }

        $resultado = new \stdClass;
        $resultado->texto = $texto;
        $resultado->campos = $campos;
        $resultado->valores = $valores;
        return json_encode($resultado);
    }

    public function consultar_comodato(Request $request) {
        $emp_controller = new EmpresasController;
        $resultado = new \stdClass;
        $resultado->texto = "";
        if ($emp_controller->consultar_solo($request)) $resultado->texto = "Empresa não encontrada";
        if (!$resultado->texto) {
            $inicio = Carbon::createFromFormat('d/m/Y', $request->inicio)->format('Y-m-d');
            $fim = Carbon::createFromFormat('d/m/Y', $request->fim)->format('Y-m-d');
            $consulta = DB::select(DB::raw("
                SELECT
                    CONCAT(
                        valores.descr, ' ',
                        CASE
                            WHEN (CURDATE() > fim) THEN 'esteve'
                            WHEN (CURDATE() >= inicio) THEN 'está'
                            ELSE 'estará'
                        END,
                        ' comodatada entre ',
                        DATE_FORMAT(inicio, '%d/%m/%Y'), ' e ', DATE_FORMAT(fim, '%d/%m/%Y')
                    ) AS texto,
                    CASE
                        WHEN inicio >= '".$inicio."' THEN 'S'
                        ELSE 'N'
                    END AS invalida_inicio,
                    CASE
                        WHEN fim < '".$fim."' THEN 'S'
                        ELSE 'N'
                    END AS invalida_fim

                FROM comodatos

                JOIN valores
                    ON valores.id = comodatos.id_maquina

                WHERE (('".$inicio."' BETWEEN comodatos.inicio AND comodatos.fim) OR ('".$fim."' BETWEEN comodatos.inicio AND comodatos.fim))
                  AND comodatos.inicio <> comodatos.fim
                  AND id_maquina = ".$request->id_maquina
            ));
            if (sizeof($consulta)) $resultado = $consulta[0];
        }
        return json_encode($resultado);
    }

    public function criar_comodato(Request $request) {
        $inicio = Carbon::createFromFormat('d/m/Y', $request->inicio)->format('Y-m-d');
        $fim = Carbon::createFromFormat('d/m/Y', $request->fim)->format('Y-m-d');
        
        $linha = new Comodatos;
        $linha->id_maquina = $request->id_maquina;
        $linha->id_empresa = $request->id_empresa;
        $linha->inicio = $inicio;
        $linha->fim = $fim;
        $linha->fim_orig = $fim;
        $linha->save();
        $log = new LogController;
        $log->inserir("C", "comodatos", $linha->id);
        return redirect("/valores/maquinas");
    }

    public function encerrar_comodato(Request $request) {
        $modelo = Comodatos::find(
            DB::table("comodatos")
                ->whereRaw("CURDATE() >= inicio AND CURDATE() < fim")
                ->where("id_maquina", $request->id_maquina)
                ->value("id")
        );
        $modelo->fim = date('Y-m-d');
        $modelo->save();
        $log = new LogController;
        $log->inserir("E", "comodatos", $modelo->id);
        return redirect("/valores/maquinas");
    }

    public function mov_estoque($id_produto, $api) {
        $log = new LogController;
        $maquinas = DB::table("valores")
                        ->select("id")
                        ->where("alias", "maquinas")
                        ->get();
        foreach ($maquinas as $maquina) {
            if (!sizeof(
                DB::table("maquinas_produtos")
                    ->where("id_produto", $id_produto)
                    ->where("id_maquina", $maquina->id)
                    ->get()
            )) {
                $gestor = new MaquinasProdutos;
                $gestor->id_maquina = $maquina->id;
                $gestor->id_produto = $id_produto;
                $gestor->save();
                $log->inserir("C", "maquinas_produtos", $gestor->id, $api);
            }
        }
    }
}