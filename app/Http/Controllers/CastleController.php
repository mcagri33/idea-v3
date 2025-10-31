<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\Auth;
class CastleController extends Controller
{
    public function index(Request $request)
    {
      $year = $request->get('year', now()->year);
      $userId = Auth::id();
	  $note = Auth::user()->generalNotes()->where('year', $year)->first();

      $categories = DocumentCategory::withCount([
        'documents as approved_count' => function ($query) use ($year, $userId) {
          $query->where('status', 1)
            ->where('file_year', $year)
            ->where('user_id', $userId);
        },
        'documents as rejected_count' => function ($query) use ($year, $userId) {
          $query->where('status', 0)
            ->where('file_year', $year)
            ->where('user_id', $userId);
        },
        'documents as pending_count' => function ($query) use ($year, $userId) {
          $query->where('status', 2)
            ->where('file_year', $year)
            ->where('user_id', $userId);
        }
      ])->orderBy('order','asc')->get();

      $allApproved = $categories->every(function ($category) {
        return $category->approved_count > 0 &&
          $category->pending_count == 0 &&
          $category->rejected_count == 0;
      });

      return view('content.pages.pages-home',compact('categories', 'allApproved', 'year', 'note'));
    }

  public function login(Request $request)
  {
    // POST [email, password]
    // Validation
    $request->validate([
      'email' => 'required|email|string',
      'password' => 'required'
    ]);

    // Email check
    $user = User::where("email", $request->email)->first();

    if (!empty($user)) {
      // User exists
      if (Hash::check($request->password, $user->password)) {
        // Password matched
        $token = $user->createToken("myAccessToken")->plainTextToken;

        return response()->json([
          "status" => true,
          "message" => "Login successful",
          "token" => $token,
          "data" => []
        ]);
      } else {
        return response()->json([
          "status" => false,
          "message" => "Password didn't match",
          "data" => []
        ]);
      }
    } else {
      return response()->json([
        "status" => false,
        "message" => "Invalid Email value",
        "data" => []
      ]);
    }
  }

  public function profile()
  {
    $userData = auth()->user();

    return response()->json([
      "status" => true,
      "message" => "Profile information",
      "data" => $userData,
      "id" => auth()->user()->id
    ]);
  }

  public function logout()
  {
    auth()->user()->tokens()->delete();

    return response()->json([
      "status" => true,
      "message" => "User Logged out successfully",
      "data" => []
    ]);
  }
}
