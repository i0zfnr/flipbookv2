/**
 * Politeknik Besut Academic FlipBook Reader Engine
 * Powered by StPageFlip (page-flip) and PDF.js
 */

(function () {
    'use strict';

    // Global Reader State
    const state = {
        pdfUrl: window.FLIPBOOK_CONFIG.pdfUrl,
        bookId: window.FLIPBOOK_CONFIG.bookId,
        bookSlug: window.FLIPBOOK_CONFIG.bookSlug,
        bookTitle: window.FLIPBOOK_CONFIG.bookTitle,
        interactiveElements: window.FLIPBOOK_CONFIG.interactiveElements || [],
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

        pdfDoc: null,
        pageFlip: null,
        totalPages: 0,
        currentPage: 1,
        zoom: 1,
        spreadMode: 'auto', // 'auto', 'single', 'double'
        isSinglePage: false,
        pageImages: [],
        pageTexts: [],
        outline: [],
        bookmarks: [],
        aiChatHistory: [],
    };

    // DOM Elements
    const elements = {
        mountEl: document.getElementById('book-mount-wrapper'),
        loadingScreen: document.getElementById('loading-screen'),
        renderProgressText: document.getElementById('render-progress-text'),
        renderProgressBar: document.getElementById('render-progress-bar'),

        // Toolbar
        btnPrev: document.getElementById('btn-prev-page'),
        btnNext: document.getElementById('btn-next-page'),
        inputPage: document.getElementById('input-current-page'),
        totalPageLabel: document.getElementById('label-total-pages'),
        btnZoomIn: document.getElementById('btn-zoom-in'),
        btnZoomOut: document.getElementById('btn-zoom-out'),
        btnZoomFit: document.getElementById('btn-zoom-fit'),
        btnSpread: document.getElementById('btn-spread-mode'),
        btnFullscreen: document.getElementById('btn-fullscreen'),

        // Drawers & Modals
        btnToggleToc: document.getElementById('btn-toggle-toc'),
        btnToggleThumbs: document.getElementById('btn-toggle-thumbs'),
        btnToggleSearch: document.getElementById('btn-toggle-search'),
        btnToggleBookmarks: document.getElementById('btn-toggle-bookmarks'),
        btnToggleInteractive: document.getElementById('btn-toggle-interactive'),
        btnToggleAiTutor: document.getElementById('btn-toggle-ai-tutor'),

        drawerToc: document.getElementById('drawer-toc'),
        drawerThumbs: document.getElementById('drawer-thumbs'),
        drawerSearch: document.getElementById('drawer-search'),
        drawerBookmarks: document.getElementById('drawer-bookmarks'),
        drawerAiTutor: document.getElementById('drawer-ai-tutor'),
        modalInteractive: document.getElementById('modal-interactive'),

        tocList: document.getElementById('toc-list'),
        thumbsGrid: document.getElementById('thumbs-grid'),
        searchInput: document.getElementById('pdf-search-input'),
        searchForm: document.getElementById('pdf-search-form'),
        searchResults: document.getElementById('search-results-list'),
        bookmarksList: document.getElementById('bookmarks-list'),
        btnBookmarkCurrent: document.getElementById('btn-bookmark-current'),

        // AI Tutor Chat
        aiChatMessages: document.getElementById('reader-ai-messages'),
        aiChatForm: document.getElementById('reader-ai-form'),
        aiChatInput: document.getElementById('reader-ai-input'),
        aiChatSubmit: document.getElementById('reader-ai-submit'),
        aiCurrentPageBadge: document.getElementById('ai-current-page-badge'),
    };

    // 1. Initial Entry Point
    window.addEventListener('DOMContentLoaded', async () => {
        initBookmarks();
        setupEventListeners();
        await loadAndRenderPdf();
    });

    // 2. Load PDF & Render Vector Pages to ObjectURLs
    async function loadAndRenderPdf() {
        try {
            // Configure PDF.js worker
            pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.min.js';

            const loadingTask = pdfjsLib.getDocument({
                url: state.pdfUrl,
                cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
                cMapPacked: true,
            });

            state.pdfDoc = await loadingTask.promise;
            state.totalPages = state.pdfDoc.numPages;
            elements.totalPageLabel.textContent = `/ ${state.totalPages}`;
            elements.inputPage.max = state.totalPages;

            // Extract initial page ratio
            const firstPage = await state.pdfDoc.getPage(1);
            const vp = firstPage.getViewport({ scale: 1 });
            const pageAspectRatio = vp.width / vp.height;

            // Render all pages sequentially with progress update
            state.pageImages = [];
            state.pageTexts = [];

            const targetWidth = 1400; // Retina crisp width
            for (let p = 1; p <= state.totalPages; p++) {
                const imgUrl = await renderPageToBlob(p, targetWidth);
                state.pageImages.push(imgUrl);

                // Extract text for search & AI context
                extractPageText(p);

                const percent = Math.round((p / state.totalPages) * 100);
                elements.renderProgressBar.style.width = `${percent}%`;
                elements.renderProgressText.textContent = `Rendering high-resolution vector pages... ${percent}% (${p}/${state.totalPages})`;

                // Yield frame to UI
                await new Promise(r => setTimeout(r, 10));
            }

            // Hide loading screen
            elements.loadingScreen.style.display = 'none';

            // Check initial page from URL query
            const urlParams = new URLSearchParams(window.location.search);
            const startPage = parseInt(urlParams.get('page')) || getSavedPageProgress() || 1;
            state.currentPage = Math.min(Math.max(1, startPage), state.totalPages);

            // Mount StPageFlip
            mountPageFlip(pageAspectRatio);

            // Render Drawers
            renderTableOfContents();
            renderThumbnails();
            renderBookmarksList();
            setupInteractiveModal();
        } catch (err) {
            console.error('PDF load error:', err);
            elements.renderProgressText.innerHTML = `<span style="color:#ef4444;">Failed to load PDF document. Please ensure file exists in storage.</span>`;
        }
    }

    // Render individual page to Image Blob
    async function renderPageToBlob(pageNumber, targetWidth) {
        const page = await state.pdfDoc.getPage(pageNumber);
        const unscaledViewport = page.getViewport({ scale: 1 });
        const scale = Math.min(Math.max(targetWidth / unscaledViewport.width, 1.5), 2.2);
        const viewport = page.getViewport({ scale });

        const canvas = document.createElement('canvas');
        canvas.width = Math.round(viewport.width);
        canvas.height = Math.round(viewport.height);
        const ctx = canvas.getContext('2d', { alpha: false });

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        await page.render({
            canvasContext: ctx,
            viewport: viewport,
        }).promise;

        return new Promise(resolve => {
            canvas.toBlob(blob => {
                resolve(URL.createObjectURL(blob));
            }, 'image/jpeg', 0.92);
        });
    }

    // Extract text in background
    async function extractPageText(pageNumber) {
        try {
            const page = await state.pdfDoc.getPage(pageNumber);
            const content = await page.getTextContent();
            const text = content.items.map(i => i.str).join(' ');
            state.pageTexts[pageNumber - 1] = text;
        } catch (e) {
            state.pageTexts[pageNumber - 1] = '';
        }
    }

    // 3. Mount PageFlip Engine
    function mountPageFlip(aspectRatio) {
        if (state.pageFlip) {
            try { state.pageFlip.destroy(); } catch (e) {}
        }

        elements.mountEl.innerHTML = '';
        const bookDiv = document.createElement('div');
        bookDiv.className = 'st-flip-container';
        elements.mountEl.appendChild(bookDiv);

        // Compute optimal dimensions
        const viewportW = window.innerWidth;
        const viewportH = window.innerHeight - 70; // Header offset
        const isMobile = viewportW < 768;

        const forceSingle = state.spreadMode === 'single' || (state.spreadMode === 'auto' && isMobile);
        state.isSinglePage = forceSingle;

        const maxH = Math.max(viewportH - 60, 350);
        const maxW = Math.max(viewportW - 60, 300);

        let pageW, pageH;
        if (state.isSinglePage) {
            pageH = maxH;
            pageW = pageH * aspectRatio;
            if (pageW > maxW) {
                pageW = maxW;
                pageH = pageW / aspectRatio;
            }
        } else {
            const halfW = maxW / 2;
            pageH = maxH;
            pageW = pageH * aspectRatio;
            if (pageW > halfW) {
                pageW = halfW;
                pageH = pageW / aspectRatio;
            }
        }

        pageW = Math.max(Math.floor(pageW), 220);
        pageH = Math.max(Math.floor(pageH), 320);

        const pageFlip = new St.PageFlip(bookDiv, {
            width: pageW,
            height: pageH,
            size: 'fixed',
            minWidth: 200,
            maxWidth: 2400,
            minHeight: 300,
            maxHeight: 2400,
            showCover: false,
            usePortrait: state.isSinglePage,
            startPage: Math.max(0, state.currentPage - 1),
            drawShadow: true,
            flippingTime: 600,
            useMouseEvents: true,
            swipeDistance: 30,
            maxShadowOpacity: 0.35,
            showPageCorners: true,
            disableFlipByClick: false,
        });

        pageFlip.loadFromImages(state.pageImages);

        pageFlip.on('flip', (e) => {
            const newPage = e.data + 1;
            onPageChange(newPage);
        });

        state.pageFlip = pageFlip;
        onPageChange(state.currentPage);
    }

    // 4. Page Change Handler
    function onPageChange(newPage) {
        state.currentPage = newPage;
        elements.inputPage.value = newPage;

        // Update AI Tutor Current Page indicator
        if (elements.aiCurrentPageBadge) {
            elements.aiCurrentPageBadge.textContent = `Page ${newPage}`;
        }

        // Save progress to LocalStorage
        saveReadingProgress();

        // Update Thumbnail Highlight
        const thumbs = document.querySelectorAll('.thumb-card');
        thumbs.forEach(t => {
            if (parseInt(t.getAttribute('data-page')) === newPage) {
                t.classList.add('active');
                t.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                t.classList.remove('active');
            }
        });

        // Update URL query without full reload
        const url = new URL(window.location);
        url.searchParams.set('page', newPage);
        window.history.replaceState({}, '', url);
    }

    // 5. Reading Progress & Bookmarks Helpers
    function saveReadingProgress() {
        try {
            const key = `flipbook_progress_${state.bookId}`;
            const data = {
                bookId: state.bookId,
                title: state.bookTitle,
                slug: state.bookSlug,
                currentPage: state.currentPage,
                totalPages: state.totalPages,
                percent: Math.round((state.currentPage / state.totalPages) * 100),
                lastReadAt: Date.now(),
            };
            localStorage.setItem(key, JSON.stringify(data));
        } catch (e) {}
    }

    function getSavedPageProgress() {
        try {
            const raw = localStorage.getItem(`flipbook_progress_${state.bookId}`);
            if (raw) {
                const item = JSON.parse(raw);
                return item.currentPage || 1;
            }
        } catch (e) {}
        return 1;
    }

    function initBookmarks() {
        try {
            const raw = localStorage.getItem(`flipbook_bookmarks_${state.bookId}`);
            state.bookmarks = raw ? JSON.parse(raw) : [];
        } catch (e) {
            state.bookmarks = [];
        }
    }

    function toggleCurrentBookmark() {
        const page = state.currentPage;
        const existsIndex = state.bookmarks.findIndex(b => b.page === page);

        if (existsIndex !== -1) {
            state.bookmarks.splice(existsIndex, 1);
        } else {
            state.bookmarks.push({
                page: page,
                date: new Date().toLocaleDateString(),
                note: `Page ${page} Bookmark`,
            });
            state.bookmarks.sort((a, b) => a.page - b.page);
        }

        try {
            localStorage.setItem(`flipbook_bookmarks_${state.bookId}`, JSON.stringify(state.bookmarks));
        } catch (e) {}

        renderBookmarksList();
    }

    function renderBookmarksList() {
        if (!elements.bookmarksList) return;
        elements.bookmarksList.innerHTML = '';

        if (state.bookmarks.length === 0) {
            elements.bookmarksList.innerHTML = `
                <div style="text-align:center;padding:2rem 1rem;color:#94a3b8;font-size:0.875rem;">
                    <i data-lucide="bookmark-minus" style="width:32px;height:32px;margin:0 auto 0.5rem;opacity:0.6;"></i>
                    <p>No bookmarks saved for this e-book yet.</p>
                </div>
            `;
            if (window.lucide) window.lucide.createIcons();
            return;
        }

        state.bookmarks.forEach((b, idx) => {
            const item = document.createElement('div');
            item.style.cssText = 'padding:0.85rem 1rem;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:space-between;cursor:pointer;';
            item.innerHTML = `
                <div>
                    <div style="font-weight:700;color:#f8fafc;font-size:0.9rem;">Page ${b.page}</div>
                    <div style="font-size:0.75rem;color:#94a3b8;">${b.note || 'Saved Bookmark'} • ${b.date}</div>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <button class="btn-jump-bm" data-page="${b.page}" style="background:#4f46e5;color:#fff;border:none;border-radius:4px;padding:0.25rem 0.6rem;font-size:0.75rem;cursor:pointer;">Jump</button>
                    <button class="btn-del-bm" data-index="${idx}" style="background:transparent;color:#ef4444;border:none;cursor:pointer;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                </div>
            `;
            elements.bookmarksList.appendChild(item);
        });

        // Jump buttons
        elements.bookmarksList.querySelectorAll('.btn-jump-bm').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const p = parseInt(btn.getAttribute('data-page'));
                turnToPage(p);
                closeAllDrawers();
            });
        });

        // Delete buttons
        elements.bookmarksList.querySelectorAll('.btn-del-bm').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(btn.getAttribute('data-index'));
                state.bookmarks.splice(idx, 1);
                localStorage.setItem(`flipbook_bookmarks_${state.bookId}`, JSON.stringify(state.bookmarks));
                renderBookmarksList();
            });
        });

        if (window.lucide) window.lucide.createIcons();
    }

    // 6. Drawers Setup
    function closeAllDrawers() {
        [
            elements.drawerToc,
            elements.drawerThumbs,
            elements.drawerSearch,
            elements.drawerBookmarks,
            elements.drawerAiTutor,
        ].forEach(d => {
            if (d) d.classList.remove('open');
        });
    }

    function toggleDrawer(drawer) {
        if (!drawer) return;
        const isOpen = drawer.classList.contains('open');
        closeAllDrawers();
        if (!isOpen) {
            drawer.classList.add('open');
        }
    }

    // Table of Contents
    async function renderTableOfContents() {
        if (!elements.tocList) return;
        elements.tocList.innerHTML = '';

        try {
            const rawOutline = await state.pdfDoc.getOutline();
            if (rawOutline && rawOutline.length > 0) {
                rawOutline.forEach(item => {
                    appendTocItem(item.title, 1); // fallback
                });
            } else {
                // Synthetic Chapter Navigation
                const step = state.totalPages > 20 ? 5 : state.totalPages > 10 ? 3 : 2;
                for (let p = 1; p <= state.totalPages; p += step) {
                    const title = p === 1 ? 'Cover & Introduction' : `Module Section • Page ${p}`;
                    appendTocItem(title, p);
                }
            }
        } catch (e) {
            // Synthetic fallback
            for (let p = 1; p <= state.totalPages; p += 5) {
                appendTocItem(`Section • Page ${p}`, p);
            }
        }
    }

    function appendTocItem(title, pageNum) {
        const item = document.createElement('div');
        item.style.cssText = 'padding:0.75rem 1rem;border-bottom:1px solid rgba(255,255,255,0.08);color:#e2e8f0;font-size:0.875rem;cursor:pointer;display:flex;justify-content:space-between;align-items:center;';
        item.innerHTML = `
            <span>${title}</span>
            <span style="color:#94a3b8;font-size:0.75rem;background:rgba(255,255,255,0.06);padding:0.15rem 0.5rem;border-radius:4px;">p. ${pageNum}</span>
        `;
        item.addEventListener('click', () => {
            turnToPage(pageNum);
            closeAllDrawers();
        });
        elements.tocList.appendChild(item);
    }

    // Thumbnails Grid
    function renderThumbnails() {
        if (!elements.thumbsGrid) return;
        elements.thumbsGrid.innerHTML = '';

        state.pageImages.forEach((url, idx) => {
            const pageNum = idx + 1;
            const thumb = document.createElement('div');
            thumb.className = `thumb-card ${pageNum === state.currentPage ? 'active' : ''}`;
            thumb.setAttribute('data-page', pageNum);
            thumb.innerHTML = `
                <img src="${url}" loading="lazy" alt="Page ${pageNum}">
                <div class="thumb-page-num">${pageNum}</div>
            `;
            thumb.addEventListener('click', () => {
                turnToPage(pageNum);
                closeAllDrawers();
            });
            elements.thumbsGrid.appendChild(thumb);
        });
    }

    // In-Text Search
    function performSearch(query) {
        if (!elements.searchResults) return;
        const q = query.trim().toLowerCase();
        elements.searchResults.innerHTML = '';

        if (!q || q.length < 2) {
            elements.searchResults.innerHTML = `<div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:0.85rem;">Enter at least 2 characters to search.</div>`;
            return;
        }

        const matches = [];
        state.pageTexts.forEach((text, idx) => {
            const pageNum = idx + 1;
            const lower = (text || '').toLowerCase();
            const index = lower.indexOf(q);

            if (index !== -1) {
                const start = Math.max(0, index - 40);
                const end = Math.min(text.length, index + q.length + 50);
                const snippet = (start > 0 ? '...' : '') + text.substring(start, end) + (end < text.length ? '...' : '');

                matches.push({ page: pageNum, snippet: snippet });
            }
        });

        if (matches.length === 0) {
            elements.searchResults.innerHTML = `<div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:0.85rem;">No occurrences found for "${escapeHtml(query)}".</div>`;
            return;
        }

        matches.forEach(m => {
            const card = document.createElement('div');
            card.style.cssText = 'padding:0.85rem 1rem;border-bottom:1px solid rgba(255,255,255,0.08);cursor:pointer;';
            card.innerHTML = `
                <div style="font-weight:700;color:#a78bfa;font-size:0.85rem;margin-bottom:0.25rem;">Page ${m.page}</div>
                <div style="font-size:0.825rem;color:#cbd5e1;line-height:1.4;">${highlightText(m.snippet, query)}</div>
            `;
            card.addEventListener('click', () => {
                turnToPage(m.page);
                closeAllDrawers();
            });
            elements.searchResults.appendChild(card);
        });
    }

    function highlightText(text, keyword) {
        const escaped = escapeHtml(text);
        const regex = new RegExp(`(${keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escaped.replace(regex, '<mark style="background:#fef08a;color:#854d0e;padding:0 2px;border-radius:2px;">$1</mark>');
    }

    // 7. Interactive Learning Modal (Quiz, Flashcards, Video)
    function setupInteractiveModal() {
        const elementsList = state.interactiveElements;
        const modal = elements.modalInteractive;
        if (!modal) return;

        const tabQuiz = document.getElementById('tab-btn-quiz');
        const tabFlash = document.getElementById('tab-btn-flash');
        const tabVideo = document.getElementById('tab-btn-video');
        const tabMatch = document.getElementById('tab-btn-match');

        const paneQuiz = document.getElementById('pane-quiz');
        const paneFlash = document.getElementById('pane-flash');
        const paneVideo = document.getElementById('pane-video');
        const paneMatch = document.getElementById('pane-match');

        let quizData = [];
        let flashData = [];
        let videoData = null;

        elementsList.forEach(item => {
            if (item.type === 'quiz') {
                quizData = quizData.concat(item.data?.questions || []);
            } else if (item.type === 'flashcards') {
                flashData = flashData.concat(item.data?.cards || []);
            } else if (item.type === 'video') {
                videoData = item.data;
            }
        });

        // Tab Navigation
        const tabs = [
            { btn: tabQuiz, pane: paneQuiz },
            { btn: tabFlash, pane: paneFlash },
            { btn: tabMatch, pane: paneMatch },
            { btn: tabVideo, pane: paneVideo },
        ];

        tabs.forEach(t => {
            if (!t.btn || !t.pane) return;
            t.btn.addEventListener('click', () => {
                tabs.forEach(o => {
                    o.btn?.classList.remove('active');
                    if (o.pane) o.pane.style.display = 'none';
                });
                t.btn.classList.add('active');
                t.pane.style.display = 'block';
            });
        });

        // Render Quiz
        renderQuizWidget(quizData);

        // Render Flashcards
        renderFlashcardsWidget(flashData);

        // Render Speed Match Game
        renderSpeedMatchWidget(flashData);

        // Render Video
        renderVideoWidget(videoData);
    }

    function renderQuizWidget(questions) {
        const container = document.getElementById('quiz-widget-content');
        if (!container) return;

        if (questions.length === 0) {
            container.innerHTML = `<div style="text-align:center;padding:2rem;color:#64748b;">No quizzes generated yet for this book.</div>`;
            return;
        }

        let currentQIndex = 0;
        let score = 0;

        function showQuestion(idx) {
            const q = questions[idx];
            container.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;font-size:0.85rem;color:#64748b;">
                    <span>Question ${idx + 1} of ${questions.length}</span>
                    <span>Score: <strong style="color:#4f46e5;">${score}</strong></span>
                </div>
                <h4 style="font-size:1.1rem;font-weight:700;color:#0f172a;line-height:1.5;margin-bottom:1.25rem;">
                    ${escapeHtml(q.question)}
                </h4>
                <div id="quiz-options-box" style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem;">
                    ${q.options.map((opt, oIdx) => `
                        <button class="quiz-option-btn" data-opt-index="${oIdx}" style="text-align:left;padding:0.85rem 1.15rem;border-radius:8px;border:1px solid #e2e8f0;background:#ffffff;font-size:0.925rem;cursor:pointer;transition:all 0.15s ease;">
                            <span style="font-weight:700;margin-right:0.5rem;color:#6366f1;">${String.fromCharCode(65 + oIdx)}.</span> ${escapeHtml(opt)}
                        </button>
                    `).join('')}
                </div>
                <div id="quiz-feedback-box" style="display:none;padding:1rem;border-radius:8px;font-size:0.875rem;line-height:1.5;margin-bottom:1.25rem;"></div>
                <div style="display:flex;justify-content:flex-end;">
                    <button id="btn-next-quiz-q" class="btn btn-primary btn-sm" style="display:none;">
                        ${idx + 1 === questions.length ? 'Finish Assessment' : 'Next Question →'}
                    </button>
                </div>
            `;

            const optButtons = container.querySelectorAll('.quiz-option-btn');
            const feedbackBox = document.getElementById('quiz-feedback-box');
            const nextBtn = document.getElementById('btn-next-quiz-q');

            optButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const selectedIdx = parseInt(btn.getAttribute('data-opt-index'));
                    const isCorrect = selectedIdx === q.correctIndex;

                    optButtons.forEach(b => b.disabled = true);

                    if (isCorrect) {
                        score++;
                        btn.style.borderColor = '#10b981';
                        btn.style.background = '#ecfdf5';
                        feedbackBox.style.display = 'block';
                        feedbackBox.style.background = '#ecfdf5';
                        feedbackBox.style.border = '1px solid #a7f3d0';
                        feedbackBox.style.color = '#065f46';
                        feedbackBox.innerHTML = `<strong>✓ Correct!</strong> ${escapeHtml(q.explanation || 'Excellent work.')}`;
                    } else {
                        btn.style.borderColor = '#ef4444';
                        btn.style.background = '#fef2f2';
                        // Highlight correct one
                        optButtons[q.correctIndex].style.borderColor = '#10b981';
                        optButtons[q.correctIndex].style.background = '#ecfdf5';

                        feedbackBox.style.display = 'block';
                        feedbackBox.style.background = '#fef2f2';
                        feedbackBox.style.border = '1px solid #fecaca';
                        feedbackBox.style.color = '#991b1b';
                        feedbackBox.innerHTML = `<strong>✗ Incorrect.</strong> ${escapeHtml(q.explanation || 'Review the explanation and continue.')}`;
                    }

                    nextBtn.style.display = 'inline-flex';
                });
            });

            nextBtn.addEventListener('click', () => {
                if (idx + 1 < questions.length) {
                    showQuestion(idx + 1);
                } else {
                    // Summary
                    container.innerHTML = `
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <div style="width:64px;height:64px;border-radius:50%;background:#ecfdf5;color:#10b981;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                <i data-lucide="award" style="width:36px;height:36px;"></i>
                            </div>
                            <h3 style="font-size:1.5rem;font-weight:800;color:#0f172a;">Assessment Completed!</h3>
                            <p style="font-size:1.15rem;color:#4f46e5;font-weight:700;margin:0.5rem 0 1.5rem;">
                                Your Score: ${score} / ${questions.length} (${Math.round((score / questions.length) * 100)}%)
                            </p>
                            <button id="btn-restart-quiz" class="btn btn-secondary">
                                <i data-lucide="rotate-ccw" style="width:16px;height:16px;"></i> Retake Quiz
                            </button>
                        </div>
                    `;
                    if (window.lucide) window.lucide.createIcons();
                    document.getElementById('btn-restart-quiz')?.addEventListener('click', () => {
                        score = 0;
                        showQuestion(0);
                    });
                }
            });
        }

        showQuestion(0);
    }

    function renderFlashcardsWidget(cards) {
        const container = document.getElementById('flash-widget-content');
        if (!container) return;

        if (cards.length === 0) {
            container.innerHTML = `<div style="text-align:center;padding:2rem;color:#64748b;">No flashcards available.</div>`;
            return;
        }

        let cardIdx = 0;

        function updateCard() {
            const c = cards[cardIdx];
            container.innerHTML = `
                <div style="text-align:center;font-size:0.85rem;color:#64748b;margin-bottom:1rem;">
                    Card ${cardIdx + 1} of ${cards.length} • Click card to flip
                </div>

                <!-- 3D Card Container -->
                <div id="interactive-flashcard" class="flashcard-3d-box" style="width:100%;max-width:500px;height:240px;margin:0 auto 1.5rem;perspective:1000px;cursor:pointer;">
                    <div id="flashcard-inner" class="flashcard-inner" style="position:relative;width:100%;height:100%;text-align:center;transition:transform 0.5s;transform-style:preserve-3d;">
                        <!-- Front (Term) -->
                        <div style="position:absolute;width:100%;height:100%;backface-visibility:hidden;background:#ffffff;border:2px solid #e2e8f0;border-radius:14px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;">
                            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#7c3aed;letter-spacing:0.05em;margin-bottom:0.5rem;">Concept Term</div>
                            <h3 style="font-size:1.4rem;font-weight:800;color:#0f172a;">${escapeHtml(c.term)}</h3>
                            <div style="font-size:0.775rem;color:#94a3b8;margin-top:1rem;">(Click to flip definition)</div>
                        </div>

                        <!-- Back (Definition) -->
                        <div style="position:absolute;width:100%;height:100%;backface-visibility:hidden;background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);color:#ffffff;border-radius:14px;box-shadow:var(--shadow-md);transform:rotateY(180deg);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;">
                            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#c7d2fe;letter-spacing:0.05em;margin-bottom:0.5rem;">Academic Definition</div>
                            <p style="font-size:1rem;line-height:1.6;color:#f8fafc;max-width:420px;">${escapeHtml(c.definition)}</p>
                            <div style="font-size:0.775rem;color:#a5b4fc;margin-top:1rem;">(Click to return to term)</div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:center;gap:1rem;align-items:center;">
                    <button id="btn-flash-prev" class="btn btn-secondary btn-sm" ${cardIdx === 0 ? 'disabled' : ''}>← Previous</button>
                    <button id="btn-flash-next" class="btn btn-secondary btn-sm" ${cardIdx === cards.length - 1 ? 'disabled' : ''}>Next →</button>
                </div>
            `;

            const cardBox = document.getElementById('interactive-flashcard');
            const cardInner = document.getElementById('flashcard-inner');
            let isFlipped = false;

            cardBox.addEventListener('click', () => {
                isFlipped = !isFlipped;
                cardInner.style.transform = isFlipped ? 'rotateY(180deg)' : 'rotateY(0deg)';
            });

            document.getElementById('btn-flash-prev')?.addEventListener('click', () => {
                if (cardIdx > 0) { cardIdx--; updateCard(); }
            });
            document.getElementById('btn-flash-next')?.addEventListener('click', () => {
                if (cardIdx < cards.length - 1) { cardIdx++; updateCard(); }
            });
        }

        updateCard();
    }

    function renderSpeedMatchWidget(cards) {
        const container = document.getElementById('match-widget-content');
        if (!container) return;

        if (cards.length < 3) {
            container.innerHTML = `<div style="text-align:center;padding:2rem;color:#64748b;">Not enough terms for speed match game.</div>`;
            return;
        }

        // Take up to 6 cards
        const subset = cards.slice(0, 6);
        let items = [];
        subset.forEach((c, i) => {
            items.push({ id: `term-${i}`, matchId: i, text: c.term, type: 'term' });
            items.push({ id: `def-${i}`, matchId: i, text: c.definition, type: 'def' });
        });

        // Shuffle
        items.sort(() => Math.random() - 0.5);

        let selected = null;
        let matchedCount = 0;
        let timerSeconds = 0;
        let timerInterval = null;

        container.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div style="font-size:0.875rem;font-weight:700;color:#0f172a;">Match terms with their definitions</div>
                <div style="font-size:0.875rem;font-weight:700;color:#4f46e5;"><i data-lucide="clock" style="width:14px;height:14px;display:inline-block;"></i> <span id="match-timer">0.0s</span></div>
            </div>
            <div id="match-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:0.75rem;">
                ${items.map(item => `
                    <button class="match-card-btn" data-id="${item.id}" data-match="${item.matchId}" style="min-height:85px;padding:0.75rem;border-radius:8px;border:1px solid #e2e8f0;background:#ffffff;font-size:0.8rem;text-align:center;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s ease;line-height:1.4;">
                        ${escapeHtml(item.text)}
                    </button>
                `).join('')}
            </div>
        `;

        if (window.lucide) window.lucide.createIcons();

        const timerEl = document.getElementById('match-timer');
        timerInterval = setInterval(() => {
            timerSeconds += 0.1;
            timerEl.textContent = `${timerSeconds.toFixed(1)}s`;
        }, 100);

        const cardBtns = container.querySelectorAll('.match-card-btn');
        cardBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('matched') || btn === selected) return;

                if (!selected) {
                    selected = btn;
                    btn.style.borderColor = '#6366f1';
                    btn.style.background = '#eef2ff';
                } else {
                    const match1 = selected.getAttribute('data-match');
                    const match2 = btn.getAttribute('data-match');

                    if (match1 === match2) {
                        // Match!
                        matchedCount++;
                        btn.style.borderColor = '#10b981';
                        btn.style.background = '#ecfdf5';
                        btn.classList.add('matched');

                        selected.style.borderColor = '#10b981';
                        selected.style.background = '#ecfdf5';
                        selected.classList.add('matched');

                        selected = null;

                        if (matchedCount === subset.length) {
                            clearInterval(timerInterval);
                            setTimeout(() => {
                                container.innerHTML = `
                                    <div style="text-align:center;padding:2.5rem 1rem;">
                                        <h3 style="font-size:1.5rem;font-weight:800;color:#0f172a;">🎉 Outstanding Speed!</h3>
                                        <p style="font-size:1.1rem;color:#10b981;font-weight:700;margin:0.5rem 0 1.5rem;">Completed in ${timerSeconds.toFixed(1)} seconds!</p>
                                        <button id="btn-replay-match" class="btn btn-primary">Play Again</button>
                                    </div>
                                `;
                                document.getElementById('btn-replay-match')?.addEventListener('click', () => {
                                    renderSpeedMatchWidget(cards);
                                });
                            }, 400);
                        }
                    } else {
                        // Mismatch
                        btn.style.borderColor = '#ef4444';
                        btn.style.background = '#fef2f2';
                        const first = selected;
                        selected = null;

                        setTimeout(() => {
                            btn.style.borderColor = '#e2e8f0';
                            btn.style.background = '#ffffff';
                            first.style.borderColor = '#e2e8f0';
                            first.style.background = '#ffffff';
                        }, 500);
                    }
                }
            });
        });
    }

    function renderVideoWidget(video) {
        const container = document.getElementById('video-widget-content');
        if (!container) return;

        if (!video || !video.youtubeUrl) {
            container.innerHTML = `
                <div style="text-align:center;padding:3rem 1rem;color:#64748b;">
                    <i data-lucide="video-off" style="width:36px;height:36px;margin:0 auto 0.5rem;opacity:0.6;"></i>
                    <p>No video lecture linked for this chapter.</p>
                </div>
            `;
            if (window.lucide) window.lucide.createIcons();
            return;
        }

        const videoId = video.videoId || 'xxpc-HPKN28';
        container.innerHTML = `
            <div style="max-width:700px;margin:0 auto;">
                <h4 style="font-size:1.1rem;font-weight:700;margin-bottom:0.75rem;color:#0f172a;">${escapeHtml(video.title || 'Curated Academic Lecture')}</h4>
                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;box-shadow:var(--shadow-md);margin-bottom:1rem;">
                    <iframe 
                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                        src="https://www.youtube-nocookie.com/embed/${videoId}" 
                        title="Course Lecture" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                    ></iframe>
                </div>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.5;">${escapeHtml(video.description || 'Watch this video to reinforce key concepts.')}</p>
            </div>
        `;
    }

    // 8. Aura AI Tutor Chat in Reader
    function setupAiTutorChat() {
        if (!elements.aiChatForm) return;

        elements.aiChatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = elements.aiChatInput.value.trim();
            if (!message) return;

            // Append user turn
            appendAiChatMessage('user', message);
            elements.aiChatInput.value = '';
            elements.aiChatInput.disabled = true;
            elements.aiChatSubmit.disabled = true;

            const loadingId = 'ai-loading-' + Date.now();
            appendAiLoading(loadingId);

            // Fetch active page text as context
            const currentPageText = state.pageTexts[state.currentPage - 1] || '';

            try {
                const res = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': state.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        book_title: state.bookTitle,
                        current_page: state.currentPage,
                        page_text: currentPageText.substring(0, 3000),
                        history: state.aiChatHistory.slice(-6),
                    }),
                });

                const data = await res.json();
                removeAiLoading(loadingId);

                if (data && data.reply) {
                    appendAiChatMessage('model', data.reply);
                    state.aiChatHistory.push({ role: 'user', content: message });
                    state.aiChatHistory.push({ role: 'model', content: data.reply });
                } else {
                    appendAiChatMessage('model', 'I could not generate an answer right now. Please rephrase your question.');
                }
            } catch (err) {
                removeAiLoading(loadingId);
                appendAiChatMessage('model', 'Connection notice: Could not contact academic tutor server.');
            } finally {
                elements.aiChatInput.disabled = false;
                elements.aiChatSubmit.disabled = false;
                elements.aiChatInput.focus();
                if (window.lucide) window.lucide.createIcons();
            }
        });
    }

    function appendAiChatMessage(role, text) {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;gap:0.6rem;max-width:90%;margin-bottom:1rem;';

        if (role === 'user') {
            wrap.style.alignSelf = 'flex-end';
            wrap.style.flexDirection = 'row-reverse';
            wrap.innerHTML = `
                <div style="background:#4f46e5;color:#fff;border-radius:12px;border-top-right-radius:2px;padding:0.75rem 1rem;font-size:0.875rem;line-height:1.4;white-space:pre-wrap;">${escapeHtml(text)}</div>
            `;
        } else {
            wrap.innerHTML = `
                <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg, #7c3aed, #4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.75rem;font-weight:700;">AI</div>
                <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#e2e8f0;border-radius:12px;border-top-left-radius:2px;padding:0.85rem 1rem;font-size:0.875rem;line-height:1.5;white-space:pre-wrap;">${formatMarkdown(text)}</div>
            `;
        }

        elements.aiChatMessages.appendChild(wrap);
        elements.aiChatMessages.scrollTop = elements.aiChatMessages.scrollHeight;
    }

    function appendAiLoading(id) {
        const wrap = document.createElement('div');
        wrap.id = id;
        wrap.style.cssText = 'display:flex;gap:0.6rem;margin-bottom:1rem;';
        wrap.innerHTML = `
            <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg, #7c3aed, #4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.75rem;font-weight:700;">AI</div>
            <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;border-radius:12px;padding:0.75rem 1rem;font-size:0.825rem;display:flex;align-items:center;gap:0.4rem;">
                <i data-lucide="loader-2" style="width:14px;height:14px;animation:spin 1s linear infinite;"></i>
                <span>Analyzing Page ${state.currentPage}...</span>
            </div>
        `;
        elements.aiChatMessages.appendChild(wrap);
        elements.aiChatMessages.scrollTop = elements.aiChatMessages.scrollHeight;
        if (window.lucide) window.lucide.createIcons();
    }

    function removeAiLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    // 9. Setup Event Listeners
    function setupEventListeners() {
        // Navigation Buttons
        elements.btnPrev?.addEventListener('click', () => {
            state.pageFlip?.flipPrev();
        });

        elements.btnNext?.addEventListener('click', () => {
            state.pageFlip?.flipNext();
        });

        // Jump Page Input
        elements.inputPage?.addEventListener('change', () => {
            const target = parseInt(elements.inputPage.value);
            if (target >= 1 && target <= state.totalPages) {
                turnToPage(target);
            } else {
                elements.inputPage.value = state.currentPage;
            }
        });

        // Zoom Controls
        elements.btnZoomIn?.addEventListener('click', () => {
            state.zoom = Math.min(state.zoom + 0.15, 2.0);
            applyZoom();
        });

        elements.btnZoomOut?.addEventListener('click', () => {
            state.zoom = Math.max(state.zoom - 0.15, 0.7);
            applyZoom();
        });

        elements.btnZoomFit?.addEventListener('click', () => {
            state.zoom = 1.0;
            applyZoom();
        });

        // Spread Mode Toggle
        elements.btnSpread?.addEventListener('click', () => {
            state.spreadMode = state.spreadMode === 'single' ? 'double' : 'single';
            elements.btnSpread.textContent = state.spreadMode === 'single' ? '📖 1-Page' : '📖 2-Page';
            // Remount
            if (state.pageDoc) {
                mountPageFlip(0.707);
            }
        });

        // Fullscreen Toggle
        elements.btnFullscreen?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        });

        // Keyboard Navigation
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea'].includes(document.activeElement?.tagName?.toLowerCase())) return;

            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                state.pageFlip?.flipPrev();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                state.pageFlip?.flipNext();
            } else if (e.key === 'Escape') {
                closeAllDrawers();
                if (elements.modalInteractive) elements.modalInteractive.style.display = 'none';
            }
        });

        // Drawer Toggles
        elements.btnToggleToc?.addEventListener('click', () => toggleDrawer(elements.drawerToc));
        elements.btnToggleThumbs?.addEventListener('click', () => toggleDrawer(elements.drawerThumbs));
        elements.btnToggleSearch?.addEventListener('click', () => toggleDrawer(elements.drawerSearch));
        elements.btnToggleBookmarks?.addEventListener('click', () => toggleDrawer(elements.drawerBookmarks));
        elements.btnToggleAiTutor?.addEventListener('click', () => toggleDrawer(elements.drawerAiTutor));

        // Close Drawer Buttons
        document.querySelectorAll('.btn-close-drawer').forEach(btn => {
            btn.addEventListener('click', () => closeAllDrawers());
        });

        // Bookmark button in drawer
        elements.btnBookmarkCurrent?.addEventListener('click', () => toggleCurrentBookmark());

        // Search Form
        elements.searchForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch(elements.searchInput.value);
        });

        // Interactive Modal
        elements.btnToggleInteractive?.addEventListener('click', () => {
            if (elements.modalInteractive) {
                elements.modalInteractive.style.display = 'flex';
                if (window.lucide) window.lucide.createIcons();
            }
        });

        document.getElementById('btn-close-interactive-modal')?.addEventListener('click', () => {
            if (elements.modalInteractive) elements.modalInteractive.style.display = 'none';
        });

        // AI Tutor Chat setup
        setupAiTutorChat();
    }

    function turnToPage(pageNum) {
        if (!state.pageFlip) return;
        const targetIndex = Math.max(0, pageNum - 1);
        try {
            state.pageFlip.turnToPage(targetIndex);
        } catch (e) {}
    }

    function applyZoom() {
        if (elements.mountEl) {
            elements.mountEl.style.transform = `scale(${state.zoom})`;
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function formatMarkdown(text) {
        let esc = escapeHtml(text);
        esc = esc.replace(/\*\*(.*?)\*\*/g, '<strong style="color:#ffffff;">$1</strong>');
        return esc;
    }
})();
