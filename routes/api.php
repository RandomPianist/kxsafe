<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ProdutosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(["prefix" => "erp"], function() {
    Route::get ("/empresas",             [ApiController::class, "empresas"]);
    Route::get ("/maquinas",             [ApiController::class, "maquinas"]);
    Route::get ("/produtos-por-maquina", [ApiController::class, "produtos_por_maquina"]);
    Route::get ("/produtos",             [ProdutosController::class, "listar"]);
    Route::post("/categorias",           [ApiController::class, "categorias"]);
    Route::post("/produtos",             [ApiController::class, "produtos"]);
    Route::post("/movimentar-estoque",   [ApiController::class, "movimentar_estoque"]);
    Route::post("/gerenciar-estoque",    [ApiController::class, "gerenciar_estoque"]);
});

Route::group(["prefix" => "app"], function() {
    Route::post("/ver-pessoa",          [ApiController::class, "verPessoa"]);
    Route::post("/produtos-por-pessoa", [ApiController::class, "produtosPorPessoa"]);
    Route::post("/validar",             [ApiController::class, "validarApp"]);
    Route::post("/retirar",             [ApiController::class, "retirar"]);
});