<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentSafetyController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $db = (string) ($user?->safety_status ?? 'safe');

        return response()->json([
            'status' => $db === 'at_risk' ? 'not_safe' : 'safe',
            'note' => null,
            'updated_at' => $user?->updated_at?->toISOString() ?? now()->toISOString(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $request->validate([
            'status' => ['required', 'string', 'in:safe,not_safe'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user->safety_status = $data['status'] === 'not_safe' ? 'at_risk' : 'safe';
        $user->save();

        return response()->json([
            'status' => $user->safety_status === 'at_risk' ? 'not_safe' : 'safe',
            'note' => $data['note'] ?? null,
            'updated_at' => $user->updated_at?->toISOString() ?? now()->toISOString(),
        ]);
    }
}