<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use App\Models\DocumentCategory;
use App\Models\Document;
use App\Models\DocumentLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LogsExport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\DocumentCategoryNote; 
use App\Models\DocumentAssignment;
use App\Mail\TaskAssignedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\UserGeneralNote;
use App\Mail\GeneralNoteReminderMail; 

class DocumentController extends Controller
{

  use LogsActivity;
  public function showCategory(DocumentCategory $category)
  {
    $documents = $category->documents()
      ->where('user_id', Auth::id())
      ->orderBy('created_at', 'desc')
      ->paginate(5);

    $desc = $category->description;
    return view('panel.document.upload', compact('category', 'documents','desc'));
  }

  public function getUploadedFiles(DocumentCategory $category)
  {
    $documents = $category->documents()
      ->where('user_id', Auth::id())
      ->with('lastDownloadLog.performedBy:id,name')
      ->orderBy('created_at', 'desc')
      ->get();

    return response()->json(['files' => $documents]);
  }


 public function uploadDocument(Request $request, DocumentCategory $category)
  {
    $user = Auth::user();
    $fileYear = $request->input('file_year');
    $documentName = $request->input('document_name');
    $description = $request->input('description');

    if ($request->has('images') && !empty($request->input('images'))) {
      $files = $request->input('images');
      foreach ($files as $file) {
          $document = Document::create([
            'user_id' => Auth::id(),
            'category_id' => $category->id,
            'file_path' => $file,
            'file_year' => $fileYear,
            'uuid' => Str::uuid(),
            'document_name' => $documentName,
            'description' => $description,
            'status' => 2,
            'rejection_note' => null,
          ]);
          
		  
		  
          // Eğer görev ID'si varsa bu belge göreve bağlanır
          if ($request->filled('assignment_id')) {
              \App\Models\DocumentAssignment::where('id', $request->assignment_id)
                  ->where('user_id', $user->id)
                  ->update([
                      'document_id' => $document->id,
                      'status' => 'uploaded'
                  ]);
          }
		  

        $this->logActivity(
          'Eklendi',
          $category->name,
          "{$user->company} firması {$category->name} evrakına belge ekledi.",
          $user->company,
          now()
        );


      }

      return redirect()->back()->with('success', 'Dosya Başarıyla Yüklendi');
    }else{
    return redirect()->back()->with('error', 'Dosya yüklenmedi.');
    }
  }

	private function notifyBot($document)
{
    try {
        $botWebhook = env('BOT_WEBHOOK_URL', 'http://127.0.0.1:3002/webhook/new-document');
        
        Http::timeout(5)->post($botWebhook, [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'category_id' => $document->category_id,
            'file_path' => $document->file_path,
            'document_name' => $document->document_name,
            'file_year' => $document->file_year
        ]);
        
        Log::info('Bot webhook gönderildi', ['document_id' => $document->id]);
    } catch (\Exception $e) {
        Log::warning('Bot webhook hatası: ' . $e->getMessage());
        // Hata olsa bile dosya yükleme devam etsin
    }
}

  public function downloadDocument($uuid)
  {
    try {
      $documentQuery = Document::where('uuid', $uuid);

      if (!Auth::user()->hasRole('Admin')) {
        $documentQuery->where('user_id', Auth::id());
      }

      $document = $documentQuery->firstOrFail();

      $filePath = Storage::disk('public')->path($document->file_path);

      info("Aranan dosya yolu: {$filePath}");

      if (!Storage::disk('public')->exists($document->file_path)) {
        return redirect()->back()->with('error', 'Dosya bulunamadı.');
      }

      // Dosyayı indir
      $response = response()->download($filePath, basename($document->file_path));

      // İndirme başarılı olduysa log kaydı oluştur
      try {
        DocumentLog::create([
          'document_id' => $document->id,
          'action' => 'download',
          'performed_by' => Auth::id(),
          'note' => 'Dosya indirildi: ' . basename($document->file_path),
        ]);
      } catch (\Exception $e) {
        // Log kaydı oluşturulamazsa, dosya indirme işlemi devam etsin
        Log::warning('Document download log kaydı oluşturulamadı', [
          'document_id' => $document->id,
          'user_id' => Auth::id(),
          'error' => $e->getMessage()
        ]);
      }

      return $response;
    } catch (\Exception $e) {
      Log::error('Document download hatası', [
        'uuid' => $uuid,
        'user_id' => Auth::id(),
        'error' => $e->getMessage()
      ]);

      return redirect()->back()->with('error', 'Dosya indirilirken bir hata oluştu.');
    }
  }


    public function showUser(Request $request)
    {
        $query = User::role('Customer');

        // Eğer arama sorgusu varsa, filtreleme yap
        if ($request->has('q') && !empty($request->q)) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->paginate(10);

        return view('panel.document.userShow', compact('users'));
    }

    public function listDoc(Request $request, User $user)
{
    $query = Document::whereHas('user', function ($q) use ($user) {
        $q->where('uuid', $user->uuid);
    })  ->with(['category', 'user', 'lastDownloadLog.performedBy']) // Eager loading ekle  
    ;

    // Filtreler
    if ($request->filled('file_year')) {
        $query->where('file_year', $request->file_year);
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('document_name')) {
        $query->where('document_name', 'like', '%' . $request->document_name . '%');
    }

    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $documents = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->all());

    $categories = DocumentCategory::all();

    return view('panel.document.documentShow', compact('user', 'documents', 'categories'));
}

	public function updateYear(Request $request, $uuid)
{
    $request->validate([
        'file_year' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
    ]);

    $document = Document::where('uuid', $uuid)->firstOrFail();
    $document->file_year = $request->file_year;
    $document->save();

    return redirect()->back()->with('success', 'Dosya yılı güncellendi.');
}

  public function updateStatus(Request $request, $uuid, DocumentCategory $category)
{
    try {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $selectedUser = $document->user;
        $newStatus = $request->status;
        $oldStatus = $document->status;

        // rejection_note varsa kaydet
        if ($newStatus == 0 && $request->filled('rejection_note')) {
            $document->rejection_note = $request->input('rejection_note');
        }

        $document->status = $newStatus;
        $document->save();

        // Status değişikliğini DocumentLog'a kaydet
        if ($oldStatus != $newStatus) {
            $action = $newStatus == 1 ? 'approve' : ($newStatus == 0 ? 'reject' : 'pending');
            DocumentLog::create([
                'document_id' => $document->id,
                'action' => $action,
                'performed_by' => Auth::id(),
                'note' => $newStatus == 1 ? 'Belge onaylandı' : ($newStatus == 0 ? 'Belge reddedildi' : 'Belge beklemede'),
            ]);
        }

        $statusText = $newStatus == 1 ? 'Onaylandı' : 'Reddedildi';

        $description = "{$document->document_name} adlı evrakın son durumu: {$statusText}";
        if ($newStatus == 0 && $document->rejection_note) {
            $description .= " | Red Notu: " . $document->rejection_note;
        }

        $category = $document->category;

        ActivityLog::create([
            'user_id' => $selectedUser->id,
            'company_name' => $document->user->company ?? 'Belirtilmedi',
            'action_type' => $statusText,
            'model_type' => $category->name,
            'description' => $description,
            'approved_at' => $newStatus == 1 ? now() : null,
            'file_created_at' => $document->created_at
        ]);
        return redirect()->back()->with('success', 'Dosya Durumu Başarıyla Güncellendi');
    } catch (\Exception $e) {
        Log::error('Evrak Durumu Güncellenemedi: ', ['error' => $e->getMessage()]);
        return back()->withErrors('Bir hata oluştu: ' . $e->getMessage());
    }
}


  public function updateNote(Request $request, $uuid)
  {
    $request->validate([
      'admin_note' => 'nullable|string|max:255',
    ]);

    $document = Document::where('uuid', $uuid)->firstOrFail();
    $document->rejection_note = $request->rejection_note;
    $document->save();

    return back()->with('success', 'Admin notu başarıyla güncellendi.');
  }

  public function uploadImageViaAjax(Request $request)
  {
    Log::info('uploadImageViaAjax method started');
    $user = \Illuminate\Support\Facades\Auth::user();
    $names = [];
    $originalNames = [];

    if ($request->hasFile('file')) {
      foreach ($request->file('file') as $file) {
        $userName = Str::slug($user->name) . '-' . Str::slug($user->company);
        $imageName = uniqid() . time() . '.' . $file->getClientOriginalExtension();

        $directoryPath = 'documents/' . $userName;
        $filePath = $directoryPath . '/' . $imageName;

        $file->storeAs('public/' . $directoryPath, $imageName);

        $names[] = $filePath;
        $originalNames[] = $file->getClientOriginalName();
      }
    } else {
      Log::error('No file found in the uploadImageViaAjax request');
      return response()->json(['error' => 'No files found'], 400);
    }

    return response()->json([
      'name' => $names,
      'original_name' => $originalNames
    ]);
  }

  public function adminLogs(Request $request)
  {
    $uniqueFileTypes = ActivityLog::select('model_type')->distinct()->pluck('model_type');
    $uniqueActionTypes = ActivityLog::select('action_type')->distinct()->pluck('action_type');

    $logs = ActivityLog::query();

    if ($request->filled('start_date')) {
      $logs->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
      $logs->whereDate('created_at', '<=', $request->end_date);
    }

    if ($request->filled('model_type')) {
      $logs->where('model_type', $request->model_type);
    }

    if ($request->filled('company_name')) {
      $logs->where('company_name', 'like', '%' . $request->company_name . '%');
    }

    if ($request->filled('action_type')) {
      $logs->where('action_type', $request->action_type);
    }

    $logs = $logs->orderBy('created_at', 'desc')->paginate(10);

    return view('panel.logs.admin-logs', compact('logs', 'uniqueFileTypes', 'uniqueActionTypes'));
  }


  public function userLogs(Request $request)
  {
    $user = Auth::user();
    $uniqueFileTypes = ActivityLog::select('model_type')->distinct()->pluck('model_type');

    $logs = ActivityLog::where('user_id', $user->id);

    if ($request->filled('start_date')) {
      $logs->whereDate('created_at', '>=', $request->input('start_date'));
    }
    if ($request->filled('end_date')) {
      $logs->whereDate('created_at', '<=', $request->input('end_date'));
    }

    if ($request->filled('model_type')) {
      $logs->where('model_type', $request->input('model_type'));
    }

    $logs = $logs->orderBy('created_at', 'desc')->paginate(10);

    return view('panel.logs.user-logs', compact('logs', 'uniqueFileTypes'));
  }


  public function exportPDF(Request $request)
  {
    $logs = ActivityLog::query();

    if ($request->filled('start_date')) {
      $logs->where('file_created_at', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
      $logs->where('file_created_at', '<=', $request->end_date);
    }

    if ($request->filled('model_type')) {
      $logs->where('model_type', $request->model_type);
    }

    if ($request->filled('company_name')) {
      $logs->where('company_name', 'LIKE', '%' . $request->company_name . '%');
    }

    if ($request->filled('action_type')) {
      $logs->where('action_type', $request->action_type);
    }

    $logsAsPdf = $logs->get();

    $pdf = PDF::loadView('logs.export_pdf', compact('logsAsPdf'));

    return $pdf->download('logs_filtered.pdf');
  }

  public function exportExcel(Request $request)
  {
    return Excel::download(new LogsExport($request->all()), 'logs_filtered.xlsx');
  }

  public function userDocuments()
  {
    $user = Auth::user();
    $userId = $user->id;

    $documents = UserDocument::where('user_id', $userId)->paginate(10);
    //dd($userId);
    return view('panel.report.userDocuments', compact('documents'));
  }

  public function downloadUserDocument($id)
  {
    $document = UserDocument::findOrFail($id);

    if ($document->user_id !== auth()->id()) {
      abort(403);
    }

    return response()->download(storage_path('app/public/' . $document->path));
  }

  public function showUploadForm()
  {
    $users = User::all();
    return view('panel.report.upload-form',compact('users'));
  }

  public function uploadUserDocument(Request $request)
  {

    $user = $request->user_id;
    $documentName = $request->input('document_name');
    $description = $request->input('description');

    if ($request->has('images') && !empty($request->input('images'))) {
      $files = $request->input('images');
      foreach ($files as $filePath) {
        $document = new UserDocument([
          'user_id' => $user,
          'uuid' => Str::uuid(),
          'document_name' => $documentName,
          'description' => $description,
          'path' => $filePath,
        ]);

        $document->save();
        Log::info('Document saved successfully');
      }
    } else {
      Log::error('No file found in the request');
    }

    return redirect()->back()->with('success', 'Dosyalar başarıyla yüklendi.');
  }

    public function downloadTemplate($uuid)
    {
        $category = DocumentCategory::where('uuid', $uuid)->firstOrFail();

        if (!$category->template_file) {
            return redirect()->back()->with('error', 'Şablon dosyası bulunamadı.');
        }

        $filePath = Storage::disk('public')->path($category->template_file);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Şablon dosyası bulunamadı. Dosya yolu: ' . $filePath);
        }

        return response()->download($filePath);
    }
    
	public function showDocumentStatus(Request $request)
	{
    $userId = $request->input('user_id');
    $year = $request->input('year', now()->year - 1);

    $users = User::role('customer')->orderBy('name', 'asc')->get();

    $categories = collect();

    if ($userId) {
        $categories = DocumentCategory::withCount([
            'documents as approved_count' => function ($q) use ($userId, $year) {
                $q->where('user_id', $userId)
                  ->where('status', 1)
                  ->where('file_year', $year);
            },
            'documents as rejected_count' => function ($q) use ($userId, $year) {
                $q->where('user_id', $userId)
                  ->where('status', 0)
                  ->where('file_year', $year);
            },
            'documents as pending_count' => function ($q) use ($userId, $year) {
                $q->where('user_id', $userId)
                  ->where('status', 2)
                  ->where('file_year', $year);
            },
            'documents as total_uploaded' => function ($q) use ($userId, $year) {
                $q->where('user_id', $userId)
                  ->where('file_year', $year);
            },
        ])
        ->with([
            'documents' => function ($q) use ($userId, $year) {
                $q->where('user_id', $userId)
                  ->where('file_year', $year);
            },
            'notes' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }
        ])
        ->get();
    }

    return view('panel.document.status-overview', compact('users', 'categories', 'userId', 'year'));
	}

	public function updateCategoryNote(Request $request)
	{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'category_id' => 'required|exists:document_categories,id',
        'note' => 'nullable|string|max:1000',
    ]);

    DocumentCategoryNote::updateOrCreate(
        [
            'user_id' => $request->input('user_id'),
            'document_category_id' => $request->input('category_id'),
        ],
        [
            'note' => $request->input('note'),
        ]
    );

    return back()->with('success', 'Not kaydedildi.');
	}

	public function showDocumentStatusForUser(Request $request)
{
    $user = auth()->user();
    $year = $request->input('year', now()->year - 1);

    $categories = DocumentCategory::withCount([
        'documents as approved_count' => function ($q) use ($user, $year) {
            $q->where('user_id', $user->id)->where('status', 1)->where('file_year', $year);
        },
        'documents as rejected_count' => function ($q) use ($user, $year) {
            $q->where('user_id', $user->id)->where('status', 0)->where('file_year', $year);
        },
        'documents as pending_count' => function ($q) use ($user, $year) {
            $q->where('user_id', $user->id)->where('status', 2)->where('file_year', $year);
        },
        'documents as total_uploaded' => function ($q) use ($user, $year) {
            $q->where('user_id', $user->id)->where('file_year', $year);
        },
    ])
    ->with([
        'documents' => function ($q) use ($user, $year) {
            $q->where('user_id', $user->id)->where('file_year', $year);
        },
        'notes' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }
    ])
    ->get();

    return view('panel.document.user-status', compact('categories', 'year'));
}
	
public function destroy(string $uuid)
{
    $document = Document::where('uuid', $uuid)->firstOrFail();

    $category = $document->category;
    $user = $document->user;

    $document->delete(); // Soft delete

    $this->logActivity(
        'Silindi',
        $category?->name,
        "{$user->company} firması {$category?->name} evrakına belge silindi.",
        $user->company,
        now()
    );

    return redirect()->back()->with('success', 'Evrak başarıyla silindi.');
}

	
	 public function assignmentShow()
    {
        $users = User::role('customer')->get(); // sadece müşterileri al
        $categories = DocumentCategory::all();

        return view('panel.assignments.create', compact('users', 'categories'));
    }
	
	 public function assignmentIndex(Request $request)
    {
        $query = DocumentAssignment::query()->with(['user', 'category']);

    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    $assignments = $query->latest()->paginate(10);

    $users = User::role('customer')->get();
    $categories = DocumentCategory::all();

    return view('panel.assignments.index', compact('assignments', 'users', 'categories'));
    }
	
	
	public function assignmentStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
		
		    $user = User::findOrFail($request->user_id);

     $assignment =   DocumentAssignment::create([
            'user_id' => $request->user_id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);
		
    
    	Mail::to($user->email)->queue(new TaskAssignedMail($user, $assignment));
			Log::info('Mail gönderimi tamamlandı');


        return redirect()->back()->with('success', 'Görev başarıyla atandı.');
    }

	public function myAssignments()
{
    $user = auth()->user();

    $assignments = \App\Models\DocumentAssignment::with('category', 'document')
        ->where('user_id', $user->id)
        ->latest()
        ->paginate(10);

    return view('panel.assignments.myAssignments', compact('assignments'));
}
	
	public function assignmentDestroy($id)
{
    $assignment = DocumentAssignment::findOrFail($id);
    $assignment->delete();

    return redirect()->route('assignments.index')->with('success', 'Görev başarıyla silindi.');
}
	
	public function generalNote(Request $request)
{
    $query = User::query()->role('Customer'); // Sadece "Customer" rolündekiler

    if ($search = $request->get('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $users = $query->paginate(10)->withQueryString(); // Sayfalama + query string koruma

    return view('panel.general-note.index', compact('users'));
}

// Admin kategori notunu JSON dosyasına kaydet (general-note için)
public function saveGeneralNoteCategoryNote(Request $request, User $user)
{
    $request->validate([
        'category_id' => 'required|exists:document_categories,id',
        'year' => 'required|digits:4',
        'note' => 'nullable|string|max:1000',
    ]);

    $adminId = Auth::id();
    $year = $request->input('year');
    $categoryId = $request->input('category_id');
    $note = $request->input('note');

    // Dosya yolu
    $fileName = "admin_category_notes/admin_{$adminId}/user_{$user->id}_year_{$year}.json";
    
    // Mevcut dosyayı oku (varsa)
    $notes = [];
    if (Storage::exists($fileName)) {
        $content = Storage::get($fileName);
        $notes = json_decode($content, true) ?? [];
    }

    // Kategori notunu güncelle/ekle (mevcut auditor_note'u koru)
    $existingData = $notes[$categoryId] ?? [];
    $notes[$categoryId] = array_merge($existingData, [
        'category_id' => (int)$categoryId,
        'note' => $note,
        'updated_at' => now()->toDateTimeString()
    ]);

    // Dosyaya kaydet
    Storage::put($fileName, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return response()->json([
        'success' => true,
        'message' => 'Not kaydedildi.'
    ]);
}

// Denetçi notunu JSON dosyasına kaydet
public function saveAuditorNote(Request $request, User $user)
{
    $request->validate([
        'category_id' => 'required|exists:document_categories,id',
        'year' => 'required|digits:4',
        'auditor_note' => 'nullable|string|max:1000',
    ]);

    $adminId = Auth::id();
    $year = $request->input('year');
    $categoryId = $request->input('category_id');
    $auditorNote = $request->input('auditor_note');

    // Dosya yolu
    $fileName = "admin_category_notes/admin_{$adminId}/user_{$user->id}_year_{$year}.json";
    
    // Mevcut dosyayı oku (varsa)
    $notes = [];
    if (Storage::exists($fileName)) {
        $content = Storage::get($fileName);
        $notes = json_decode($content, true) ?? [];
    }

    // Denetçi notunu güncelle/ekle (mevcut note'u koru)
    $existingData = $notes[$categoryId] ?? [];
    $notes[$categoryId] = array_merge($existingData, [
        'category_id' => (int)$categoryId,
        'auditor_note' => $auditorNote,
        'auditor_note_updated_at' => now()->toDateTimeString()
    ]);

    // Dosyaya kaydet
    Storage::put($fileName, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return response()->json([
        'success' => true,
        'message' => 'Denetçi notu kaydedildi.'
    ]);
}

// Admin kategori notlarını JSON dosyasından oku
private function getAdminCategoryNotes(User $user, $year)
{
    $adminId = Auth::id();
    $fileName = "admin_category_notes/admin_{$adminId}/user_{$user->id}_year_{$year}.json";
    
    if (Storage::exists($fileName)) {
        $content = Storage::get($fileName);
        return json_decode($content, true) ?? [];
    }
    
    return [];
}

// generalNoteShow metodunu güncelleyin
public function generalNoteShow(Request $request, User $user)
{
    $year = $request->input('year', now()->year - 1);
    $adminNotes = $this->getAdminCategoryNotes($user, $year);

    $categories = DocumentCategory::withCount([
        'documents as approved_count' => fn($q) => $q->where('status', 1)->where('file_year', $year)->where('user_id', $user->id),
        'documents as rejected_count' => fn($q) => $q->where('status', 0)->where('file_year', $year)->where('user_id', $user->id),
        'documents as pending_count' => fn($q) => $q->where('status', 2)->where('file_year', $year)->where('user_id', $user->id),
    ])
    ->orderBy('order')
    ->get();

    // Her kategori için indirme durumunu kontrol et
    foreach ($categories as $category) {
        // Bu kategoriye ait belgelerin ID'lerini al
        $documentIds = Document::where('category_id', $category->id)
            ->where('user_id', $user->id)
            ->where('file_year', $year)
            ->pluck('id');

        if ($documentIds->isNotEmpty()) {
            // Bu belgelerden herhangi birinin indirme kaydı var mı?
            $lastDownloadLog = DocumentLog::whereIn('document_id', $documentIds)
                ->where('action', 'download')
                ->with('performedBy:id,name')
                ->latest('created_at')
                ->first();

            $category->last_download_log = $lastDownloadLog;
            $category->has_download = $lastDownloadLog !== null;

            // Onaylı belgelerden birinin onaylayan kişisini bul
            $approvedDocumentIds = Document::where('category_id', $category->id)
                ->where('user_id', $user->id)
                ->where('file_year', $year)
                ->where('status', 1) // Sadece onaylı belgeler
                ->pluck('id');

            if ($approvedDocumentIds->isNotEmpty()) {
                $approveLog = DocumentLog::whereIn('document_id', $approvedDocumentIds)
                    ->where('action', 'approve')
                    ->with('performedBy:id,name')
                    ->latest('created_at')
                    ->first();

                $category->approve_log = $approveLog;
                $category->has_approved = $approveLog !== null;
            } else {
                $category->approve_log = null;
                $category->has_approved = false;
            }
        } else {
            $category->last_download_log = null;
            $category->has_download = false;
            $category->approve_log = null;
            $category->has_approved = false;
        }

        // Denetçi notunu JSON'dan al
        $category->auditor_note = $adminNotes[$category->id]['auditor_note'] ?? null;
    }

    $note = $user->generalNotes()->where('year', $year)->first();
    
    return view('panel.general-note.show', compact('user', 'categories', 'year', 'note', 'adminNotes'));
}

public function exportGeneralNotePdf(Request $request, User $user)
{
  $year = $request->input('year', now()->year - 1);    
    // Admin notlarını JSON dosyasından oku
    $adminNotes = $this->getAdminCategoryNotes($user, $year);
    
    $categories = DocumentCategory::withCount([
        'documents as approved_count' => fn($q) => $q->where('status', 1)->where('file_year', $year)->where('user_id', $user->id),
        'documents as rejected_count' => fn($q) => $q->where('status', 0)->where('file_year', $year)->where('user_id', $user->id),
        'documents as pending_count' => fn($q) => $q->where('status', 2)->where('file_year', $year)->where('user_id', $user->id),
    ])
    ->orderBy('order')
    ->get();

    $note = $user->generalNotes()->where('year', $year)->first();

    $pdf = PDF::loadView('panel.general-note.export-pdf', compact('user', 'categories', 'year', 'note', 'adminNotes'));
    
    // Türkçe karakter desteği için ayarlar
    $pdf->setPaper('a4', 'portrait');
    $pdf->setOption('defaultFont', 'DejaVu Sans');
    $pdf->setOption('isHtml5ParserEnabled', true);
    $pdf->setOption('isRemoteEnabled', true);
    $pdf->setOption('encoding', 'UTF-8');
    
    return $pdf->download("{$user->name}_{$year}_Belgeler.pdf");
}



// Mail Gönderme
public function sendGeneralNoteMail(Request $request, User $user)
{
    $request->validate([
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    $year = $request->get('year', now()->year - 1);
    
    try {
        Mail::to($user->email)->send(new GeneralNoteReminderMail($user, $year, $request->subject, $request->message));
        
        return redirect()->back()->with('success', 'Mail başarıyla gönderildi.');
    } catch (\Exception $e) {
        Log::error('Mail gönderme hatası: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Mail gönderilirken bir hata oluştu.');
    }
}



    public function generalNoteSaveNote(Request $request, User $user)
{
    $request->validate([
        'note' => 'nullable|string',
        'year' => 'required|digits:4'
    ]);

    $note = UserGeneralNote::updateOrCreate(
        ['user_id' => $user->id, 'year' => $request->year],
        ['note' => $request->note]
    );

    return back()->with('success', 'Genel not başarıyla kaydedildi.');
}

	public function documentCheck(Request $request)
{
    $request->validate([
        'status'        => 'nullable|in:0,1,2',
        'category_id'   => 'nullable|exists:document_categories,id',
        'file_year'     => 'nullable|digits:4',
        'user_id'       => 'nullable|exists:users,id',
        'document_name' => 'nullable|string|max:255',
    ]);

    $q = Document::query()->with(['user:id,uuid,name','category:id,name','lastDownloadLog.performedBy:id,name']);

    // --- Filtreler ---
    if ($request->filled('document_name')) {
        $q->where('document_name', 'like', '%'.$request->document_name.'%');
    }
    if ($request->filled('file_year')) {
        $q->where('file_year', $request->file_year);
    }
    if ($request->filled('status') && $request->status !== '') {
        $q->where('status', (int)$request->status);
    }
    if ($request->filled('category_id')) {
        $q->where('category_id', $request->category_id);
    }
    if ($request->filled('user_id')) {
        $q->where('user_id', $request->user_id);
    }

    // Bekleyenler önce → sonra yeni tarih
   $q->orderByRaw("CASE WHEN status = 2 THEN 0 WHEN status = 1 THEN 1 ELSE 2 END")
  ->orderBy('created_at','desc');

    $documents  = $q->paginate(30)->appends($request->all());
    $categories = DocumentCategory::orderBy('name')->get(['id','name']);
    $users      = User::orderBy('name')->get(['id','name']); // filtre dropdown için

    return view('panel.document.allDocuments', compact('documents','categories','users'));
}
	
}
