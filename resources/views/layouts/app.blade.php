<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Politeknik Besut Academic E-Book Platform')</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @yield('styles')
</head>
<body>
    <!-- Liquid Glass Navigation Bar -->
    <nav class="liquid-nav">
        <div class="container nav-container">
            <!-- Brand -->
            <a href="{{ route('home') }}" class="nav-brand">
                <div class="brand-icon-box">
                    <i data-lucide="book-open" style="width: 20px; height: 20px;"></i>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span>PoliBesut</span>
                    <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">FlipBook</span>
                    <span class="liquid-pill" style="font-size: 10px; padding: 2px 7px;">Academic</span>
                </div>
            </a>

            <!-- Nav Links -->
            <ul class="nav-links">
                <li>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i data-lucide="home" style="width: 16px; height: 16px;"></i> Home
                    </a>
                </li>
                <li>
                    <a href="{{ route('library') }}" class="nav-link {{ request()->routeIs('library') ? 'active' : '' }}">
                        <i data-lucide="library" style="width: 16px; height: 16px;"></i> Library
                    </a>
                </li>
                <li>
                    <a href="{{ route('ai.tutor') }}" class="nav-link {{ request()->routeIs('ai.tutor') ? 'active' : '' }}">
                        <i data-lucide="sparkles" style="color: var(--primary); width: 16px; height: 16px;"></i> AI Study Room
                    </a>
                </li>
                <li>
                    <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                        <i data-lucide="info" style="width: 16px; height: 16px;"></i> About
                    </a>
                </li>
            </ul>

            <!-- Actions -->
            <div class="nav-actions">
                @auth
                    <a href="{{ route('ebooks.create') }}" class="liquid-btn-primary" style="padding: 0.5rem 1rem; font-size: 0.825rem;">
                        <i data-lucide="upload-cloud" style="width: 15px; height: 15px;"></i> Upload E-Book
                    </a>
                    
                    <div class="user-badge-box">
                        <i data-lucide="user" style="width: 14px; height: 14px; color: var(--primary);"></i>
                        <span style="max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ Auth::user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline; margin-left: 0.25rem;">
                            @csrf
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--muted); display: flex; align-items: center; padding: 2px;" title="Log Out">
                                <i data-lucide="log-out" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="liquid-btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.825rem;">
                        <i data-lucide="log-in" style="width: 14px; height: 14px;"></i> Sign In
                    </a>
                    <a href="{{ route('register') }}" class="liquid-btn-primary" style="padding: 0.5rem 1rem; font-size: 0.825rem;">
                        <i data-lucide="user-plus" style="width: 14px; height: 14px;"></i> Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container">
            <!-- Flash Alerts -->
            @if (session('success'))
                <div class="liquid-alert liquid-alert-success">
                    <i data-lucide="check-circle-2" style="width: 20px; height: 20px; flex-shrink: 0; color: #059669;"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="liquid-alert liquid-alert-danger">
                    <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0; color: #dc2626;"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="liquid-alert liquid-alert-info">
                    <i data-lucide="info" style="width: 20px; height: 20px; flex-shrink: 0; color: #2563eb;"></i>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: var(--ink); font-size: 1.15rem;">
                        <div class="brand-icon-box" style="width: 32px; height: 32px; border-radius: 9px;">
                            <i data-lucide="book-open" style="width: 16px; height: 16px;"></i>
                        </div>
                        <span>Politeknik Besut Academic Platform</span>
                    </div>
                    <p>
                        High-performance 3D interactive flipbook and pedagogical AI tutoring system designed for technical and academic coursework at Politeknik Besut, Terengganu.
                    </p>
                </div>

                <div>
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.75rem;">Lecturer Project Team</h4>
                    <ul style="list-style: none; font-size: 0.85rem; color: var(--muted); space-y: 0.4rem;">
                        <li>• Farah Hayati Binti Che Lah</li>
                        <li>• Wan Izyani Binti Wan Jusoh</li>
                        <li>• Wee Siew Ping</li>
                    </ul>
                </div>

                <div>
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.75rem;">Quick Navigation</h4>
                    <ul style="list-style: none; font-size: 0.85rem; space-y: 0.4rem;">
                        <li><a href="{{ route('home') }}" style="color: var(--muted);">Home</a></li>
                        <li><a href="{{ route('library') }}" style="color: var(--muted);">E-Book Library</a></li>
                        <li><a href="{{ route('ai.tutor') }}" style="color: var(--muted);">AI Academic Tutor</a></li>
                        <li><a href="{{ route('about') }}" style="color: var(--muted);">About Platform</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} Politeknik Besut, Terengganu. All rights reserved.</span>
                <span class="liquid-pill" style="font-size: 10px;">Laravel 13 • Gemini AI • StPageFlip</span>
            </div>
        </div>
    </footer>

    <!-- Global Liquid Glass Confirmation Dialog -->
    <div id="liquid-confirm-modal" class="liquid-modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="liquid-confirm-title">
        <div class="liquid-modal-backdrop" onclick="closeConfirmModal()"></div>
        <div class="liquid-modal-card">
            <div class="liquid-modal-glow"></div>
            <div class="liquid-modal-body">
                <div class="liquid-modal-icon-wrap">
                    <div class="liquid-modal-icon">
                        <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
                <div class="liquid-modal-content">
                    <h3 id="liquid-confirm-title" class="liquid-modal-title">Delete E-Book?</h3>
                    <p id="liquid-confirm-desc" class="liquid-modal-desc">
                        Are you sure you want to permanently delete this e-book? This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="liquid-modal-actions">
                <button type="button" id="liquid-confirm-cancel-btn" class="liquid-btn-secondary liquid-modal-btn" onclick="closeConfirmModal()">
                    Cancel
                </button>
                <button type="button" id="liquid-confirm-proceed-btn" class="liquid-modal-btn liquid-btn-danger" onclick="proceedConfirmAction()">
                    <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                    <span id="liquid-confirm-proceed-text">Delete Permanently</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Initialize Lucide Icons & Global Modal Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        let activeTargetForm = null;

        window.confirmDelete = function(formOrAction, bookTitle, customMessage) {
            activeTargetForm = formOrAction;
            const modal = document.getElementById('liquid-confirm-modal');
            const desc = document.getElementById('liquid-confirm-desc');
            const titleEl = document.getElementById('liquid-confirm-title');

            if (titleEl) titleEl.textContent = 'Delete E-Book?';
            if (desc) {
                if (customMessage) {
                    desc.innerHTML = customMessage;
                } else if (bookTitle) {
                    desc.innerHTML = `Are you sure you want to permanently delete <strong>"${bookTitle}"</strong>? This will remove the PDF document from the library and cannot be undone.`;
                } else {
                    desc.innerHTML = 'Are you sure you want to permanently delete this e-book? This action will remove the PDF document from the server and cannot be undone.';
                }
            }

            modal.style.display = 'flex';
            void modal.offsetWidth; // Trigger reflow for CSS transition
            modal.classList.add('active');

            if (window.lucide) {
                window.lucide.createIcons();
            }

            const cancelBtn = document.getElementById('liquid-confirm-cancel-btn');
            if (cancelBtn) cancelBtn.focus();
        };

        window.closeConfirmModal = function() {
            const modal = document.getElementById('liquid-confirm-modal');
            if (!modal) return;
            modal.classList.remove('active');
            setTimeout(function() {
                if (!modal.classList.contains('active')) {
                    modal.style.display = 'none';
                    activeTargetForm = null;
                }
            }, 260);
        };

        window.proceedConfirmAction = function() {
            if (activeTargetForm) {
                if (typeof activeTargetForm === 'function') {
                    activeTargetForm();
                } else if (activeTargetForm.tagName === 'FORM') {
                    activeTargetForm.submit();
                } else if (typeof activeTargetForm.submit === 'function') {
                    activeTargetForm.submit();
                }
            }
            closeConfirmModal();
        };

        // Keyboard navigation (Escape to dismiss)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('liquid-confirm-modal');
                if (modal && modal.classList.contains('active')) {
                    closeConfirmModal();
                }
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
