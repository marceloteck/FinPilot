<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\sendEmailController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Finpilot\PageController;
use App\Http\Controllers\Finpilot\DebtController;
use App\Http\Controllers\Finpilot\AIController;
use App\Http\Controllers\Finpilot\AdminHotmartController;
use App\Http\Controllers\Finpilot\ReportsController;
use App\Http\Controllers\Finpilot\TransactionController;
use App\Http\Controllers\Finpilot\ViewController;
use App\Http\Controllers\Pages\index\HomeContollerRoutes;

use function Termwind\render;

// Rotas principais do usuário
Route::middleware('guest')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('finpilot.home');
    Route::get('/about', [HomeContollerRoutes::class, 'about'])->name('index.about');
    Route::get('/contact', [HomeContollerRoutes::class, 'contact'])->name('index.contact');
    Route::get('/project', [HomeContollerRoutes::class, 'project'])->name('index.project');
    Route::get('/education', [HomeContollerRoutes::class, 'education'])->name('index.education');
    Route::get('/experiences', [HomeContollerRoutes::class, 'experiences'])->name('index.experiences');
});
// end rotas

Route::get('/transactions', [TransactionController::class, 'index'])->name('finpilot.transactions');
Route::post('/transactions', [TransactionController::class, 'store'])->name('finpilot.transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('finpilot.transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('finpilot.transactions.destroy');

Route::post('/views', [ViewController::class, 'store'])->name('finpilot.views.store');
Route::put('/views/{view}', [ViewController::class, 'update'])->name('finpilot.views.update');
Route::delete('/views/{view}', [ViewController::class, 'destroy'])->name('finpilot.views.destroy');

Route::get('/debts', [DebtController::class, 'index'])->name('finpilot.debts');
Route::post('/debts', [DebtController::class, 'store'])->name('finpilot.debts.store');
Route::put('/debts/{debt}', [DebtController::class, 'update'])->name('finpilot.debts.update');
Route::delete('/debts/{debt}', [DebtController::class, 'destroy'])->name('finpilot.debts.destroy');

Route::get('/ai', [AIController::class, 'index'])->name('finpilot.ai');
Route::post('/ai/generate', [AIController::class, 'generate'])->name('finpilot.ai.generate');
Route::post('/ai/confirm', [AIController::class, 'confirm'])->name('finpilot.ai.confirm');
Route::post('/ai/feedback', [AIController::class, 'feedback'])->name('finpilot.ai.feedback');
Route::get('/goals', [PageController::class, 'goals'])->name('finpilot.goals');
Route::get('/reports', [ReportsController::class, 'index'])->name('finpilot.reports');
Route::get('/settings', [PageController::class, 'settings'])->name('finpilot.settings');

Route::middleware(['auth', 'first.user.admin'])->group(function () {
    Route::get('/admin/hotmart', [AdminHotmartController::class, 'index'])->name('admin.hotmart');
    Route::post('/admin/hotmart', [AdminHotmartController::class, 'update'])->name('admin.hotmart.update');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// EMAIL
route::post('/', [sendEmailController::class, 'Send'])->name('sendEmail');
route::get('/viewemail', [sendEmailController::class, 'view'])->name('viewemail');



// TESTES
// Route::get('/testemail', function () { return Inertia::render('Pages/testes/email'); })->name('test');
 Route::get('/teste', function () { return Inertia::render('Pages/testes/test'); })->name('test');

require __DIR__.'/auth.php';
