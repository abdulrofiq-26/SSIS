<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\BrandController;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('index');


require __DIR__.'/auth.php';

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('log-viewers', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware(['can:log-viewers.read']);

// Route Group Setting (Users, Roles, Permissions)
Route::group(['prefix' => 'setting','middleware' => ['auth']],function () {
    // ... (Route Users, Roles, Permissions tetap sama) ...
    Route::group(['prefix' => 'manage_account'], function () {
        Route::group(['prefix' => 'users'], function () { //route to manage users
            Route::any('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/data', [UserController::class, 'data'])->name('users.data');
            Route::any('/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
            Route::any('/reset_password/{id}', [UserController::class, 'reset_password'])->name('users.reset_password');
            Route::delete('/delete', [UserController::class, 'delete'])->name('users.delete');
        });
        Route::group(['prefix' => 'roles'], function () { //route to manage roles
            Route::any('/', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/data', [RoleController::class, 'data'])->name('roles.data');
            Route::any('/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
            Route::delete('/destroy', [RoleController::class, 'destroy'])->name('roles.destroy');
        });
        Route::group(['prefix' => 'permissions'], function () { //route to manage permissions
            Route::any('/', [PermissionController::class, 'index'])->name('permissions.index');
            Route::get('/data', [PermissionController::class, 'data'])->name('permissions.data');
            Route::get('/view/{id}', [PermissionController::class, 'view'])->name('permissions.view');
            Route::get('/view/{id}/users', [PermissionController::class, 'view_users_data'])->name('permissions.view_users_data');
            Route::get('/view/{id}/roles', [PermissionController::class, 'view_roles_data'])->name('permissions.view_roles_data');
        });
    });
});

// Route search
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/results', [SearchController::class, 'performSearch'])->name('search.results');
// **Rute POST untuk Pencarian Gambar**
Route::post('/search/image', [SearchController::class, 'performImageSearch'])->name('search.image');
Route::get('/test-storage', function () {
    try {
        // Perintah ini secara paksa mencoba menulis file ke disk 'local'
        Storage::disk('local')->put('test.txt', 'Halo Dunia, file ini berhasil dibuat.');

        // Jika berhasil, cek file di folder storage/app/test.txt
        return 'TES BERHASIL: File `test.txt` berhasil dibuat di folder `storage/app/`.';

    } catch (\Exception $e) {
        // Jika gagal, tampilkan errornya
        return 'TES GAGAL: ' . $e->getMessage();
    }
});

// ROUTE GROUP UNTUK MASTER DATA (Brands & Spareparts)
Route::group(['prefix' => 'master_data','middleware' => ['auth']],function () {
    
    // CRUD Brand
    // Route DataTables
    Route::get('brands/data', [BrandController::class, 'data'])->name('brands.data'); 
    // Route Delete AJAX
    Route::delete('brands/delete', [BrandController::class, 'delete'])->name('brands.delete');
    // Route Resource (brands.index, brands.store, brands.edit, brands.update)
    Route::resource('brands', BrandController::class)->only(['index', 'store', 'edit', 'update']);
    // Route Impor/Template
    Route::get('brands/download-template', [BrandController::class, 'downloadTemplate'])->name('brands.template');
    Route::post('brands/import', [BrandController::class, 'import'])->name('brands.import');


    // CRUD Sparepart (DIPASTIKAN DI SINI UNTUK KONSISTENSI)
    Route::get('spareparts/download-template', [SparepartController::class, 'downloadTemplate'])->name('spareparts.template');
    Route::post('spareparts/import', [SparepartController::class, 'import'])->name('spareparts.import');
    Route::delete('/sparepart-images/{image}', [SparepartController::class, 'deleteImage'])->name('sparepart.image.delete');

    // ⭐ TAMBAHKAN DUA ROUTE INI (Meniru Brand)
    // 1. Route untuk DataTables Server-Side
    Route::get('spareparts/data', [SparepartController::class, 'data'])->name('spareparts.data');
    // 2. Route untuk SweetAlert AJAX Delete
    Route::delete('spareparts/delete', [SparepartController::class, 'delete'])->name('spareparts.delete');
    // Route Resource Sparepart (akan memiliki prefix: master_data/spareparts/...)
    Route::resource('spareparts', SparepartController::class)->except(['destroy']);
});