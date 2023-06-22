<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group([
    'namespace' => '\App\Http\Controllers\API',
    'middleware' => ['auth:api'],
    'prefix' => 'v1'
], function ($router) {
    // List product
    $router->post('/product/submit', 'ProductController@submit');
    $router->post('/product/list', 'ProductController@list');
    $router->post('/product/update', 'ProductController@update');
    $router->post('/product/delete', 'ProductController@delete');

    //List category
    $router->post('/category/submit', 'CategoryController@submit');
    $router->post('/category/list', 'CategoryController@list');
    $router->post('/category/update', 'CategoryController@update');
    $router->post('/category/delete', 'CategoryController@delete');

    //List image
    $router->post('/image/submit', 'ImageController@submit');
    $router->post('/image/list', 'ImageController@list');
    $router->post('/image/update', 'ImageController@update');
    $router->post('/image/delete', 'ImageController@delete');

    //List  category Product
    $router->post('/category-product/submit', 'CategoryProductController@submit');
    $router->post('/category-product/list', 'CategoryProductController@list');
    $router->post('/category-product/update', 'CategoryProductController@update');
    $router->post('/category-product/delete', 'CategoryProductController@delete');

    //List Product Image
    $router->post('/product-image/submit', 'ProductImageController@submit');
    $router->post('/product-image/list', 'ProductImageController@list');
    $router->post('/product-image/update', 'ProductImageController@update');
    $router->post('/product-image/delete', 'ProductImageController@delete');
});