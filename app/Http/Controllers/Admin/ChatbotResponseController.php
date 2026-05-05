<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotResponse;
use Illuminate\Http\Request;

class ChatbotResponseController extends Controller
{
    public function index()
    {
        $responses = ChatbotResponse::latest()->get();

        return view('admin.chatbot.index', compact('responses'));
    }

    public function create()
    {
        return view('admin.chatbot.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'reply' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ChatbotResponse::create([
            'keyword' => trim($data['keyword']),
            'reply' => trim($data['reply']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', 'Chatbot response created successfully.');
    }

    public function edit(ChatbotResponse $chatbotResponse)
    {
        return view('admin.chatbot.edit', compact('chatbotResponse'));
    }

    public function update(Request $request, ChatbotResponse $chatbotResponse)
    {
        $data = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'reply' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $chatbotResponse->update([
            'keyword' => trim($data['keyword']),
            'reply' => trim($data['reply']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', 'Chatbot response updated successfully.');
    }

    public function destroy(ChatbotResponse $chatbotResponse)
    {
        $chatbotResponse->delete();

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', 'Chatbot response deleted successfully.');
    }
}