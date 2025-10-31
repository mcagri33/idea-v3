<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $categories = DocumentCategory::orderBy('id','Desc')->paginate(10);
      return view('panel.category.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      return view('panel.category.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
       'template_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx|max:2048', // Dosya doğrulama

      ]);

        $filePath = null;
        if ($request->hasFile('template_file')) {
            $filePath = $request->file('template_file')->store('templates', 'public'); // storage/app/public/templates içine kaydedilir
        }

       DocumentCategory::create([
        'uuid' => Str::uuid(),
        'name' => $request->name,
        'description' => $request->description,
        'template_file' => $filePath, // Dosya yolu kaydedilir
      ]);

      return redirect()->route('castle.category.index')
        ->with('success','Kategori Başarıyla Oluşturuldu!');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid)
    {
      $category = DocumentCategory::where('uuid', $uuid)->first();
        return view('panel.category.edit',compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid)
    {
        $category = DocumentCategory::where('uuid', $uuid)->first();

        if (!$category) {
            return redirect()->route('castle.category.index')
                ->with('error', 'Kategori bulunamadı!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx|max:2048',
        ]);

        if ($request->hasFile('template_file')) {
            if ($category->template_file) {
                Storage::disk('public')->delete($category->template_file);
            }
            $filePath = $request->file('template_file')->store('templates', 'public');
            $category->template_file = $filePath;
        }
        $category->name = $request->name;
        $category->description = $request->description;

        $category->save();

        return redirect()->route('castle.category.index')
            ->with('success', 'Kategori Başarıyla Güncellendi!');
    }

    public function destroy($uuid)
    {
        $category = DocumentCategory::where('uuid', $uuid)->first();

        if (!$category) {
            return redirect()->route('castle.category.index')
                ->with('error', 'Kategori bulunamadı!');
        }

        $category->delete();

        return redirect()->route('castle.category.index')
            ->with('success', 'Kategori başarıyla silindi!');
    }


    public function search(Request $request)
  {
    $searchTerm = $request->input('q');

    $users = DocumentCategory::where('name', 'like', '%'.$searchTerm.'%')
      ->orWhere('name', 'like', '%'.$searchTerm.'%')
      ->paginate(10);

    return view('panel.user.index', compact('users'));
  }

  public function menu()
  {
    $categories = DocumentCategory::orderBy('order', 'asc')->get();

    return view('panel.category.menu', compact('categories'));
  }

  public function updateOrder(Request $request)
  {
    $orderedIds = $request->input('orderedIds');

    if (!$orderedIds || !is_array($orderedIds)) {
      return response()->json(['message' => 'Geçersiz veri!'], 400);
    }

    foreach ($orderedIds as $index => $id) {
      DocumentCategory::where('id', $id)->update(['order' => $index + 1]);
    }

    return response()->json(['message' => 'Kategori sıralaması başarıyla güncellendi!']);
  }


}
