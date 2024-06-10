<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Pessoas;
use Illuminate\Http\Request;

class HomeController extends Controller {
    public function index() {
        if (intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa)) return redirect("/colaboradores/pagina/F");
        return redirect("/valores/categorias");
    }

    public function autocomplete(Request $request) {
        if ($request->column <> "referencia") {
            return json_encode(
                DB::table($request->table)
                    ->select(
                        "id",
                        $request->column
                    )
                    ->where($request->column, "LIKE", $request->search."%")
                    ->where(function($sql) use($request) {
                        if ($request->filter) $sql->where($request->filter_col, $request->filter);
                    })
                    ->where(function($sql) use($request) {
                        $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa);
                        if ($id_emp) {
                            switch($request->table) {
                                case "empresas":
                                    if (sizeof(
                                        DB::table("empresas")
                                            ->where("id_matriz", $id_emp)
                                            ->where("lixeira", 0)
                                            ->get()
                                    ) > 0) {
                                        $sql->where("id", $id_emp)
                                            ->orWhere("id_matriz", $id_emp);
                                    } else $sql->where("id", $id_emp);
                                    break;
                                case "setores":
                                    $sql->where("cria_usuario", 0);
                                    break;
                            }
                        }
                    })
                    ->where("lixeira", 0)
                    ->orderby($request->column)
                    ->take(30)
                    ->get()
            );
        }
        $where = " AND ".$request->column." LIKE '".$request->search."%'";
        if ($request->filter) $where .= " AND referencia NOT IN (
            SELECT produto_ou_referencia_valor
            FROM atribuicoes
            WHERE pessoa_ou_setor_valor = ".$request->filter."
              AND lixeira = 0
        )";
        return json_encode(DB::select(DB::raw("
            SELECT
                MIN(id) AS id,
                referencia
            
            FROM produtos

            WHERE lixeira = 0".$where."

            GROUP BY referencia

            ORDER BY referencia
        ")));
    }
}