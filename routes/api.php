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

Route::get ("produtos",  [ProdutosController::class, "listar"]);
Route::post("categoria", [ApiController::class, "categoria"]);
Route::post("produtos",  [ApiController::class, "salvar_produtos"]);
Route::post("estoque",   [ApiController::class, "estoque"]);