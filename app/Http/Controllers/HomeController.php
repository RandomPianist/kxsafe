<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Pessoas;
use Illuminate\Http\Request;

class HomeController extends Controller {
    public function index() {
        if (intval(Pessoas::find(Auth::user()->id_pessoa)->id_empresa)) return redirect("/colaboradores");
        return redirect("/valores/categorias");
    }

    public function autocomplete(Request $request) {
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
                    if ($request->table == "empresas") {
                        $id_emp = intval(Pessoas::find(Auth::user()->id_pessoa)->id_emp);
                        if ($id_emp) {
                            if (sizeof(
                                DB::table("empresas")
                                    ->where("id_matriz", $id_emp)
                                    ->where("lixeira", 0)
                                    ->get()
                            ) > 0) {
                                $sql->where("id", $id_emp)
                                    ->orWhere("id_matriz", $id_emp);
                            } else $sql->where("id", $id_emp);
                        }
                    }
                })
                ->where("lixeira", 0)
                ->take(30)
                ->get()
        );
    }
}