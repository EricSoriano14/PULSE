<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\ChatbotResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = $this->normalizeMessage($data['message']);

        // Restricted / admin-only concerns
        if ($this->containsAny($message, [
            'approve',
            'decline',
            'reject',
            'accepted',
            'why was my report rejected',
            'why is my report rejected',
            'admin decision',
            'internal note',
            'internal notes',
            'show other students',
            'other student',
            'other students',
            'all reports',
            'staff only',
            'admin only',
            'change my status',
            'edit my report status',
            'resolve my report',
            'verify my report',
        ])) {
            return response()->json([
                'reply' => $this->officeRedirectReply(),
            ]);
        }

        // Load active chatbot scripts from database
        $responses = ChatbotResponse::where('is_active', true)->get();

        foreach ($responses as $response) {
            $keyword = $this->normalizeMessage($response->keyword);

            if ($keyword !== '' && Str::contains($message, $keyword)) {
                return response()->json([
                    'reply' => $response->reply,
                ]);
            }
        }

        // Default fallback
        return response()->json([
            'reply' => "I can help with using the app, submitting reports, checking report statuses, viewing announcements, inbox messages, and the safety feature. For concerns that require staff action or official decisions, please contact or email your instructor or the appropriate school office for further assistance.",
        ]);
    }

    private function normalizeMessage(string $message): string
    {
        $message = Str::lower(trim($message));
        $message = preg_replace('/\s+/', ' ', $message);

        return $message;
    }

    private function containsAny(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (Str::contains($message, Str::lower($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function officeRedirectReply(): string
    {
        return "Please contact or email your instructor or the appropriate school office for further assistance.";
    }
}