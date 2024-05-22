<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ValoresController;
use App\Http\Controllers\SetoresController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\PessoasController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\AtribuicoesController;
use App\Http\Controllers\MaquinasController;
use App\Http\Controllers\RelatoriosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware("auth")->group(function () {
    Route::get("/",             [HomeController::class, "index"]);
    Route::get("/autocomplete", [HomeController::class, "autocomplete"]);

    Route::group(["prefix" => "valores/{alias}"], function() {
        Route::get ("/",             [ValoresController::class, "ver"]);
        Route::get ("/listar",       [ValoresController::class, "listar"]);
        Route::get ("/consultar",    [ValoresController::class, "consultar"]);
        Route::get ("/mostrar/{id}", [ValoresController::class, "mostrar"]);
        Route::get ("/aviso/{id}",   [ValoresController::class, "aviso"]);
        Route::post("/salvar",       [ValoresController::class, "salvar"]);
        Route::post("/excluir",      [ValoresController::class, "excluir"]);
    });

    Route::group(["prefix" => "setores"], function() {
        Route::get ("/",              [SetoresController::class, "ver"]);
        Route::get ("/listar",        [SetoresController::class, "listar"]);
        Route::get ("/consultar",     [SetoresController::class, "consultar"]);
        Route::get ("/usuarios/{id}", [SetoresController::class, "usuarios"]);
        Route::get ("/pessoas/{id}",  [SetoresController::class, "pessoas"]);
        Route::get ("/mostrar/{id}",  [SetoresController::class, "mostrar"]);
        Route::get ("/aviso/{id}",    [SetoresController::class, "aviso"]);
        Route::post("/salvar",        [SetoresController::class, "salvar"]);
        Route::post("/excluir",       [SetoresController::class, "excluir"]);
    });

    Route::group(["prefix" => "empresas"], function() {
        Route::get ("/",             [EmpresasController::class, "ver"]);
        Route::get ("/listar",       [EmpresasController::class, "listar"]);
        Route::get ("/consultar",    [EmpresasController::class, "consultar"]);
        Route::get ("/mostrar/{id}", [EmpresasController::class, "mostrar"]);
        Route::get ("/aviso/{id}",   [EmpresasController::class, "aviso"]);
        Route::post("/salvar",       [EmpresasController::class, "salvar"]);
        Route::post("/excluir",      [EmpresasController::class, "excluir"]);
    });

    Route::group(["prefix" => "colaboradores"], function() {
        Route::get ("/",             [PessoasController::class, "ver"]);
        Route::get ("/listar",       [PessoasController::class, "listar"]);
        Route::get ("/consultar",    [PessoasController::class, "consultar"]);
        Route::get ("/mostrar/{id}", [PessoasController::class, "mostrar"]);
        Route::get ("/aviso/{id}",   [PessoasController::class, "aviso"]);
        Route::post("/salvar",       [PessoasController::class, "salvar"]);
        Route::post("/excluir",      [PessoasController::class, "excluir"]);
    });

    Route::group(["prefix" => "produtos"], function() {
        Route::get ("/",               [ProdutosController::class, "ver"]);
        Route::get ("/listar",         [ProdutosController::class, "listar"]);
        Route::get ("/consultar",      [ProdutosController::class, "consultar"]);
        Route::get ("/mostrar/{id}",   [ProdutosController::class, "mostrar"]);
        Route::get ("/aviso/{id}",     [ProdutosController::class, "aviso"]);
        Route::post("/salvar",         [ProdutosController::class, "salvar"]);
        Route::post("/excluir",        [ProdutosController::class, "excluir"]);
    });

    Route::group(["prefix" => "atribuicoes"], function() {
        Route::get ("/ver-maximo", [AtribuicoesController::class, "verMaximo"]);
        Route::get ("/mostrar",    [AtribuicoesController::class, "mostrar"]);
        Route::post("/salvar",     [AtribuicoesController::class, "salvar"]);
        Route::post("/excluir",    [AtribuicoesController::class, "excluir"]);
    });

    Route::group(["prefix" => "maquinas"], function() {
        Route::group(["prefix" => "estoque"], function() {
            Route::post("/",          [MaquinasController::class, "estoque"]);
            Route::get ("/consultar", [MaquinasController::class, "consultar_estoque"]);
        });
        Route::group(["prefix" => "comodato"], function() {
            Route::get ("/consultar", [MaquinasController::class, "consultar_comodato"]);
            Route::post("/criar",     [MaquinasController::class, "criar_comodato"]);
            Route::post("/encerrar",  [MaquinasController::class, "encerrar_comodato"]);
        });
    });

    Route::group(["prefix" => "relatorios"], function() {
        Route::get("/comodatos", [RelatoriosController::class, "comodatos"]);
        Route::group(["prefix" => "extrato"], function() {
            Route::get("/",          [RelatoriosController::class, "extrato"]);
            Route::get("/consultar", [RelatoriosController::class, "extrato_consultar"]);
        });
        Route::group(["prefix" => "bilateral"], function() {
            Route::get("/",          [RelatoriosController::class, "bilateral"]);
            Route::get("/consultar", [RelatoriosController::class, "bilateral_consultar"]);
        });
        Route::group(["prefix" => "retiradas"], function() {
            Route::get("/",          [RelatoriosController::class, "retiradas"]);
            Route::get("/consultar", [RelatoriosController::class, "retiradas_consultar"]);
        });
    });
});

require __DIR__.'/auth.php';