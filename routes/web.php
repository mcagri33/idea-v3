<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CastleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SiteController;


Route::group(['prefix' => '/'], function () {
  Route::get('/', [SiteController::class, 'index'])
    ->name('site.index');
});


Route::group(['prefix' => '/panel', 'middleware' => ['auth']], function () {
  Route::get('/', [CastleController::class, 'index'])
    ->name('dashboard');
});


Route::group(['prefix' => '/user','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [UserController::class, 'index'])
    ->name('castle.user.index');
  Route::get('/add',[UserController::class,'create'])
    ->name('castle.user.add');
  Route::post('/store',[UserController::class,'store'])
    ->name('castle.user.store');
  Route::get('/edit/{uuid}', [UserController::class, 'edit'])
    ->name('castle.user.edit');
  Route::post('/update/{uuid}', [UserController::class, 'update'])
    ->name('castle.user.update');
    Route::delete('/delete/{user:uuid}', [UserController::class, 'destroy'])
        ->name('castle.user.delete');
    Route::get('/export/excel', [UserController::class, 'exportUsers'])->name('castle.admin.users.export.excel');


});

Route::group(['prefix' => '/roles','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [RoleController::class, 'index'])
    ->name('castle.role.index');
  Route::get('/add',[RoleController::class,'create'])
    ->name('castle.role.add');
  Route::post('/store',[RoleController::class,'store'])
    ->name('castle.role.store');
  Route::get('/edit/{id}', [RoleController::class, 'edit'])
    ->name('castle.role.edit');
  Route::post('/update/{id}', [RoleController::class, 'update'])
    ->name('castle.role.update');
  Route::get('/delete/{id}', [RoleController::class, 'destroy'])
    ->name('castle.role.delete');
});

Route::group(['prefix' => '/panel/category','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [DocumentCategoryController::class, 'index'])
    ->name('castle.category.index');
  Route::get('/create',[DocumentCategoryController::class,'create'])
    ->name('castle.category.add');
  Route::post('/store',[DocumentCategoryController::class,'store'])
    ->name('castle.category.store');
  Route::get('/edit/{uuid}', [DocumentCategoryController::class, 'edit'])
    ->name('castle.category.edit');
  Route::post('/update/{uuid}', [DocumentCategoryController::class, 'update'])
    ->name('castle.category.update');
  Route::get('/delete/{uuid}', [DocumentCategoryController::class, 'destroy'])
    ->name('castle.category.delete');
  Route::get('/menu', [DocumentCategoryController::class, 'menu'])
    ->name('castle.category.menu');
  Route::post('/update-order', [DocumentCategoryController::class, 'updateOrder'])
    ->name('castle.categories.updateOrder');

});

Route::middleware(['auth'])->group(function () {
  Route::get('/panel/category/{category:slug}', [DocumentController::class, 'showCategory'])->name('category.show');
  Route::post('/panel/category/{category:slug}/upload', [DocumentController::class, 'uploadDocument'])->name('category.upload');
  Route::get('/panel/document/{uuid}/download', [DocumentController::class, 'downloadDocument'])->name('document.download');
  Route::post('/panel/upload-image-via-ajax', [DocumentController::class, 'uploadImageViaAjax'])
    ->name('file.upload');
  Route::get('/panel/document/details', [DocumentController::class, 'showDetails'])->name('document.showDetails');
  Route::get('/panel/category/{category:slug}/uploaded-files', [DocumentController::class, 'getUploadedFiles'])
    ->name('category.getUploadedFiles');
});

Route::group(['prefix' => '/panel/user-doc','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [DocumentController::class, 'showUser'])
    ->name('user-doc');
  Route::get('/{user:uuid}/documents',[DocumentController::class,'listDoc'])
    ->name('castle.userDoc.show');
  Route::patch('/documents/{uuid}/status', [DocumentController::class, 'updateStatus'])->name('document.updateStatus');
  Route::patch('/document/{uuid}/update-note', [DocumentController::class, 'updateNote'])->name('document.updateNote');
  Route::delete('/documents/{uuid}/delete', [DocumentController::class, 'destroy'])->name('document.file.destroy');
  Route::patch('/documents/{uuid}/update-year', [DocumentController::class, 'updateYear'])->name('document.updateYear');

});

Route::group(['prefix' => '/panel/general-note','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [DocumentController::class, 'generalNote'])->name('general.note');
  Route::get('/{user}', [DocumentController::class, 'generalNoteShow'])->name('general.note.show');
  Route::post('{user}/note', [DocumentController::class, 'generalNoteSaveNote'])->name('general.note.saveNote');
  Route::get('{user}/export-pdf', [DocumentController::class, 'exportGeneralNotePdf'])->name('general.note.exportPdf');
  Route::post('{user}/send-mail', [DocumentController::class, 'sendGeneralNoteMail'])->name('general.note.sendMail');
  Route::post('{user}/category-note', [DocumentController::class, 'saveGeneralNoteCategoryNote'])->name('general.note.saveCategoryNote');
});


Route::group(['prefix' => '/panel/admin-log','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [DocumentController::class, 'adminLogs'])
    ->name('admin-log');
});

Route::group(['prefix' => '/panel/admin-log','middleware' => ['auth','role:Admin']], function () {
  Route::get('/export/pdf', [DocumentController::class, 'exportPDF'])->name('castle.admin.log.export.pdf');
  Route::get('/export/excel', [DocumentController::class, 'exportExcel'])->name('castle.admin.log.export.excel');
});

Route::group(['prefix' => '/panel/user-log','middleware' => ['auth']], function () {
  Route::get('/', [DocumentController::class, 'userLogs'])
    ->name('user-log');
});

Route::group(['prefix' => '/panel/bagimsiz-denetim-raporu-user','middleware' => ['auth']], function () {
  Route::get('/', [DocumentController::class, 'userDocuments'])->name('bagimsiz-denetim-raporu-user');
  Route::get('/download/{id}', [DocumentController::class, 'downloadUserDocument'])->name('castle.user.document.download');
});

Route::group(['prefix' => '/panel/document-status','middleware' => ['auth','role:Admin']], function () {
 Route::get('/', [DocumentController::class, 'showDocumentStatus'])
        ->name('documents.status');
	Route::post('/document-status/note-update', [DocumentController::class, 'updateCategoryNote'])->name('documents.status.note.update');

});

Route::group(['prefix' => '/panel/document-status-user','middleware' => ['auth','role:Customer']], function () {
 
	    Route::get('/', [DocumentController::class, 'showDocumentStatusForUser'])->name('user.documents.status');

});

Route::get('/panel/users/search', function(Request $request) {
    $q = $request->input('q');
    $users = User::role('customer')
        ->where('name', 'like', "%{$q}%")
        ->orWhere('email', 'like', "%{$q}%")
        ->limit(10)
        ->get(['id', 'name', 'email']);
    return response()->json($users);
})->middleware(['auth','role:Admin']);


Route::group(['prefix' => '/panel/user-to-document','middleware' => ['auth','role:Admin']], function () {
  Route::get('/', [DocumentController::class, 'showUploadForm'])
    ->name('castle.user.document.upload');
  Route::post('/store', [DocumentController::class, 'uploadUserDocument'])
    ->name('castle.user.document.upload.store');
});

Route::get('/panel/category/{uuid}/template-download', [DocumentController::class, 'downloadTemplate'])
    ->name('category.template.download');


Route::group(['prefix' => '/panel/assignment','middleware' => ['auth','role:Admin']], function () {
	Route::get('/', [DocumentController::class, 'assignmentIndex'])->name('assignments.index');
    Route::get('/create', [DocumentController::class, 'assignmentShow'])->name('assignments.create');
    Route::post('/store', [DocumentController::class, 'assignmentStore'])->name('assignments.store');
	Route::delete('/{id}', [DocumentController::class, 'assignmentDestroy'])->name('assignments.destroy');
});

Route::group(['prefix' => '/panel/myAssignment','middleware' => ['auth','role:Customer']], function () {
        Route::get('/', [DocumentController::class, 'myAssignments'])->name('user.assignments');
});

Route::group(['prefix' => '/panel/documentCheck','middleware' => ['auth','role:Admin']], function () {
        Route::get('/', [DocumentController::class, 'documentCheck'])->name('document.check');
});
