<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EbookController extends Controller
{
    /**
     * Display a listing of ebooks in the library.
     */
    public function index(Request $request): View
    {
        $query = Ebook::query();

        if ($search = $request->input('search', $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            // Default show published unless authenticated user specifies all
            if (!auth()->check()) {
                $query->where('status', 'published');
            }
        }

        $ebooks = $query->latest()->paginate(12)->withQueryString();

        return view('library', compact('ebooks', 'search', 'status'));
    }

    /**
     * Show book details page.
     */
    public function show(string $idOrSlug): View
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook) {
            abort(404, 'E-Book not found.');
        }

        return view('ebooks.show', compact('ebook'));
    }

    /**
     * Show the upload form.
     */
    public function create(): View
    {
        return view('ebooks.create');
    }

    /**
     * Store a newly created ebook.
     */
    public function store(Request $request, GeminiService $geminiService): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'pdf' => 'required|file|mimes:pdf|max:102400', // Max 100MB
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'total_pages' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:published,draft,archived',
            'generate_ai' => 'nullable|boolean',
            'text_sample' => 'nullable|string',
        ]);

        $pdfFile = $request->file('pdf');
        $originalFilename = $pdfFile->getClientOriginalName();
        $fileSize = $pdfFile->getSize();

        // Unique PDF file name
        $pdfFileName = Str::uuid() . '.' . $pdfFile->getClientOriginalExtension();
        $pdfPath = $pdfFile->storeAs('ebooks', $pdfFileName, 'public');

        // Optional Cover
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverFile = $request->file('cover');
            $coverFileName = Str::uuid() . '.' . $coverFile->getClientOriginalExtension();
            $coverPath = $coverFile->storeAs('covers', $coverFileName, 'public');
        }

        $slug = Ebook::generateUniqueSlug($validated['title']);
        $totalPages = $validated['total_pages'] ?? 10;

        // Interactive AI suite generation if requested
        $interactiveElements = null;
        if (!empty($validated['generate_ai'])) {
            try {
                $textSample = $validated['text_sample'] ?? '';
                $interactiveElements = $geminiService->generateInteractiveElements($validated['title'], (int) $totalPages, $textSample);
            } catch (\Throwable $e) {
                // Continue gracefully with fallback
            }
        }

        $ebook = Ebook::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'author' => $validated['author'] ?? null,
            'description' => $validated['description'] ?? null,
            'pdf_path' => $pdfPath,
            'cover_path' => $coverPath,
            'original_filename' => $originalFilename,
            'file_size' => $fileSize,
            'total_pages' => $totalPages,
            'status' => $validated['status'] ?? 'published',
            'interactive_elements' => $interactiveElements,
        ]);

        return redirect()->route('ebooks.show', $ebook->slug)->with('success', 'E-Book "' . $ebook->title . '" uploaded successfully!');
    }

    /**
     * Show edit form.
     */
    public function edit(string $idOrSlug): View
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook) {
            abort(404, 'E-Book not found.');
        }

        return view('ebooks.edit', compact('ebook'));
    }

    /**
     * Update existing ebook.
     */
    public function update(Request $request, string $idOrSlug): RedirectResponse
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook) {
            abort(404, 'E-Book not found.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'total_pages' => 'nullable|integer|min:1',
            'status' => 'required|string|in:published,draft,archived',
        ]);

        if ($validated['title'] !== $ebook->title) {
            $ebook->slug = Ebook::generateUniqueSlug($validated['title'], $ebook->id);
            $ebook->title = $validated['title'];
        }

        $ebook->author = $validated['author'] ?? null;
        $ebook->description = $validated['description'] ?? null;
        $ebook->status = $validated['status'];

        if (isset($validated['total_pages'])) {
            $ebook->total_pages = $validated['total_pages'];
        }

        if ($request->hasFile('cover')) {
            if ($ebook->cover_path && Storage::disk('public')->exists($ebook->cover_path)) {
                Storage::disk('public')->delete($ebook->cover_path);
            }
            $coverFile = $request->file('cover');
            $coverFileName = Str::uuid() . '.' . $coverFile->getClientOriginalExtension();
            $ebook->cover_path = $coverFile->storeAs('covers', $coverFileName, 'public');
        }

        $ebook->save();

        return redirect()->route('ebooks.show', $ebook->slug)->with('success', 'E-Book updated successfully.');
    }

    /**
     * Delete ebook and files.
     */
    public function destroy(string $idOrSlug): RedirectResponse
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook) {
            abort(404, 'E-Book not found.');
        }

        $title = $ebook->title;

        if ($ebook->pdf_path && Storage::disk('public')->exists($ebook->pdf_path)) {
            Storage::disk('public')->delete($ebook->pdf_path);
        }

        if ($ebook->cover_path && Storage::disk('public')->exists($ebook->cover_path)) {
            Storage::disk('public')->delete($ebook->cover_path);
        }

        $ebook->delete();

        return redirect()->route('library')->with('info', 'E-Book "' . $title . '" and all associated files were deleted.');
    }

    /**
     * Dedicated 3D FlipBook reader view.
     */
    public function read(string $idOrSlug): View
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook) {
            abort(404, 'E-Book not found.');
        }

        return view('reader', compact('ebook'));
    }

    /**
     * Stream PDF document binary with range support and proper headers.
     */
    public function file(string $idOrSlug)
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook || !$ebook->pdf_path || !Storage::disk('public')->exists($ebook->pdf_path)) {
            abort(404, 'PDF file not found in storage.');
        }

        return Storage::disk('public')->response($ebook->pdf_path, $ebook->original_filename ?? 'document.pdf', [
            'Content-Type' => 'application/pdf',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Expose-Headers' => 'Content-Length, Content-Range, Accept-Ranges',
        ]);
    }

    /**
     * Stream Cover image.
     */
    public function cover(string $idOrSlug)
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook || !$ebook->cover_path || !Storage::disk('public')->exists($ebook->cover_path)) {
            abort(404, 'Cover image not found.');
        }

        return Storage::disk('public')->response($ebook->cover_path, 'cover.jpg', [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
        ]);
    }

    /**
     * On-demand AI Interactive elements generation.
     */
    public function generateAi(Request $request, string $idOrSlug, GeminiService $geminiService): JsonResponse|RedirectResponse
    {
        $ebook = $this->findEbook($idOrSlug);

        if (!$ebook) {
            abort(404, 'E-Book not found.');
        }

        $textSample = $request->input('text_sample', '');
        $totalPages = $ebook->total_pages ?: $request->input('total_pages', 10);

        $elements = $geminiService->generateInteractiveElements($ebook->title, $totalPages, $textSample);

        $ebook->interactive_elements = $elements;
        $ebook->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $elements,
            ]);
        }

        return back()->with('success', 'Google Gemini AI Interactive Suite generated successfully!');
    }

    /**
     * Helper to find ebook by ID or Slug.
     */
    protected function findEbook(string $idOrSlug): ?Ebook
    {
        return is_numeric($idOrSlug)
            ? Ebook::find($idOrSlug)
            : Ebook::where('slug', $idOrSlug)->first();
    }
}
