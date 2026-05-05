<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentAuthController extends Controller
{
    // ... your existing login/me methods

    public function logout(Request $request)
    {
        // Revoke only the current token used for this request
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
