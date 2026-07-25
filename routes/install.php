<?php

use App\Http\Controllers\Install\InstallController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes are for the installation wizard. They will be automatically
| disabled after the installation is complete (when install.lock file exists).
|
*/

Route::name('install.')->group(function () {
    // Step 1: Welcome
    Route::get('/', [InstallController::class, 'welcome'])->name('welcome');
    Route::get('/welcome', [InstallController::class, 'welcome'])->name('welcome');
    
    // Step 2: Requirements
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    
    // Step 3: Database Configuration
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/setup-database', [InstallController::class, 'setupDatabase'])->name('setup-database')
        ->middleware('throttle:10,5');
    
    // Step 4: Site Configuration
    Route::get('/site-config', [InstallController::class, 'siteConfig'])->name('site-config');
    Route::post('/save-site-config', [InstallController::class, 'saveSiteConfig'])->name('save-site-config')
        ->middleware('throttle:10,5');
    
    // Step 5: Theme Selection
    Route::get('/theme', [InstallController::class, 'theme'])->name('theme');
    Route::post('/save-theme', [InstallController::class, 'saveTheme'])->name('save-theme')
        ->middleware('throttle:10,5');
    
    // Step 6: Payment Gateway Setup
    Route::get('/payment', [InstallController::class, 'payment'])->name('payment');
    Route::post('/save-payment', [InstallController::class, 'savePayment'])->name('save-payment')
        ->middleware('throttle:10,5');
    
    // Step 7: Complete
    Route::get('/complete', [InstallController::class, 'complete'])->name('complete');
});
