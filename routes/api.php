<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Document;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// AI Bot API Endpoints
Route::get('/documents/all', function () {
    return Document::with(['category:id,name,slug','user:id,name,company,uuid'])
        ->select('id','user_id','category_id','file_path','status','rejection_note','document_name','file_year','created_at')
        ->get();
});

Route::post('/bot/feedback', function (Request $request) {
    $request->validate([
        'file_path' => 'required',
        'approved' => 'required|boolean',
        'note' => 'nullable|string'
    ]);

    $doc = Document::where('file_path', $request->file_path)->first();
    
    if ($doc) {
        $doc->status = $request->approved ? 1 : 0;
        $doc->rejection_note = $request->note ?? null;
        $doc->save();

        return response()->json(['success' => true, 'message' => 'Feedback kaydedildi.']);
    }

    return response()->json(['success' => false, 'message' => 'Doküman bulunamadı.'], 404);
});
