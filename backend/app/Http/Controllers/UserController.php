<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // ← Add this line

class UserController extends Controller
{
    public function index()
    {
        // Correct way — no red error from Intelephense
        $user = Auth::user();

        // Only admins can see all users
        if ($user->role !== 'admin') {
            return response()->json(['users' => []]);
        }

        $users = User::all();

        return response()->json(['users' => $users]);
    }

   // Example in UserController
public function deactivate($id) {
    $user = User::findOrFail($id);
    $user->status = 'inactive';
    $user->save();
    return response()->json(['status' => $user->status]);
}

public function activate($id) {
    $user = User::findOrFail($id);
    $user->status = 'active';
    $user->save();
    return response()->json(['status' => $user->status]);
}


}