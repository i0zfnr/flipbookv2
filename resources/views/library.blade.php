@extends('layouts.app')

@section('title', 'Academic E-Book Library • Politeknik Besut')

@section('styles')
<!-- PDF.js for automatic first-page cover generation -->
<script src="{{ asset('js/pdf.min.js') }}"></script>
<script>
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
    }
</script>
@endsection

@section('content')
<div style="margin-bottom: 3.5rem;">
    <!-- Page Header & Main Action -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem; margin-bottom: 2rem;">
        <div>
            <div class="liquid-pill" style="margin-bottom: 0.5rem;">
                <i data-lucide="sparkles" style="width: 12px; height: 12px;"></i>
                <span>Curated Academic Repository</span>
            </div>
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--ink); letter-spacing: -0.025em; line-height: 1.2;">
                Academic E-Book Library
            </h1>
            <p style="color: var(--muted); font-size: 0.95rem; margin-top: 0.35rem;">
                Official course textbooks, modules, and interactive syllabi for Politeknik Besut
            </p>
        </div>

        @auth
            <a href="{{ route('ebooks.create') }}" class="liquid-btn-primary" style="padding: 0.75rem 1.4rem; font-size: 0.925rem;">
                <i data-lucide="upload-cloud" style="width: 18px; height: 18px;"></i> Upload New E-Book
            </a>
        @endauth
    </div>

    <!-- Search & Filter Card (Liquid Glass Floating Panel) -->
    <div class="liquid-card search-filter-card" style="margin-bottom: 2.5rem;">
        <form method="GET" action="{{ route('library') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <!-- Search Input -->
            <div style="flex: 1; min-width: 280px; position: relative;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search ?? '' }}" 
                    class="liquid-input" 
                    placeholder="Search by course code, textbook title, or author..."
                    style="padding-left: 2.75rem;"
                >
                <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--muted);"></i>
            </div>

            <!-- Status Filter -->
            @auth
                <div style="min-width: 170px;">
                    <select name="status" class="liquid-input" style="padding-right: 2rem; cursor: pointer;">
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="published" {{ ($status ?? '') === 'published' ? 'selected' : '' }}>Published Only</option>
                        <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Drafts Only</option>
                    </select>
                </div>
            @endauth

            <!-- Search Action Button -->
            <button type="submit" class="liquid-btn-primary" style="padding: 0.75rem 1.5rem;">
                <i data-lucide="filter" style="width: 16px; height: 16px;"></i> Search
            </button>

            @if(!empty($search) || !empty($status))
                <a href="{{ route('library') }}" class="liquid-btn-secondary" title="Reset Search Filters">
                    <i data-lucide="rotate-ccw" style="width: 16px; height: 16px;"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Books Grid with 3D Spine and Dynamic Vector Cover Art -->
    @if($ebooks->count() > 0)
        <div class="books-grid">
            @foreach($ebooks as $book)
                <div class="liquid-card book-card">
                    <!-- Book Cover Wrap with 3D Spine -->
                    <a href="{{ route('ebooks.read', $book->slug) }}" class="book-cover-container group">
                        <!-- 3D Book Spine Shadow -->
                        <div class="book-spine-effect"></div>
                        <div class="book-spine-line"></div>

                        <!-- Cover Image with Dynamic Client-Side Page 1 Rendering -->
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
                            <!-- Fallback Artistic Header in case JS is disabled -->
                            <div class="cover-fallback-content" style="position: absolute; inset: 0; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; color: #fff; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #0f172a 100%); z-index: 1;">
                                <div>
                                    <span class="liquid-pill" style="font-size: 9px; padding: 2px 8px; color: #c4b5fd; border-color: rgba(196, 181, 253, 0.3); background: rgba(124, 58, 237, 0.2);">
                                        POLIBESUT E-BOOK
                                    </span>
                                    <h4 style="font-size: 1.05rem; font-weight: 800; line-height: 1.35; margin-top: 0.75rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
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

                        <!-- AI Interactive Badge -->
                        @if(!empty($book->interactive_elements))
                            <div style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); color: #c4b5fd; padding: 0.25rem 0.6rem; border-radius: var(--radius-full); font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 0.35rem; z-index: 12; border: 1px solid rgba(255, 255, 255, 0.15);">
                                <i data-lucide="sparkles" style="width: 11px; height: 11px; color: #a78bfa;"></i>
                                <span>AI Ready</span>
                            </div>
                        @endif

                        <!-- Draft Badge -->
                        @if($book->status === 'draft')
                            <div style="position: absolute; top: 0.75rem; left: 1.25rem; background: #f59e0b; color: #ffffff; padding: 0.2rem 0.55rem; border-radius: 6px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; z-index: 12;">
                                Draft
                            </div>
                        @endif

                        <!-- Hover Read Overlay -->
                        <div class="book-read-overlay">
                            <span class="liquid-btn-primary" style="font-size: 0.825rem; padding: 0.6rem 1.15rem;">
                                <i data-lucide="book-open" style="width: 15px; height: 15px;"></i> Open FlipBook
                            </span>
                        </div>
                    </a>

                    <!-- Details Area -->
                    <div class="book-card-details">
                        <!-- Pages & Size Meta -->
                        <div class="book-meta-bar" style="margin-top: 0; padding-top: 0; border: none; margin-bottom: 0.4rem;">
                            <span style="display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                                <i data-lucide="file-text" style="width: 13px; height: 13px; color: var(--primary);"></i>
                                {{ $book->total_pages ? "{$book->total_pages} Pages" : 'PDF Document' }}
                            </span>
                            <span>{{ number_format(($book->file_size ?: 0) / (1024 * 1024), 1) }} MB</span>
                        </div>

                        <!-- Title -->
                        <a href="{{ route('ebooks.show', $book->slug) }}" class="book-card-title" title="{{ $book->title }}">
                            {{ $book->title }}
                        </a>

                        <!-- Author -->
                        <p class="book-card-author">
                            By {{ $book->author ?: 'Politeknik Besut Academic Department' }}
                        </p>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 0.5rem; margin-top: auto; padding-top: 0.75rem;">
                            <a href="{{ route('ebooks.read', $book->slug) }}" class="liquid-btn-primary" style="flex: 1; padding: 0.55rem 0.85rem; font-size: 0.8rem;">
                                <i data-lucide="book-open" style="width: 14px; height: 14px;"></i> Read
                            </a>
                            <a href="{{ route('ebooks.show', $book->slug) }}" class="liquid-btn-secondary" style="padding: 0.55rem 0.75rem;" title="View Textbook Details">
                                <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                            </a>
                            @auth
                                <a href="{{ route('ebooks.edit', $book->slug) }}" class="liquid-btn-secondary" style="padding: 0.55rem 0.75rem;" title="Edit E-Book">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form method="POST" action="{{ route('ebooks.destroy', $book->slug) }}" onsubmit="event.preventDefault(); confirmDelete(this, '{{ addslashes($book->title) }}');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="liquid-btn-secondary" style="padding: 0.55rem 0.75rem; color: #ef4444;" title="Delete E-Book">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 3rem; display: flex; justify-content: center;">
            {{ $ebooks->links() }}
        </div>
    @else
        <div class="liquid-card" style="padding: 4.5rem 2rem; text-align: center; max-width: 600px; margin: 2rem auto;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.25rem;">
                <i data-lucide="book-x" style="width: 32px; height: 32px;"></i>
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--ink);">No e-books match your search</h3>
            <p style="color: var(--muted); font-size: 0.925rem; margin-top: 0.5rem;">Try using another course title, lecturer name, or reset your filters.</p>
            <div style="margin-top: 1.5rem;">
                <a href="{{ route('library') }}" class="liquid-btn-secondary">Clear Search Filters</a>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Automatic First Page PDF Thumbnail Extractor
    document.addEventListener('DOMContentLoaded', function () {
        const lazyPdfCovers = document.querySelectorAll('.lazy-pdf-cover');

        lazyPdfCovers.forEach(async (img) => {
            const pdfUrl = img.getAttribute('data-pdf-url');
            const bookId = img.getAttribute('data-book-id');
            if (!pdfUrl) return;

            // Check SessionStorage Cache
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
                const targetWidth = 400;
                const scale = targetWidth / unscaledVp.width;
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.width = Math.round(viewport.width);
                canvas.height = Math.round(viewport.height);
                const ctx = canvas.getContext('2d', { alpha: false });

                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                await page.render({
                    canvasContext: ctx,
                    viewport: viewport,
                }).promise;

                const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                img.src = dataUrl;
                img.style.opacity = '1';
                hideFallback(img);

                try {
                    sessionStorage.setItem(`thumb_${bookId}`, dataUrl);
                } catch (e) {}
            } catch (err) {
                console.warn('Could not extract PDF first page cover for book #' + bookId, err);
            }
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
