<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\CCRController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\EntryController;
use App\Http\Middleware\RUEmployeeMiddleware;
use App\Http\Middleware\RUOrThirdPartyCashierEmployeeMiddleware;
use App\Http\Middleware\RoomsAdministratorMiddleware;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

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

// Fix wrong style/mix urls when being served from reverse proxy
URL::forceRootUrl(config('app.url'));

Route::group(['middleware' => ['web']], function () {
    Route::group(['middleware' => ['auth', 'verified']], function () {
    });
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::middleware(RUEmployeeMiddleware::class)->namespace('\App\Http\Controllers')->group(function () {
        Route::get   ('/menu',           [MenuController::class, 'index'])          ->name('web.menu.index');
        Route::post  ('/menu',           [MenuController::class, 'filter'])         ->name('web.menu.filter');
        Route::get   ('/menu/create',    [MenuController::class, 'create'])         ->name('web.menu.create');
        Route::get   ('/menu/edit/{id}', [MenuController::class, 'edit'])           ->name('web.menu.edit');
        Route::post  ('/menu/form',      [MenuController::class, 'createOrUpdate']) ->name('web.menu.createOrUpdate');
        Route::delete('/menu/{date}',    [MenuController::class, 'delete'])         ->name('web.menu.delete');

        Route::get ('/user',                        [UserController::class, 'index'])          ->name('web.user.index');
        Route::get ('/user/create',                 [UserController::class, 'create'])         ->name('web.user.create');
        Route::post('/user/form',                   [UserController::class, 'form'])           ->name('web.user.form');
        Route::post('/user/forgot-password/${uid}', [UserController::class, 'forgotPassword']) ->name('web.user.forgot-password');
        Route::put('/user/${uid}/${active?}', [UserController::class, 'changeUserActivity']) ->name('web.user.changeUserActivity');
        Route::post('/user/reset-password/${uid}',  [UserController::class, 'resetPassword'])  ->name('web.user.reset-password');
        //Route::delete('/user/{id}',                 [UserController::class, 'delete'])        ->name('web.user.delete');

        Route::post('/report/entry', [ReportController::class, 'redirectEntryReport']) ->name('web.report.redirect-entry-report');
    });

    Route::middleware(RUOrThirdPartyCashierEmployeeMiddleware::class)->namespace('\App\Http\Controllers')->group(function () {
        Route::get('/entry',  [EntryController::class, 'index'])  ->name('web.entry.index');
        Route::get('/ticket', [TicketController::class, 'index']) ->name('web.ticket.index');
        Route::get('/report', [ReportController::class, 'index']) ->name('web.report.index');
        Route::get('/sell',   [SellController::class, 'index'])   ->name('web.sell.index');

        Route::post('/sell', [SellController::class, 'sellTicket']) ->name('web.sell.sell-ticket');
        Route::post('/sell-visitor', [SellController::class, 'sellVisitorTicket']) ->name('web.sell.sell-visitor-ticket');
        Route::post('/sell-third-party', [SellController::class, 'sellThirdPartyTicket']) ->name('web.sell.sell-third-party-ticket');
        Route::post('/report/ticket', [ReportController::class, 'redirectTicketReport']) ->name('web.report.redirect-ticket-report');
    });

    Route::middleware(RoomsAdministratorMiddleware::class)->namespace('\App\Http\Controllers')->group(function () {
        Route::get('/block', [BlockController::class, 'index'])->name('web.block.index');
        Route::get('/block/create', [BlockController::class, 'create'])->name('web.block.create');
        Route::get('/block/edit/{id}', [BlockController::class, 'edit'])->name('web.block.edit');
        Route::post('/block/form', [BlockController::class, 'createOrUpdate'])->name('web.block.createOrUpdate');

        Route::get('/room', [RoomController::class, 'index'])->name('web.room.index');
        Route::get('/room/create', [RoomController::class, 'create'])->name('web.room.create');
        Route::get('/room/edit/{id}', [RoomController::class, 'edit'])->name('web.room.edit');
        Route::post('/room/form', [RoomController::class, 'createOrUpdate'])->name('web.room.createOrUpdate');

        Route::get('/ccr', [CCRController::class, 'index'])->name('web.ccr.index');
        Route::get('/ccr/create', [CCRController::class, 'create'])->name('web.ccr.create');
        Route::get('/ccr/edit/{id}', [CCRController::class, 'edit'])->name('web.ccr.edit');
        Route::post('/ccr/form', [CCRController::class, 'createOrUpdate'])->name('web.ccr.createOrUpdate');

        Route::get('/lessee', [UserController::class, 'lessee'])->name('web.lessee.index');
        Route::post('/lessee', [UserController::class, 'changeLesseePermission'])->name('web.lessee.changeLesseePermission');
    });
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard',             [DashboardController::class, 'index'])->name('dashboard');

Route::get('/reset-password', [AuthController::class, 'redirectResetPassword'])->name('web.auth.redirectResetPassword');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('web.auth.resetPassword');
Route::get('/', [AuthController::class, 'index'])->name('web.auth.index');

