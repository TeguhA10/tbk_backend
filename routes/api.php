<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

Route::apiResource('categories', CategoryController::class);
Route::apiResource('coas', CoaController::class);
Route::apiResource('transactions', TransactionController::class);

Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);
Route::get('/reports/profit-loss/export', [ReportController::class, 'exportProfitLoss']);
