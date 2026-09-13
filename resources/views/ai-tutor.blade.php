@extends('layouts.app')

@section('title', 'AI Academic Tutor ("Aura") • Politeknik Besut')

@section('content')
<div style="max-width: 900px; margin: 1rem auto 3rem;">
    <!-- Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--secondary), var(--primary)); color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow);">
                <i data-lucide="bot" style="width: 26px; height: 26px;"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--dark);">Aura AI Academic Tutor</h1>
                    <span style="font-size: 0.75rem; background: rgba(124, 58, 237, 0.1); color: var(--secondary); font-weight: 700; padding: 0.15rem 0.55rem; border-radius: var(--radius-full);">
                        Google Gemini Powered
                    </span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Personalized mathematical, engineering, and syllabus tutor for Politeknik Besut</p>
            </div>
        </div>

        <!-- Textbook Context Selector -->
        <div style="min-width: 240px;">
            <select id="book-select" class="form-control" style="font-size: 0.85rem; font-weight: 600;">
                <option value="">-- General Academic Context --</option>
                @foreach($books as $b)
                    <option value="{{ $b->title }}" {{ (isset($selectedBook) && $selectedBook->id === $b->id) ? 'selected' : '' }}>
                        📚 {{ Str::limit($b->title, 35) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Chat Container Card -->
    <div class="card" style="height: 620px; display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-md);">
        <!-- Active Context Banner -->
        <div style="padding: 0.75rem 1.25rem; background: var(--bg-page); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; font-size: 0.825rem; color: var(--text-muted);">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <i data-lucide="book-marked" style="width: 15px; height: 15px; color: var(--primary);"></i>
                <span>Active Context: <strong id="active-book-label" style="color: var(--dark);">{{ isset($selectedBook) ? $selectedBook->title : 'General Academic Syllabus' }}</strong></span>
            </div>
            <span style="display: inline-flex; align-items: center; gap: 0.35rem; color: var(--success); font-weight: 600;">
                <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--success); display: inline-block;"></span> Online
            </span>
        </div>

        <!-- Messages Area -->
        <div id="chat-messages" style="flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Welcome message from Aura -->
            <div style="display: flex; gap: 0.75rem; max-width: 85%;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, var(--secondary), var(--primary)); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">
                    AI
                </div>
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 14px; border-top-left-radius: 4px; padding: 1rem 1.25rem; box-shadow: var(--shadow-sm); font-size: 0.925rem; line-height: 1.6; color: var(--text-main);">
                    <p style="font-weight: 600; color: var(--dark); margin-bottom: 0.35rem;">
                        Hello! I am <strong>Aura</strong>, your academic AI study partner. 🎓
                    </p>
                    <p>
                        I'm here to help you work through calculations, clarify engineering theories, understand definitions, and test your knowledge for your coursework.
                    </p>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                        Select your textbook from the dropdown above or pick a quick study prompt below to begin!
                    </p>
                </div>
            </div>
        </div>

        <!-- Suggestion Pills -->
        <div style="padding: 0.6rem 1.25rem; background: #fafafa; border-top: 1px solid var(--border); display: flex; gap: 0.5rem; overflow-x: auto; white-space: nowrap;">
            <button type="button" class="btn-prompt-pill" data-prompt="Summarize the core concepts and fundamental formulas for this subject.">
                💡 Summarize core concepts
            </button>
            <button type="button" class="btn-prompt-pill" data-prompt="Give me an exam-style practice problem with a step-by-step worked solution.">
                📝 Practice problem with solution
            </button>
            <button type="button" class="btn-prompt-pill" data-prompt="Explain the difference between population and sample with a practical example.">
                🔍 Population vs Sample
            </button>
            <button type="button" class="btn-prompt-pill" data-prompt="Test my knowledge with 3 quick multiple-choice questions.">
                ⚡ Test my knowledge
            </button>
        </div>

        <!-- Chat Input Form -->
        <div style="padding: 1rem 1.25rem; background: #ffffff; border-top: 1px solid var(--border);">
            <form id="chat-form" style="display: flex; gap: 0.75rem; align-items: flex-end;">
                <textarea 
                    id="chat-input" 
                    rows="1" 
                    placeholder="Ask Aura about an equation, formula, or concept..." 
                    class="form-control" 
                    style="resize: none; min-height: 44px; max-height: 120px; font-size: 0.925rem; padding: 0.65rem 0.85rem;"
                    required
                ></textarea>

                <button type="submit" id="chat-submit" class="btn btn-primary" style="height: 44px; padding: 0 1.25rem; border-radius: var(--radius-sm); flex-shrink: 0;">
                    <i data-lucide="send" style="width: 17px; height: 17px;"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .btn-prompt-pill {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: var(--radius-full);
        padding: 0.35rem 0.85rem;
        font-size: 0.775rem;
        font-weight: 600;
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-prompt-pill:hover {
        background: var(--primary-light);
        color: var(--primary-text);
        border-color: #c7d2fe;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('chat-form');
        const input = document.getElementById('chat-input');
        const submitBtn = document.getElementById('chat-submit');
        const messagesBox = document.getElementById('chat-messages');
        const bookSelect = document.getElementById('book-select');
        const activeBookLabel = document.getElementById('active-book-label');
        const promptPills = document.querySelectorAll('.btn-prompt-pill');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const history = [];

        // Auto-update context label
        bookSelect.addEventListener('change', () => {
            activeBookLabel.textContent = bookSelect.value || 'General Academic Syllabus';
        });

        // Prompt pills
        promptPills.forEach(pill => {
            pill.addEventListener('click', () => {
                input.value = pill.getAttribute('data-prompt');
                input.focus();
            });
        });

        // Auto-expand textarea
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) return;

            // 1. Append User Message
            appendMessage('user', message);
            input.value = '';
            input.style.height = '44px';
            input.disabled = true;
            submitBtn.disabled = true;

            // 2. Append Loading Placeholder
            const loadingId = 'loading-' + Date.now();
            appendLoading(loadingId);

            // 3. Send Request to /ai/chat
            try {
                const response = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        book_title: bookSelect.value || 'Politeknik Besut Academic Coursework',
                        history: history.slice(-8), // Keep recent turns
                    }),
                });

                const data = await response.json();
                removeLoading(loadingId);

                if (data && data.reply) {
                    appendMessage('model', data.reply);
                    history.push({ role: 'user', content: message });
                    history.push({ role: 'model', content: data.reply });
                } else {
                    appendMessage('model', 'I apologize, but I encountered an issue processing your query. Please ask again.');
                }
            } catch (err) {
                removeLoading(loadingId);
                appendMessage('model', 'Connection notice: Could not contact academic tutor server. Please verify your connection.');
            } finally {
                input.disabled = false;
                submitBtn.disabled = false;
                input.focus();
                if (window.lucide) window.lucide.createIcons();
            }
        });

        function appendMessage(role, text) {
            const wrap = document.createElement('div');
            wrap.style.display = 'flex';
            wrap.style.gap = '0.75rem';
            wrap.style.maxWidth = '85%';

            if (role === 'user') {
                wrap.style.alignSelf = 'flex-end';
                wrap.style.flexDirection = 'row-reverse';
                wrap.innerHTML = `
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">
                        You
                    </div>
                    <div style="background: var(--primary); color: #ffffff; border-radius: 14px; border-top-right-radius: 4px; padding: 0.85rem 1.15rem; font-size: 0.925rem; line-height: 1.5; box-shadow: var(--shadow-sm); white-space: pre-wrap;">${escapeHtml(text)}</div>
                `;
            } else {
                wrap.innerHTML = `
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, var(--secondary), var(--primary)); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">
                        AI
                    </div>
                    <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 14px; border-top-left-radius: 4px; padding: 1rem 1.25rem; font-size: 0.925rem; line-height: 1.6; color: var(--text-main); box-shadow: var(--shadow-sm); white-space: pre-wrap;">${formatMarkdown(text)}</div>
                `;
            }

            messagesBox.appendChild(wrap);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function appendLoading(id) {
            const wrap = document.createElement('div');
            wrap.id = id;
            wrap.style.display = 'flex';
            wrap.style.gap = '0.75rem';
            wrap.innerHTML = `
                <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, var(--secondary), var(--primary)); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; font-weight: 700;">
                    AI
                </div>
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 14px; padding: 0.85rem 1.15rem; color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="loader-2" class="spin" style="width: 16px; height: 16px; animation: spin 1s linear infinite;"></i>
                    <span>Aura is formulating pedagogical solution...</span>
                </div>
            `;
            messagesBox.appendChild(wrap);
            messagesBox.scrollTop = messagesBox.scrollHeight;
            if (window.lucide) window.lucide.createIcons();
        }

        function removeLoading(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatMarkdown(text) {
            let escaped = escapeHtml(text);
            // Bold
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Headers
            escaped = escaped.replace(/^### (.*$)/gim, '<h4 style="font-weight:700;margin-top:0.5rem;color:var(--dark);">$1</h4>');
            escaped = escaped.replace(/^## (.*$)/gim, '<h3 style="font-weight:800;margin-top:0.75rem;color:var(--dark);">$1</h3>');
            return escaped;
        }
    });
</script>
@endsection
