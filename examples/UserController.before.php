<?php

// INTENTIONALLY VULNERABLE: the "before" version reviewed in docs/code-review.md.
// Do not use.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function update(Request $request, $id)
    {
        $user = User::find($id);                       // null → 500 on missing ID
        $user->update($request->all());                // mass assignment, no authorization

        $orders = DB::select("SELECT * FROM orders WHERE user_id = $id"); // SQL injection, unbounded

        return response()->json(['user' => $user, 'orders' => $orders]);  // leaks internal fields
    }
}
