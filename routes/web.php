<?php

use App\Http\Controllers\AccordAppreciateurController;
use App\Http\Controllers\AccordAppreciationController;
use App\Http\Controllers\AccordAvisMesrsController;
use App\Http\Controllers\AccordConformiteController;
use App\Http\Controllers\AccordController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ArchivePartageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CodirController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Mot de passe oublié
Route::middleware('guest')->group(function () {
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])
        ->name('password.update');

    // Inscription sur invitation
    Route::get('/inscription/{token}', [RegistrationController::class, 'show'])
        ->name('invitation.accept');
    Route::post('/inscription', [RegistrationController::class, 'store'])
        ->name('invitation.store');
});

// App (authentifié)
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::post('notifications/tout-lire', [NotificationController::class, 'marquerToutLu'])
        ->name('notifications.tout-lire');
    Route::post('notifications/{id}/lire', [NotificationController::class, 'marquerLu'])
        ->name('notifications.lire');

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
    // Route-order note: "accords/appreciateurs" is declared before the "accords" resource's
    // GET accords/{accord} route since both match a single path segment.
    Route::middleware('role:admin')->group(function () {
        Route::get('accords/appreciateurs', [AccordAppreciateurController::class, 'index'])
            ->name('accords.appreciateurs.index');
        Route::post('accords/appreciateurs', [AccordAppreciateurController::class, 'store'])
            ->name('accords.appreciateurs.store');
        Route::delete('accords/appreciateurs/{appreciateur}', [AccordAppreciateurController::class, 'destroy'])
            ->name('accords.appreciateurs.destroy');
    });

    Route::resource('accords', AccordController::class);
    Route::post('accords/{accord}/envoyer', [AccordController::class, 'envoyer'])
        ->name('accords.envoyer');
    Route::post('accords/{accord}/signer', [AccordController::class, 'signer'])
        ->name('accords.signer');

    Route::get('accords/{accord}/apprecier', [AccordAppreciationController::class, 'create'])
        ->name('accords.apprecier.create');
    Route::post('accords/{accord}/apprecier', [AccordAppreciationController::class, 'store'])
        ->name('accords.apprecier.store');
    Route::post('accords/{accord}/apprecier/suggestion', [AccordAppreciationController::class, 'suggerer'])
        ->name('accords.apprecier.suggestion');
    Route::get('accords/appreciations/{appreciation}/telecharger', [AccordAppreciationController::class, 'telecharger'])
        ->name('accords.appreciations.telecharger');

    Route::get('accords/{accord}/avis-mesrs', [AccordAvisMesrsController::class, 'create'])
        ->name('accords.avis-mesrs.create');
    Route::post('accords/{accord}/avis-mesrs', [AccordAvisMesrsController::class, 'store'])
        ->name('accords.avis-mesrs.store');
    Route::post('accords/{accord}/avis-mesrs/suggestion', [AccordAvisMesrsController::class, 'suggerer'])
        ->name('accords.avis-mesrs.suggestion');

    Route::post('accords/{accord}/analyser-conformite', [AccordConformiteController::class, 'analyser'])
        ->name('accords.analyser-conformite');
    Route::get('accords/rapports-conformite/{rapport}/telecharger', [AccordConformiteController::class, 'telecharger'])
        ->name('accords.rapports-conformite.telecharger');

    // Archives (espace privé par utilisateur)
    Route::get('archives/fichiers/create', [ArchiveController::class, 'createFichier'])
        ->name('archives.fichiers.create');
    Route::post('archives/fichiers', [ArchiveController::class, 'storeFichier'])
        ->name('archives.fichiers.store');
    Route::patch('archives/fichiers/{fichier}', [ArchiveController::class, 'updateFichier'])
        ->name('archives.fichiers.update');
    Route::delete('archives/fichiers/{fichier}', [ArchiveController::class, 'destroyFichier'])
        ->name('archives.fichiers.destroy');
    Route::get('archives/fichiers/{fichier}/download', [ArchiveController::class, 'downloadFichier'])
        ->name('archives.fichiers.download');
    Route::get('archives/fichiers/{fichier}/apercu', [ArchiveController::class, 'apercuFichier'])
        ->name('archives.fichiers.apercu');
    Route::patch('archives/fichiers/{fichier}/deplacer', [ArchiveController::class, 'deplacerFichier'])
        ->name('archives.fichiers.deplacer');

    Route::post('archives/dossiers', [ArchiveController::class, 'storeDossier'])
        ->name('archives.dossiers.store');
    Route::patch('archives/dossiers/{dossier}', [ArchiveController::class, 'updateDossier'])
        ->name('archives.dossiers.update');
    Route::delete('archives/dossiers/{dossier}', [ArchiveController::class, 'destroyDossier'])
        ->name('archives.dossiers.destroy');
    Route::patch('archives/dossiers/{dossier}/deplacer', [ArchiveController::class, 'deplacerDossier'])
        ->name('archives.dossiers.deplacer');

    Route::get('archives/partages', [ArchivePartageController::class, 'index'])
        ->name('archives.partages.index');
    Route::post('archives/partages', [ArchivePartageController::class, 'store'])
        ->name('archives.partages.store');
    Route::delete('archives/partages/{partage}', [ArchivePartageController::class, 'destroy'])
        ->name('archives.partages.destroy');

    Route::delete('archives/dossier-partages/{partage}', [ArchivePartageController::class, 'destroyDossier'])
        ->name('archives.dossier-partages.destroy');
    Route::get('archives/dossier-partages/{partage}/telecharger', [ArchivePartageController::class, 'telechargerDossier'])
        ->name('archives.dossier-partages.telecharger');

    Route::get('archives/{dossier?}', [ArchiveController::class, 'index'])
        ->name('archives.index');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('reunions/pdf', [ExportController::class, 'reunionsPdf'])->name('reunions.pdf');
        Route::get('reunions/csv', [ExportController::class, 'reunionsCsv'])->name('reunions.csv');
        Route::get('accords/pdf', [ExportController::class, 'accordsPdf'])->name('accords.pdf');
        Route::get('accords/csv', [ExportController::class, 'accordsCsv'])->name('accords.csv');
    });

    // Gestion utilisateurs (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('utilisateurs', UserController::class);
        Route::patch('utilisateurs/{user}/toggle', [UserController::class, 'toggle'])
            ->name('utilisateurs.toggle');
        Route::post('utilisateurs/invitations/{invitation}/renvoyer', [UserController::class, 'renvoyerInvitation'])
            ->name('utilisateurs.invitations.renvoyer');
        Route::delete('utilisateurs/invitations/{invitation}', [UserController::class, 'revoquerInvitation'])
            ->name('utilisateurs.invitations.revoquer');
    });
});
