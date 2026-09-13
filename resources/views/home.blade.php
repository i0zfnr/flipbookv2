@extends('layouts.app')

@section('title', 'Politeknik Besut Academic E-Book Platform')

@section('styles')
<script src="{{ asset('js/pdf.min.js') }}"></script>
<script>
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
    }
</script>
@endsection

@section('content')
<!-- Hero Section (Liquid Glass Aesthetic) -->
<section style="padding: 3.5rem 0 3rem; text-align: center; position: relative;">
    <div class="liquid-pill" style="margin-bottom: 1.25rem;">
        <i data-lucide="sparkles" style="width: 14px; height: 14px; color: var(--primary);"></i>
        <span>Politeknik Besut Academic E-Book Platform</span>
    </div>

    <h1 style="font-size: 3.25rem; font-weight: 800; letter-spacing: -0.03em; color: var(--ink); line-height: 1.18; margin-bottom: 1.25rem;">
        Academic E-Books Powered by <br>
        <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Google Gemini AI
        </span> & 3D Physics
    </h1>

    <p style="font-size: 1.15rem; color: var(--muted); max-width: 700px; margin: 0 auto 2.25rem; line-height: 1.6;">
        Immersive vector-sharp digital textbook reading with realistic 3D page curls, curriculum-grade AI self-assessment quizzes, terminology flashcard games, and real-time academic tutoring.
    </p>

    <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <a href="{{ route('library') }}" class="liquid-btn-primary" style="padding: 0.85rem 1.75rem; font-size: 1rem; border-radius: 16px;">
            <i data-lucide="library" style="width: 20px; height: 20px;"></i> Browse Library
        </a>
        <a href="{{ route('ai.tutor') }}" class="liquid-btn-secondary" style="padding: 0.85rem 1.75rem; font-size: 1rem; border-radius: 16px;">
            <i data-lucide="bot" style="width: 20px; height: 20px; color: var(--primary);"></i> AI Study Room
        </a>
        @auth
            <a href="{{ route('ebooks.create') }}" class="liquid-btn-secondary" style="padding: 0.85rem 1.75rem; font-size: 1rem; border-radius: 16px; border-color: rgba(124, 58, 237, 0.4);">
                <i data-lucide="upload-cloud" style="width: 20px; height: 20px; color: var(--primary);"></i> Upload E-Book
            </a>
        @endauth
    </div>
</section>

<!-- Dynamic "Continue Reading" LocalStorage Section -->
<section id="continue-reading-container" style="display: none; margin-bottom: 3.5rem;">
    <div class="liquid-card" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; padding: 1.75rem 2.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 20px 40px -10px rgba(49, 46, 129, 0.45);">
        <div style="display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 54px; height: 54px; border-radius: 16px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.25);">
                <i data-lucide="bookmark" style="width: 28px; height: 28px; color: #c4b5fd;"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #c7d2fe; font-weight: 700;">Resume Reading</div>
                <h3 id="recent-book-title" style="font-size: 1.25rem; font-weight: 800; margin-top: 0.15rem; color: #fff;">Course Textbook</h3>
                <div id="recent-book-progress" style="font-size: 0.85rem; color: #e0e7ff; margin-top: 0.2rem; font-family: var(--font-mono);">Page 1 of 10 (10% completed)</div>
            </div>
        </div>

        <a id="recent-book-link" href="#" class="liquid-btn-primary" style="background: #ffffff; color: var(--primary); font-weight: 700; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <i data-lucide="book-open" style="width: 18px; height: 18px; color: var(--primary);"></i> Resume Reading
        </a>
    </div>
</section>

<!-- Featured E-Books Section -->
<section style="margin-bottom: 4.5rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--ink); letter-spacing: -0.02em;">Featured Academic E-Books</h2>
            <p style="font-size: 0.9rem; color: var(--muted); margin-top: 0.25rem;">Explore course textbooks available in the digital library</p>
        </div>
        <a href="{{ route('library') }}" class="liquid-btn-secondary" style="font-size: 0.825rem;">
            View All ({{ $totalBooks }}) <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>

    @if($recentBooks->count() > 0)
        <div class="books-grid">
            @foreach($recentBooks as $book)
                <div class="liquid-card book-card">
                    <a href="{{ route('ebooks.read', $book->slug) }}" class="book-cover-container group">
                        <div class="book-spine-effect"></div>
                        <div class="book-spine-line"></div>

                        @if($book->cover_path)
                            <img src="{{ route('ebooks.cover', $book->slug) }}" alt="{{ $book->title }}" class="book-cover-img" loading="lazy">
                        @else
                            <img 
                                src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='420'><rect width='300' height='420' fill='%231e1b4b'/></svg>"
                                data-pdf-url="{{ route('ebooks.file', $book->slug) }}"
                                data-book-id="{{ $book->id }}"
                                alt="{{ $book->title }}"
                                class="book-cover-img lazy-pdf-cover"
                                loading="lazy"
                            >
                            <div class="cover-fallback-content" style="position: absolute; inset: 0; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; color: #fff; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #0f172a 100%); z-index: 1;">
                                <div>
                                    <span class="liquid-pill" style="font-size: 9px; padding: 2px 8px; color: #c4b5fd; border-color: rgba(196, 181, 253, 0.3); background: rgba(124, 58, 237, 0.2);">
                                        POLIBESUT E-BOOK
                                    </span>
                                    <h4 style="font-size: 1.05rem; font-weight: 800; line-height: 1.35; margin-top: 0.75rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $book->title }}
                                    </h4>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 0.75rem;">
                                    <span style="font-size: 0.75rem; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;">
                                        {{ $book->author ?: 'Politeknik Besut' }}
                                    </span>
                                    <i data-lucide="book-open" style="width: 16px; height: 16px; color: #a78bfa;"></i>
                                </div>
                            </div>
                        @endif

                        @if(!empty($book->interactive_elements))
                            <div style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); color: #c4b5fd; padding: 0.25rem 0.6rem; border-radius: var(--radius-full); font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 0.35rem; z-index: 12; border: 1px solid rgba(255, 255, 255, 0.15);">
                                <i data-lucide="sparkles" style="width: 11px; height: 11px; color: #a78bfa;"></i>
                                <span>AI Ready</span>
                            </div>
                        @endif

                        <div class="book-read-overlay">
                            <span class="liquid-btn-primary" style="font-size: 0.825rem; padding: 0.6rem 1.15rem;">
                                <i data-lucide="book-open" style="width: 15px; height: 15px;"></i> Read FlipBook
                            </span>
                        </div>
                    </a>

                    <div class="book-card-details">
                        <div class="book-meta-bar" style="margin-top: 0; padding-top: 0; border: none; margin-bottom: 0.4rem;">
                            <span style="display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                                <i data-lucide="file-text" style="width: 13px; height: 13px; color: var(--primary);"></i>
                                {{ $book->total_pages ? "{$book->total_pages} Pages" : 'PDF Document' }}
                            </span>
                            <span>{{ number_format(($book->file_size ?: 0) / (1024 * 1024), 1) }} MB</span>
                        </div>

                        <a href="{{ route('ebooks.show', $book->slug) }}" class="book-card-title" title="{{ $book->title }}">
                            {{ $book->title }}
                        </a>

                        <p class="book-card-author">
                            By {{ $book->author ?: 'Politeknik Besut Academic Department' }}
                        </p>

                        <div style="display: flex; gap: 0.5rem; margin-top: auto; padding-top: 0.75rem;">
                            <a href="{{ route('ebooks.read', $book->slug) }}" class="liquid-btn-primary" style="flex: 1; padding: 0.55rem 0.85rem; font-size: 0.8rem;">
                                <i data-lucide="book-open" style="width: 14px; height: 14px;"></i> Read
                            </a>
                            <a href="{{ route('ebooks.show', $book->slug) }}" class="liquid-btn-secondary" style="padding: 0.55rem 0.75rem;" title="View Textbook Details">
                                <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<!-- Key Platform Innovations -->
<section style="margin-bottom: 4rem;">
    <div style="text-align: center; max-width: 640px; margin: 0 auto 3rem;">
        <h2 style="font-size: 2rem; font-weight: 800; color: var(--ink); letter-spacing: -0.02em;">Engineered for Academic Excellence</h2>
        <p style="font-size: 0.95rem; color: var(--muted); margin-top: 0.5rem;">Everything lecturers and students need for an engaging, distraction-free study experience.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.75rem;">
        <div class="liquid-card" style="padding: 2rem;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(124, 58, 237, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                <i data-lucide="book" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--ink);">Realistic 3D FlipBook</h3>
            <p style="font-size: 0.9rem; color: var(--muted); line-height: 1.6;">
                Natural page turning physics, vector retina sharpness, interactive corner curling, and single or double page spread modes.
            </p>
        </div>

        <div class="liquid-card" style="padding: 2rem;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(236, 72, 153, 0.1); color: #ec4899; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                <i data-lucide="sparkles" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--ink);">Google Gemini AI Suite</h3>
            <p style="font-size: 0.9rem; color: var(--muted); line-height: 1.6;">
                Automatically extracts mathematical concepts and technical terms to generate curriculum quizzes and 3D flashcard study games.
            </p>
        </div>

        <div class="liquid-card" style="padding: 2rem;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(6, 182, 212, 0.1); color: var(--accent); display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                <i data-lucide="bot" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--ink);">Aura AI Academic Tutor</h3>
            <p style="font-size: 0.9rem; color: var(--muted); line-height: 1.6;">
                Context-aware pedagogical assistant inside the reader answering questions, demonstrating equations, and explaining textbook pages.
            </p>
        </div>

        <div class="liquid-card" style="padding: 2rem;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                <i data-lucide="search" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--ink);">Instant Search & Bookmarks</h3>
            <p style="font-size: 0.9rem; color: var(--muted); line-height: 1.6;">
                Full-text PDF keyword search with snippet highlights, automatic Table of Contents, page thumbnails, and saved bookmarks.
            </p>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Check for recent reading progress in localStorage
        try {
            let mostRecent = null;
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith('flipbook_progress_')) {
                    const item = JSON.parse(localStorage.getItem(key));
                    if (!mostRecent || item.lastReadAt > mostRecent.lastReadAt) {
                        mostRecent = item;
                    }
                }
            }

            if (mostRecent && mostRecent.title) {
                const container = document.getElementById('continue-reading-container');
                const titleEl = document.getElementById('recent-book-title');
                const progressEl = document.getElementById('recent-book-progress');
                const linkEl = document.getElementById('recent-book-link');

                titleEl.textContent = mostRecent.title;
                progressEl.textContent = `Page ${mostRecent.currentPage} of ${mostRecent.totalPages} (${mostRecent.percent}% completed)`;
                
                const targetSlug = mostRecent.slug || mostRecent.bookId;
                linkEl.href = `/read/${targetSlug}?page=${mostRecent.currentPage}`;

                container.style.display = 'block';
                if (window.lucide) window.lucide.createIcons();
            }
        } catch (e) {}

        // Automatic First Page PDF Thumbnail Extractor
        const lazyPdfCovers = document.querySelectorAll('.lazy-pdf-cover');
        lazyPdfCovers.forEach(async (img) => {
            const pdfUrl = img.getAttribute('data-pdf-url');
            const bookId = img.getAttribute('data-book-id');
            if (!pdfUrl) return;

            const cachedThumb = sessionStorage.getItem(`thumb_${bookId}`);
            if (cachedThumb) {
                img.src = cachedThumb;
                img.style.opacity = '1';
                hideFallback(img);
                return;
            }

            try {
                const loadingTask = pdfjsLib.getDocument({ url: pdfUrl });
                const pdf = await loadingTask.promise;
                const page = await pdf.getPage(1);

                const unscaledVp = page.getViewport({ scale: 1 });
                const scale = 400 / unscaledVp.width;
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.width = Math.round(viewport.width);
                canvas.height = Math.round(viewport.height);
                const ctx = canvas.getContext('2d', { alpha: false });
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                img.src = dataUrl;
                img.style.opacity = '1';
                hideFallback(img);
                try { sessionStorage.setItem(`thumb_${bookId}`, dataUrl); } catch (e) {}
            } catch (err) {}
        });

        function hideFallback(img) {
            const container = img.closest('.book-cover-container');
            if (container) {
                const fallback = container.querySelector('.cover-fallback-content');
                if (fallback) fallback.style.display = 'none';
            }
        }
    });
</script>
@endsection
