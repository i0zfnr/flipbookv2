<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AiController extends Controller
{
    /**
     * Dedicated AI Study Room page.
     */
    public function tutorPage(Request $request): View
    {
        $books = Ebook::where('status', 'published')->orderBy('title')->get();
        $selectedBookId = $request->query('book');
        $selectedBook = null;

        if ($selectedBookId) {
            $selectedBook = is_numeric($selectedBookId)
                ? Ebook::find($selectedBookId)
                : Ebook::where('slug', $selectedBookId)->first();
        }

        return view('ai-tutor', compact('books', 'selectedBook'));
    }

    /**
     * Chat with Google Gemini AI Study Tutor ("Aura")
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'history' => 'nullable|array',
            'history.*.role' => 'required|string|in:user,model',
            'history.*.content' => 'required|string',
            'book_title' => 'nullable|string|max:255',
            'current_page' => 'nullable|integer|min:1',
            'page_text' => 'nullable|string|max:8000',
        ]);

        $userMessage = $validated['message'];
        $history = $validated['history'] ?? [];
        $bookTitle = $validated['book_title'] ?? 'Course Textbook';
        $currentPage = $validated['current_page'] ?? null;
        $pageText = $validated['page_text'] ?? null;

        $apiKey = env('GEMINI_API_KEY') ?: config('services.gemini.key');
        $model = env('GEMINI_MODEL') ?: 'gemini-3.6-flash';

        $systemInstruction = <<<PROMPT
You are "Aura", an empathetic, brilliant, and pedagogical AI Academic Tutor for Politeknik Besut e-books.
Your objective is to help polytechnic and university students deeply understand course materials, mathematical equations, engineering theories, and technical concepts.

Current Learning Context:
- Active Textbook: "{$bookTitle}"
PROMPT;

        if ($currentPage) {
            $systemInstruction .= "\n- Current Page Being Read: Page {$currentPage}";
        }
        if (!empty($pageText)) {
            $systemInstruction .= "\n\nExcerpt from current page:\n\"\"\"\n{$pageText}\n\"\"\"";
        }

        $systemInstruction .= "\n\nGuidelines for your responses:\n" .
            "1. Be encouraging, clear, and structured. Break down complex concepts into simple bullet steps.\n" .
            "2. If mathematics or engineering formulas are involved, show the exact formula and a worked example step-by-step.\n" .
            "3. Use clean Markdown formatting (bolding, code blocks, bullet points, headers) for high readability.\n" .
            "4. If the student asks for practice problems, provide a question and offer to check their work.\n" .
            "5. Keep responses direct and engaging.";

        if ($apiKey) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $contents = [];

            // Add history turns
            foreach ($history as $msg) {
                $contents[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'model',
                    'parts' => [
                        ['text' => $msg['content']],
                    ],
                ];
            }

            // Add current message
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $userMessage],
                ],
            ];

            try {
                $payload = [
                    'contents' => $contents,
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 2048,
                    ],
                ];

                $response = Http::withoutVerifying()->timeout(45)->post($endpoint, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                    if ($replyText) {
                        return response()->json([
                            'success' => true,
                            'reply' => $replyText,
                        ]);
                    }
                } else {
                    Log::warning('Gemini Chat API returned non-200: ' . $response->status() . ' ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error('Gemini Chat API connection error: ' . $e->getMessage());
            }
        }

        // Intelligent pedagogical fallback
        return response()->json([
            'success' => true,
            'reply' => "I am analyzing **{$bookTitle}**" . ($currentPage ? " (Page {$currentPage})" : "") . ".\n\n" .
                "Here is a key conceptual tip for your revision:\n" .
                "1. **Identify the Core Definition**: Clarify key variables and technical criteria first.\n" .
                "2. **Step-by-Step Breakdown**: Work through example calculations or theoretical models sequentially.\n" .
                "3. **Verify Edge Cases**: Check whether your result satisfies boundary conditions.\n\n" .
                "How else can I assist with this chapter?",
        ]);
    }
}
