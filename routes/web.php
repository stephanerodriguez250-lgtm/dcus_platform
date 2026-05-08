<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\AccordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\CodirController;
use App\Http\Controllers\DecisionController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// App (authentifié)
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // CODIR Interne
    Route::resource('codirs', CodirController::class);
    Route::get('codirs/{codir}/pdf', [CodirController::class, 'generatePdf'])
         ->name('codirs.pdf');
    Route::post('codirs/{codir}/rapport', [CodirController::class, 'uploadRapport'])
         ->name('codirs.rapport.upload');
    Route::get('codirs/{codir}/rapport/{rapport}/download',
         [CodirController::class, 'downloadRapport'])
         ->name('codirs.rapport.download');
    Route::delete('codirs/{codir}/rapport/{rapport}',
         [CodirController::class, 'deleteRapport'])
         ->name('codirs.rapport.delete');
    Route::post('codirs/{codir}/acces', [CodirController::class, 'gererAcces'])
         ->name('codirs.acces');

    // Décisions
    Route::resource('decisions', DecisionController::class)->except(['create', 'edit']);
    Route::get('decisions/{decision}/note/download',
    [DecisionController::class, 'downloadNote'])
    ->name('decisions.note.download');

    // Réunions
    Route::resource('reunions', ReunionController::class);
    Route::get('reunions/{reunion}/pdf', [ReunionController::class, 'generatePdf'])
     ->name('reunions.pdf');

    // Accords
    Route::resource('accords', AccordController::class);
    Route::post('accords/{accord}/statut', [AccordController::class, 'updateStatut'])
         ->name('accords.statut');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('reunions/pdf',  [ExportController::class, 'reunionsPdf'])->name('reunions.pdf');
        Route::get('reunions/csv',  [ExportController::class, 'reunionsCsv'])->name('reunions.csv');
        Route::get('accords/pdf',   [ExportController::class, 'accordsPdf'])->name('accords.pdf');
        Route::get('accords/csv',   [ExportController::class, 'accordsCsv'])->name('accords.csv');
    });

    // Gestion utilisateurs (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('utilisateurs', UserController::class);
        Route::patch('utilisateurs/{user}/toggle', [UserController::class, 'toggle'])
             ->name('utilisateurs.toggle');
    });
});
