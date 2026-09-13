@extends('layouts.app')

@section('title', $ebook->title . ' • Politeknik Besut FlipBook')

@section('styles')
<script src="{{ asset('js/pdf.min.js') }}"></script>
<script>
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
    }
</script>
@endsection

@section('content')
<div style="max-width: 1040px; margin: 1.5rem auto 4rem;">
    <!-- Breadcrumb -->
    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--muted); margin-bottom: 1.75rem;">
        <a href="{{ route('library') }}" style="color: var(--primary); font-weight: 600;">Library</a>
        <span>/</span>
        <span style="color: var(--ink); font-weight: 700;">{{ Str::limit($ebook->title, 40) }}</span>
    </div>

    <div class="liquid-card" style="padding: 2.5rem; margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 290px 1fr; gap: 3rem; align-items: start;">
            <!-- Cover Column -->
            <div>
                <div class="book-cover-container" style="box-shadow: 0 20px 40px -10px rgba(0,0,0,0.35);">
                    <div class="book-spine-effect"></div>
                    <div class="book-spine-line"></div>

                    @if($ebook->cover_path)
                        <img src="{{ route('ebooks.cover', $ebook->slug) }}" alt="{{ $ebook->title }}" class="book-cover-img">
                    @else
                        <img 
                            src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='420'><rect width='300' height='420' fill='%231e1b4b'/></svg>"
                            data-pdf-url="{{ route('ebooks.file', $ebook->slug) }}"
                            data-book-id="{{ $ebook->id }}"
                            alt="{{ $ebook->title }}"
                            class="book-cover-img lazy-pdf-cover"
                        >
                        <div class="cover-fallback-content" style="position: absolute; inset: 0; padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; color: #fff; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #0f172a 100%); z-index: 1;">
                            <div>
                                <span class="liquid-pill" style="font-size: 10px; color: #c4b5fd; background: rgba(124, 58, 237, 0.2);">
                                    POLIBESUT E-BOOK
                                </span>
                                <h4 style="font-size: 1.2rem; font-weight: 800; line-height: 1.35; margin-top: 1rem;">
                                    {{ $ebook->title }}
                                </h4>
                            </div>
                            <div style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 1rem;">
                                <span style="font-size: 0.8rem; color: #cbd5e1;">{{ $ebook->author ?: 'Politeknik Besut' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Primary Action Stack -->
                <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.85rem;">
                    <a href="{{ route('ebooks.read', $ebook->slug) }}" class="liquid-btn-primary" style="padding: 0.85rem; font-size: 1rem; width: 100%; border-radius: 16px;">
                        <i data-lucide="book-open" style="width: 20px; height: 20px;"></i> Open 3D FlipBook
                    </a>

                    <a href="{{ route('ebooks.file', $ebook->slug) }}" target="_blank" class="liquid-btn-secondary" style="width: 100%; border-radius: 14px;">
                        <i data-lucide="download" style="width: 16px; height: 16px;"></i> Download Original PDF
                    </a>

                    <a href="{{ route('ai.tutor', ['book' => $ebook->slug]) }}" class="liquid-btn-secondary" style="width: 100%; border-radius: 14px; border-color: rgba(124, 58, 237, 0.3); color: var(--primary);">
                        <i data-lucide="bot" style="width: 16px; height: 16px;"></i> Ask AI Tutor ("Aura")
                    </a>
                </div>
            </div>

            <!-- Details Column -->
            <div>
                <!-- Badges -->
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                    <span class="liquid-pill">
                        ACADEMIC COURSEBOOK
                    </span>
                    <span class="liquid-pill" style="color: {{ $ebook->status === 'published' ? '#059669' : '#d97706' }}; background: {{ $ebook->status === 'published' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; border-color: {{ $ebook->status === 'published' ? 'rgba(16, 185, 129, 0.25)' : 'rgba(245, 158, 11, 0.25)' }};">
                        {{ strtoupper($ebook->status) }}
                    </span>
                </div>

                <h1 style="font-size: 2.15rem; font-weight: 800; color: var(--ink); line-height: 1.25; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    {{ $ebook->title }}
                </h1>

                <p style="font-size: 1.05rem; color: var(--muted); margin-bottom: 1.75rem;">
                    Course Coordinator / Author: <strong style="color: var(--ink);">{{ $ebook->author ?: 'Politeknik Besut Academic Department' }}</strong>
                </p>

                <!-- Spec Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.65); border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 1.75rem;">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Pages</div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: var(--ink); margin-top: 0.15rem;">{{ $ebook->total_pages ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">File Size</div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: var(--ink); margin-top: 0.15rem;">{{ number_format(($ebook->file_size ?: 0) / (1024 * 1024), 2) }} <span style="font-size: 0.85rem;">MB</span></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Format</div>
                        <div style="font-size: 1rem; font-weight: 700; color: var(--ink); margin-top: 0.35rem;">Vector PDF</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Uploaded</div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: var(--ink); margin-top: 0.35rem;">{{ $ebook->created_at ? $ebook->created_at->format('M d, Y') : '—' }}</div>
                    </div>
                </div>

                <!-- Description -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--ink); margin-bottom: 0.5rem;">Syllabus & Course Scope</h3>
                    <p style="color: var(--ink-secondary); line-height: 1.7; font-size: 0.95rem; white-space: pre-line;">
                        {{ $ebook->description ?: 'This textbook contains comprehensive lecture modules, technical problem-solving exercises, and syllabus notes for Politeknik Besut academic coursework.' }}
                    </p>
                </div>

                <!-- AI Interactive Learning Suite Card -->
                <div class="liquid-card" style="padding: 1.5rem; background: linear-gradient(135deg, rgba(124, 58, 237, 0.08) 0%, rgba(99, 102, 241, 0.03) 100%); border-color: rgba(124, 58, 237, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: var(--primary); font-size: 1.05rem;">
                            <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
                            <span>Google Gemini AI Interactive Learning Suite</span>
                        </div>
                        @if(!empty($ebook->interactive_elements))
                            <span class="liquid-pill" style="color: #059669; border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.1);">
                                Ready
                            </span>
                        @endif
                    </div>

                    @if(!empty($ebook->interactive_elements))
                        @php
                            $quizzesCount = 0;
                            $flashcardsCount = 0;
                            $hasVideo = false;
                            foreach($ebook->interactive_elements as $el) {
                                if (($el['type'] ?? '') === 'quiz') $quizzesCount += count($el['data']['questions'] ?? []);
                                if (($el['type'] ?? '') === 'flashcards') $flashcardsCount += count($el['data']['cards'] ?? []);
                                if (($el['type'] ?? '') === 'video') $hasVideo = true;
                            }
                        @endphp
                        <p style="font-size: 0.9rem; color: var(--muted); margin-bottom: 1rem;">
                            Interactive learning widgets researched from this document's text are integrated directly into the 3D flipbook reader:
                        </p>
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <span class="liquid-pill" style="background: #ffffff; color: var(--ink); border-color: var(--border-subtle); padding: 6px 12px; font-size: 12px;">
                                <i data-lucide="check-circle" style="width: 15px; height: 15px; color: #10b981;"></i>
                                {{ $quizzesCount }} Self-Assessment Questions
                            </span>
                            <span class="liquid-pill" style="background: #ffffff; color: var(--ink); border-color: var(--border-subtle); padding: 6px 12px; font-size: 12px;">
                                <i data-lucide="layers" style="width: 15px; height: 15px; color: var(--primary);"></i>
                                {{ $flashcardsCount }} Flashcards & Speed Match
                            </span>
                            @if($hasVideo)
                                <span class="liquid-pill" style="background: #ffffff; color: var(--ink); border-color: var(--border-subtle); padding: 6px 12px; font-size: 12px;">
                                    <i data-lucide="play-circle" style="width: 15px; height: 15px; color: #ec4899;"></i>
                                    Curated Video Lesson
                                </span>
                            @endif
                        </div>
                    @else
                        <p style="font-size: 0.9rem; color: var(--muted); margin-bottom: 1rem;">
                            Extract concepts and formulate curriculum quizzes and flashcard match games using Google Gemini AI.
                        </p>
                        @auth
                            <form method="POST" action="{{ route('ebooks.generate-ai', $ebook->slug) }}">
                                @csrf
                                <button type="submit" class="liquid-btn-primary" style="font-size: 0.825rem; padding: 0.55rem 1rem;">
                                    <i data-lucide="sparkles" style="width: 15px; height: 15px;"></i> Generate Interactive Suite Now
                                </button>
                            </form>
                        @endauth
                    @endif
                </div>

                @auth
                    <!-- Lecturer Controls -->
                    <div style="display: flex; gap: 0.75rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-subtle);">
                        <a href="{{ route('ebooks.edit', $ebook->slug) }}" class="liquid-btn-secondary">
                            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Edit E-Book
                        </a>
                        <form method="POST" action="{{ route('ebooks.destroy', $ebook->slug) }}" onsubmit="event.preventDefault(); confirmDelete(this, '{{ addslashes($ebook->title) }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="liquid-btn-secondary" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                                <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i> Delete E-Book
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const img = document.querySelector('.lazy-pdf-cover');
        if (!img) return;

        const pdfUrl = img.getAttribute('data-pdf-url');
        const bookId = img.getAttribute('data-book-id');
        if (!pdfUrl) return;

        const cached = sessionStorage.getItem(`thumb_${bookId}`);
        if (cached) {
            img.src = cached;
            img.style.opacity = '1';
            hideFallback();
            return;
        }

        try {
            const loadingTask = pdfjsLib.getDocument({ url: pdfUrl });
            const pdf = await loadingTask.promise;
            const page = await pdf.getPage(1);

            const unscaledVp = page.getViewport({ scale: 1 });
            const scale = 500 / unscaledVp.width;
            const viewport = page.getViewport({ scale });

            const canvas = document.createElement('canvas');
            canvas.width = Math.round(viewport.width);
            canvas.height = Math.round(viewport.height);
            const ctx = canvas.getContext('2d', { alpha: false });
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            await page.render({ canvasContext: ctx, viewport: viewport }).promise;

            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            img.src = dataUrl;
            img.style.opacity = '1';
            hideFallback();
            try { sessionStorage.setItem(`thumb_${bookId}`, dataUrl); } catch (e) {}
        } catch (e) {}

        function hideFallback() {
            const fallback = document.querySelector('.cover-fallback-content');
            if (fallback) fallback.style.display = 'none';
        }
    });
</script>
@endsection
