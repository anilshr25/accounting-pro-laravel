<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\Business\BusinessController;
use App\Http\Controllers\OwnerUser\OwnerUserController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/health-check', fn() => response()->json(['status' => 'OK']));

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web'],
], function ($route) {


    $route->post('login', [
        AdminLoginController::class,
        'login',
    ]);


    $route->group(['middleware' => ['auth:admin'],], function ($route) {

        $route->get('profile', [AdminLoginController::class, 'profile',]);

        $route->post('logout', [AdminLoginController::class, 'logout',]);

        $route->get('/owner-user', [OwnerUserController::class, 'index']);
        $route->post('/owner-user', [OwnerUserController::class, 'store']);
        $route->get('/owner-user/{id}', [OwnerUserController::class, 'show']);
        $route->put('/owner-user/{id}', [OwnerUserController::class, 'update']);
        $route->delete('/owner-user/{id}', [OwnerUserController::class, 'destroy']);

        $route->get('/business', [BusinessController::class, 'index']);
        $route->get('/business/search', [BusinessController::class, 'search']);
        $route->post('/business', [BusinessController::class, 'store']);
        $route->get('/business/{id}', [BusinessController::class, 'show']);
        $route->put('/business/{id}', [BusinessController::class, 'update']);
        $route->delete('/business/{id}', [BusinessController::class, 'destroy']);

        $route->get('permission', [PermissionController::class, 'index']);
        $route->post('permission', [PermissionController::class, 'store']);
        $route->get('permission/{id}', [PermissionController::class, 'show']);
        $route->put('permission/{id}', [PermissionController::class, 'update']);
        $route->delete('permission/{id}', [PermissionController::class, 'destroy']);

        $route->get('role', [RoleController::class, 'index']);
        $route->post('role', [RoleController::class, 'store']);
        $route->get('role/{id}', [RoleController::class, 'show']);
        $route->put('role/{id}', [RoleController::class, 'update']);
        $route->delete('role/{id}', [RoleController::class, 'destroy']);
    });
});

Route::group([
    'prefix' => 'owner',
    'middleware' => ['web'],
], function ($route) {

    $route->get('/user', [UserController::class, 'index']);
    $route->post('/user', [UserController::class, 'store']);
    $route->get('/user/{id}', [UserController::class, 'show']);
    $route->put('/user/{id}', [UserController::class, 'update']);
    $route->delete('/user/{id}', [UserController::class, 'destroy']);
    $route->post('/user/{id}/business', [UserController::class, 'assignBusiness']);
    $route->put('/user/{id}/business/{tenantId}', [UserController::class, 'updateBusinessAccess']);
    $route->delete('/user/{id}/business/{tenantId}', [UserController::class, 'removeBusiness']);
    $route->get('/user/{id}/businesses', [UserController::class, 'businesses']);

    $route->post('role/{id}/permission', [RoleController::class, 'assignPermissions']);
    $route->get('role/{id}/permission', [RoleController::class, 'permissions']);
});

Route::group([
    'prefix' => 'auth',
    'middleware' => ['web'],
], function ($route) {


    $route->post('/login', [LoginController::class, 'login'])->name('auth.login');
    $route->post('/select-business', [LoginController::class, 'selectBusiness'])->middleware('auth:owner,user');
    $route->get('/verify', [LoginController::class, 'verify'])->name('auth.verify');
    $route->post('/logout', [LoginController::class, 'logout'])->name('auth.logout');
});
