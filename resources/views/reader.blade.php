<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $ebook->title }} • FlipBook 3D Reader</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Reader Core Styling -->
    <style>
        :root {
            --reader-bg: #0b0f19;
            --reader-surface: #111827;
            --reader-border: rgba(255, 255, 255, 0.1);
            --reader-text: #f8fafc;
            --reader-muted: #94a3b8;
            --reader-primary: #4f46e5;
            --reader-secondary: #7c3aed;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--reader-bg);
            color: var(--reader-text);
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            user-select: none;
        }

        /* Top Toolbar */
        .reader-toolbar {
            height: 64px;
            background: var(--reader-surface);
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            z-index: 40;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .tool-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tool-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            padding: 0.45rem 0.75rem;
            border-radius: 8px;
            font-size: 0.825rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .tool-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .tool-btn-primary {
            background: var(--reader-primary);
            border-color: var(--reader-primary);
            color: #ffffff;
        }

        .tool-btn-primary:hover {
            background: #4338ca;
        }

        .tool-btn-ai {
            background: linear-gradient(135deg, var(--reader-secondary), var(--reader-primary));
            border-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
        }

        .tool-btn-ai:hover {
            filter: brightness(1.1);
        }

        .page-counter-box {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--reader-border);
            border-radius: 8px;
            padding: 0.25rem 0.6rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .page-input {
            width: 44px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            color: #ffffff;
            text-align: center;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.2rem 0;
            outline: none;
        }

        .page-input:focus {
            border-color: var(--reader-primary);
        }

        /* Viewport Stage */
        .reader-viewport {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, #1e293b 0%, #0b0f19 100%);
        }

        #book-mount-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: center center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            border-radius: 8px;
        }

        /* StPageFlip Canvas Tweaks */
        .st-flip-container {
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            border-radius: 8px;
            overflow: hidden;
        }

        /* Loading Screen */
        .loading-screen {
            position: absolute;
            inset: 0;
            background: rgba(11, 15, 25, 0.95);
            backdrop-filter: blur(8px);
            z-index: 30;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }

        .loader-ring {
            width: 54px;
            height: 54px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--reader-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 1.25rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Drawers */
        .drawer {
            position: absolute;
            top: 64px;
            bottom: 0;
            width: 320px;
            background: var(--reader-surface);
            border-right: 1px solid var(--reader-border);
            z-index: 35;
            transform: translateX(-100%);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 10px 0 25px rgba(0, 0, 0, 0.4);
        }

        .drawer-right {
            right: 0;
            left: auto;
            border-right: none;
            border-left: 1px solid var(--reader-border);
            transform: translateX(100%);
            width: 360px;
        }

        .drawer.open {
            transform: translateX(0);
        }

        .drawer-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        /* Thumbnails */
        .thumbs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .thumb-card {
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
            cursor: pointer;
            position: relative;
            aspect-ratio: 3/4;
            transition: all 0.15s ease;
        }

        .thumb-card:hover {
            border-color: rgba(255, 255, 255, 0.4);
        }

        .thumb-card.active {
            border-color: var(--reader-primary);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.5);
        }

        .thumb-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumb-page-num {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.75);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
        }

        /* Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            z-index: 50;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .modal-card {
            background: #ffffff;
            color: #1e293b;
            border-radius: 16px;
            width: 100%;
            max-width: 820px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 0.5rem 1.75rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
        }

        .modal-tab-btn {
            background: transparent;
            border: 1px solid transparent;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .modal-tab-btn:hover {
            color: #1e293b;
            background: rgba(0, 0, 0, 0.03);
        }

        .modal-tab-btn.active {
            background: #ffffff;
            color: var(--reader-primary);
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .modal-body {
            padding: 1.75rem;
            overflow-y: auto;
            flex: 1;
        }

        @media (max-width: 768px) {
            .reader-toolbar {
                padding: 0 0.5rem;
            }
            .hide-mobile {
                display: none !important;
            }
            .drawer {
                width: 280px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Reader Toolbar -->
    <header class="reader-toolbar">
        <!-- Left: Back & Title -->
        <div class="tool-group">
            <a href="{{ route('ebooks.show', $ebook->slug) }}" class="tool-btn" title="Back to Book Overview">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                <span class="hide-mobile">Details</span>
            </a>

            <div style="margin-left: 0.5rem; border-left: 1px solid var(--reader-border); padding-left: 0.75rem;">
                <div style="font-size: 0.875rem; font-weight: 700; color: #ffffff; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ebook->title }}">
                    {{ $ebook->title }}
                </div>
                <div style="font-size: 0.75rem; color: var(--reader-muted); max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $ebook->author ?: 'Politeknik Besut' }}
                </div>
            </div>
        </div>

        <!-- Center: Navigation Controls -->
        <div class="tool-group">
            <button class="tool-btn" id="btn-prev-page" title="Previous Page (ArrowLeft)">
                <i data-lucide="chevron-left" style="width: 18px; height: 18px;"></i>
            </button>

            <div class="page-counter-box">
                <span>Page</span>
                <input type="number" id="input-current-page" class="page-input" value="1" min="1" max="{{ $ebook->total_pages ?: 1 }}">
                <span id="label-total-pages" style="color: var(--reader-muted);">/ {{ $ebook->total_pages ?: '—' }}</span>
            </div>

            <button class="tool-btn" id="btn-next-page" title="Next Page (ArrowRight)">
                <i data-lucide="chevron-right" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <!-- Right: Zoom, Spread & Drawers -->
        <div class="tool-group">
            <!-- Zoom Controls -->
            <button class="tool-btn hide-mobile" id="btn-zoom-out" title="Zoom Out (-)">
                <i data-lucide="zoom-out" style="width: 16px; height: 16px;"></i>
            </button>
            <button class="tool-btn hide-mobile" id="btn-zoom-fit" title="Reset Zoom">
                100%
            </button>
            <button class="tool-btn hide-mobile" id="btn-zoom-in" title="Zoom In (+)">
                <i data-lucide="zoom-in" style="width: 16px; height: 16px;"></i>
            </button>

            <!-- Spread Toggle -->
            <button class="tool-btn hide-mobile" id="btn-spread-mode" title="Toggle Single / Double Spread">
                📖 2-Page
            </button>

            <!-- Drawers Toggles -->
            <button class="tool-btn" id="btn-toggle-toc" title="Table of Contents">
                <i data-lucide="list" style="width: 17px; height: 17px;"></i>
            </button>

            <button class="tool-btn" id="btn-toggle-thumbs" title="Thumbnails Grid">
                <i data-lucide="layout-grid" style="width: 17px; height: 17px;"></i>
            </button>

            <button class="tool-btn" id="btn-toggle-search" title="In-Text Search">
                <i data-lucide="search" style="width: 17px; height: 17px;"></i>
            </button>

            <button class="tool-btn" id="btn-toggle-bookmarks" title="Bookmarks">
                <i data-lucide="bookmark" style="width: 17px; height: 17px;"></i>
            </button>

            <!-- Interactive Learning Modal Button -->
            <button class="tool-btn tool-btn-ai" id="btn-toggle-interactive" title="Interactive Learning Suite">
                <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                <span class="hide-mobile">AI Learning Suite</span>
            </button>

            <!-- AI Tutor Drawer Button -->
            <button class="tool-btn tool-btn-primary" id="btn-toggle-ai-tutor" title="Aura AI Academic Tutor">
                <i data-lucide="bot" style="width: 16px; height: 16px;"></i>
                <span class="hide-mobile">Aura Tutor</span>
            </button>

            <!-- Fullscreen -->
            <button class="tool-btn hide-mobile" id="btn-fullscreen" title="Toggle Fullscreen">
                <i data-lucide="maximize" style="width: 16px; height: 16px;"></i>
            </button>
        </div>
    </header>

    <!-- Main Viewport Stage -->
    <main class="reader-viewport">
        <!-- FlipBook Stage Container -->
        <div id="book-mount-wrapper"></div>

        <!-- Non-Blocking Vector Rendering Progress Overlay -->
        <div class="loading-screen" id="loading-screen">
            <div class="loader-ring"></div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; margin-bottom: 0.35rem;">
                Opening Politeknik Besut FlipBook
            </h3>
            <p id="render-progress-text" style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 1.5rem; font-family: 'JetBrains Mono', monospace;">
                Rendering vector pages... 0%
            </p>
            <div style="width: 280px; height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 99px; overflow: hidden;">
                <div id="render-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #4f46e5, #7c3aed); transition: width 0.2s ease;"></div>
            </div>
        </div>
    </main>

    <!-- Drawer 1: Table of Contents -->
    <div class="drawer" id="drawer-toc">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="list" style="width: 18px; height: 18px; color: var(--reader-primary);"></i>
                <span>Table of Contents</span>
            </div>
            <button class="tool-btn btn-close-drawer" style="padding: 0.2rem 0.5rem;">✕</button>
        </div>
        <div class="drawer-body" id="toc-list">
            <!-- Rendered by JS -->
        </div>
    </div>

    <!-- Drawer 2: Thumbnails -->
    <div class="drawer" id="drawer-thumbs">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="layout-grid" style="width: 18px; height: 18px; color: var(--reader-primary);"></i>
                <span>Page Thumbnails</span>
            </div>
            <button class="tool-btn btn-close-drawer" style="padding: 0.2rem 0.5rem;">✕</button>
        </div>
        <div class="drawer-body">
            <div class="thumbs-grid" id="thumbs-grid">
                <!-- Rendered by JS -->
            </div>
        </div>
    </div>

    <!-- Drawer 3: In-Text Search -->
    <div class="drawer drawer-right" id="drawer-search">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="search" style="width: 18px; height: 18px; color: var(--reader-primary);"></i>
                <span>Search Document</span>
            </div>
            <button class="tool-btn btn-close-drawer" style="padding: 0.2rem 0.5rem;">✕</button>
        </div>
        <div style="padding: 1rem; border-bottom: 1px solid var(--reader-border);">
            <form id="pdf-search-form" style="display: flex; gap: 0.5rem;">
                <input 
                    type="text" 
                    id="pdf-search-input" 
                    placeholder="Search keywords in text..." 
                    style="flex: 1; background: rgba(255, 255, 255, 0.06); border: 1px solid var(--reader-border); border-radius: 6px; padding: 0.5rem 0.75rem; color: #fff; font-size: 0.85rem; outline: none;"
                >
                <button type="submit" class="tool-btn tool-btn-primary">Find</button>
            </form>
        </div>
        <div class="drawer-body" id="search-results-list">
            <div style="text-align: center; padding: 2rem 1rem; color: var(--reader-muted); font-size: 0.85rem;">
                Type a keyword above to search through the vector text of this textbook.
            </div>
        </div>
    </div>

    <!-- Drawer 4: Bookmarks -->
    <div class="drawer drawer-right" id="drawer-bookmarks">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="bookmark" style="width: 18px; height: 18px; color: var(--reader-primary);"></i>
                <span>Saved Bookmarks</span>
            </div>
            <button class="tool-btn btn-close-drawer" style="padding: 0.2rem 0.5rem;">✕</button>
        </div>
        <div style="padding: 1rem; border-bottom: 1px solid var(--reader-border);">
            <button id="btn-bookmark-current" class="tool-btn tool-btn-primary" style="width: 100%; justify-content: center;">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Bookmark Current Page
            </button>
        </div>
        <div class="drawer-body" id="bookmarks-list">
            <!-- Rendered by JS -->
        </div>
    </div>

    <!-- Drawer 5: Aura AI Study Tutor -->
    <div class="drawer drawer-right" id="drawer-ai-tutor" style="width: 380px;">
        <div class="drawer-header" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(79, 70, 229, 0.2));">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="bot" style="width: 16px; height: 16px;"></i>
                </div>
                <div>
                    <span style="font-weight: 700; color: #fff;">Aura AI Tutor</span>
                    <span id="ai-current-page-badge" style="display: inline-block; font-size: 0.7rem; background: rgba(255, 255, 255, 0.1); padding: 0.1rem 0.4rem; border-radius: 4px; margin-left: 0.4rem;">
                        Page 1
                    </span>
                </div>
            </div>
            <button class="tool-btn btn-close-drawer" style="padding: 0.2rem 0.5rem;">✕</button>
        </div>

        <div class="drawer-body" id="reader-ai-messages" style="display: flex; flex-direction: column;">
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--reader-border); border-radius: 10px; padding: 0.85rem; font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem; color: #cbd5e1;">
                <strong style="color: #fff;">Greetings!</strong> I'm Aura, your Politeknik Besut study partner. Ask me any question about the concepts or formulas on the current page!
            </div>
        </div>

        <div style="padding: 0.75rem; border-top: 1px solid var(--reader-border); background: rgba(0, 0, 0, 0.3);">
            <form id="reader-ai-form" style="display: flex; gap: 0.5rem;">
                <input 
                    type="text" 
                    id="reader-ai-input" 
                    placeholder="Ask about this page..." 
                    style="flex: 1; background: rgba(255, 255, 255, 0.08); border: 1px solid var(--reader-border); border-radius: 6px; padding: 0.5rem 0.75rem; color: #fff; font-size: 0.85rem; outline: none;"
                    required
                >
                <button type="submit" id="reader-ai-submit" class="tool-btn tool-btn-primary">
                    <i data-lucide="send" style="width: 15px; height: 15px;"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Interactive Learning Suite -->
    <div class="modal-overlay" id="modal-interactive">
        <div class="modal-card">
            <!-- Modal Header -->
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(124, 58, 237, 0.1); color: var(--reader-secondary); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">Interactive Learning Suite</h3>
                        <p style="font-size: 0.8rem; color: #64748b;">Curriculum-grade learning tools researched by Google Gemini AI</p>
                    </div>
                </div>
                <button id="btn-close-interactive-modal" class="tool-btn" style="color: #0f172a; border-color: #e2e8f0; background: #f1f5f9;">✕</button>
            </div>

            <!-- Modal Tabs -->
            <div class="modal-tabs">
                <button class="modal-tab-btn active" id="tab-btn-quiz">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Self-Assessment Quiz
                </button>
                <button class="modal-tab-btn" id="tab-btn-flash">
                    <i data-lucide="layers" style="width: 16px; height: 16px;"></i> 3D Flashcards
                </button>
                <button class="modal-tab-btn" id="tab-btn-match">
                    <i data-lucide="gamepad-2" style="width: 16px; height: 16px;"></i> Speed Match Game
                </button>
                <button class="modal-tab-btn" id="tab-btn-video">
                    <i data-lucide="play-circle" style="width: 16px; height: 16px;"></i> Video Lesson
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Pane 1: Quiz -->
                <div id="pane-quiz">
                    <div id="quiz-widget-content"></div>
                </div>

                <!-- Pane 2: Flashcards -->
                <div id="pane-flash" style="display: none;">
                    <div id="flash-widget-content"></div>
                </div>

                <!-- Pane 3: Speed Match Game -->
                <div id="pane-match" style="display: none;">
                    <div id="match-widget-content"></div>
                </div>

                <!-- Pane 4: Video -->
                <div id="pane-video" style="display: none;">
                    <div id="video-widget-content"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Config for Reader Engine -->
    <script>
        window.FLIPBOOK_CONFIG = {
            pdfUrl: "{{ route('ebooks.file', $ebook->slug) }}",
            bookId: {{ $ebook->id }},
            bookSlug: "{{ $ebook->slug }}",
            bookTitle: @json($ebook->title),
            interactiveElements: @json($ebook->interactive_elements ?? []),
        };
    </script>

    <!-- Local Offline-Capable Libraries -->
    <script src="{{ asset('js/pdf.min.js') }}"></script>
    <script src="{{ asset('js/page-flip.browser.js') }}"></script>

    <!-- Reader Application Logic -->
    <script src="{{ asset('js/reader.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</body>
</html>
