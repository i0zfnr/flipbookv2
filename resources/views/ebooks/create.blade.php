@extends('layouts.app')

@section('title', 'Upload Academic E-Book • Politeknik Besut')

@section('styles')
<script src="{{ asset('js/pdf.min.js') }}"></script>
<script>
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
    }
</script>
@endsection

@section('content')
<div style="max-width: 820px; margin: 1.5rem auto 4.5rem;">
    <!-- Breadcrumb -->
    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--muted); margin-bottom: 1.75rem;">
        <a href="{{ route('library') }}" style="color: var(--primary); font-weight: 600;">Library</a>
        <span>/</span>
        <span style="color: var(--ink); font-weight: 700;">Upload Course E-Book</span>
    </div>

    <div class="liquid-card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div class="brand-icon-box" style="width: 46px; height: 46px; border-radius: 14px;">
                    <i data-lucide="upload-cloud" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--ink); letter-spacing: -0.02em;">Upload Academic Course E-Book</h1>
                    <p style="font-size: 0.9rem; color: var(--muted); margin-top: 0.2rem;">Publish curriculum modules, textbooks, or laboratory syllabi for Politeknik Besut</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('ebooks.store') }}" enctype="multipart/form-data" id="upload-form">
            @csrf
            <input type="hidden" name="total_pages" id="total_pages_input" value="{{ old('total_pages', '') }}">
            <input type="hidden" name="text_sample" id="text_sample_input" value="">

            <!-- PDF File Dropzone -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem;">
                    PDF Textbook Document <span style="color: #ef4444;">*</span>
                </label>
                <div class="liquid-glass" id="pdf-dropzone" style="border: 2px dashed rgba(124, 58, 237, 0.4); border-radius: 20px; padding: 2.75rem 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s ease;">
                    <input type="file" name="pdf" id="pdf-input" accept="application/pdf" style="display: none;" required>
                    <div id="dropzone-prompt">
                        <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(124, 58, 237, 0.1); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i data-lucide="file-up" style="width: 28px; height: 28px;"></i>
                        </div>
                        <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--ink);">Select or Drag & Drop Course PDF</h4>
                        <p style="font-size: 0.825rem; color: var(--muted); margin-top: 0.35rem;">Supports syllabus and textbook PDFs up to 100 MB</p>
                    </div>
                    <div id="dropzone-selected" style="display: none;">
                        <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                            <i data-lucide="file-check" style="width: 28px; height: 28px;"></i>
                        </div>
                        <h4 id="selected-file-name" style="font-size: 1.05rem; font-weight: 800; color: var(--ink);">document.pdf</h4>
                        <p id="selected-file-meta" style="font-size: 0.825rem; color: var(--muted); margin-top: 0.25rem;">Scanning vector pages...</p>
                        <button type="button" class="liquid-btn-secondary" id="btn-change-file" style="margin-top: 1rem; padding: 0.4rem 0.85rem; font-size: 0.8rem;">
                            Change PDF File
                        </button>
                    </div>
                </div>
                @error('pdf')
                    <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.4rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Title -->
            <div style="margin-bottom: 1.5rem;">
                <label for="title" style="display: block; font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem;">
                    Textbook Title <span style="color: #ef4444;">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title') }}" 
                    class="liquid-input" 
                    required 
                    placeholder="e.g. DBM30263 Introduction to Statistics"
                >
                @error('title')
                    <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.4rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Author & Status Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="author" style="display: block; font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem;">
                        Author / Lecturer Name
                    </label>
                    <input 
                        type="text" 
                        id="author" 
                        name="author" 
                        value="{{ old('author', Auth::user()->name ?? 'Farah Hayati Binti Che Lah') }}" 
                        class="liquid-input"
                    >
                    @error('author')
                        <div style="font-size: 0.8rem; color: #ef4444; margin-top: 0.4rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="status" style="display: block; font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem;">
                        Publication Status
                    </label>
                    <select id="status" name="status" class="liquid-input">
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published (Active for students)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                    </select>
                </div>
            </div>

            <!-- Cover Image (Optional) -->
            <div style="margin-bottom: 1.5rem;">
                <label for="cover" style="display: block; font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem;">
                    Custom Cover Image (Optional)
                </label>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div id="cover-preview-box" style="width: 80px; height: 110px; border-radius: 12px; border: 1px dashed rgba(124, 58, 237, 0.4); display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.5); color: var(--light-muted); overflow: hidden; flex-shrink: 0;">
                        <i data-lucide="image" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div style="flex: 1;">
                        <input type="file" name="cover" id="cover-input" accept="image/jpeg,image/png,image/webp" class="liquid-input">
                        <p style="font-size: 0.8rem; color: var(--muted); margin-top: 0.35rem;">
                            JPG, PNG, or WebP. If left blank, the platform automatically renders Page 1 of the PDF as the retina cover!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 1.75rem;">
                <label for="description" style="display: block; font-size: 0.875rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem;">
                    Course Scope & Description
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="liquid-input" 
                    rows="4" 
                    placeholder="Provide syllabus overview, target audience, course code, and chapter objectives..."
                    style="resize: vertical;"
                >{{ old('description') }}</textarea>
            </div>

            <!-- AI Interactive Learning Suite Generator Option -->
            <div class="liquid-card" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.08) 0%, rgba(99, 102, 241, 0.03) 100%); border-color: rgba(124, 58, 237, 0.3); padding: 1.35rem; margin-bottom: 2rem;">
                <label style="display: flex; align-items: flex-start; gap: 0.85rem; cursor: pointer;">
                    <input type="checkbox" name="generate_ai" id="generate_ai" value="1" checked style="margin-top: 0.25rem; width: 18px; height: 18px; accent-color: var(--primary);">
                    <div>
                        <div style="font-weight: 800; color: var(--ink); display: flex; align-items: center; gap: 0.45rem;">
                            <i data-lucide="sparkles" style="width: 17px; height: 17px; color: var(--primary);"></i>
                            Generate Google Gemini AI Interactive Learning Suite
                        </div>
                        <p style="font-size: 0.85rem; color: var(--muted); margin-top: 0.25rem; line-height: 1.5;">
                            Google Gemini will scan the mathematical equations and technical text inside this PDF to formulate 10 multiple-choice assessment questions and 8 terminology flashcards directly inside the 3D reader!
                        </p>
                    </div>
                </label>
            </div>

            <!-- Progress Bar -->
            <div id="upload-progress-container" style="display: none; margin-bottom: 1.75rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: var(--ink); margin-bottom: 0.4rem;">
                    <span id="upload-status-text">Uploading and processing document...</span>
                    <span id="upload-percent-text">0%</span>
                </div>
                <div style="height: 8px; width: 100%; background: rgba(226, 232, 240, 0.8); border-radius: 99px; overflow: hidden;">
                    <div id="upload-progress-bar" style="height: 100%; width: 0%; background: var(--primary-gradient); transition: width 0.3s ease;"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('library') }}" class="liquid-btn-secondary">Cancel</a>
                <button type="submit" class="liquid-btn-primary" id="btn-submit" style="padding: 0.8rem 1.6rem; font-size: 0.95rem;">
                    <i data-lucide="check" style="width: 18px; height: 18px;"></i> Upload & Publish E-Book
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropzone = document.getElementById('pdf-dropzone');
        const pdfInput = document.getElementById('pdf-input');
        const promptBox = document.getElementById('dropzone-prompt');
        const selectedBox = document.getElementById('dropzone-selected');
        const fileNameEl = document.getElementById('selected-file-name');
        const fileMetaEl = document.getElementById('selected-file-meta');
        const changeBtn = document.getElementById('btn-change-file');

        const titleInput = document.getElementById('title');
        const totalPagesInput = document.getElementById('total_pages_input');
        const textSampleInput = document.getElementById('text_sample_input');
        const coverInput = document.getElementById('cover-input');
        const coverPreviewBox = document.getElementById('cover-preview-box');

        const form = document.getElementById('upload-form');
        const progressContainer = document.getElementById('upload-progress-container');
        const progressBar = document.getElementById('upload-progress-bar');
        const progressPercent = document.getElementById('upload-percent-text');
        const progressStatus = document.getElementById('upload-status-text');
        const submitBtn = document.getElementById('btn-submit');

        dropzone.addEventListener('click', () => pdfInput.click());
        changeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            pdfInput.click();
        });

        ['dragenter', 'dragover'].forEach(name => {
            dropzone.addEventListener(name, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = 'var(--primary)';
            });
        });

        ['dragleave', 'drop'].forEach(name => {
            dropzone.addEventListener(name, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = 'rgba(124, 58, 237, 0.4)';
            });
        });

        dropzone.addEventListener('drop', (e) => {
            if (e.dataTransfer.files.length > 0) {
                pdfInput.files = e.dataTransfer.files;
                handlePdfSelected(pdfInput.files[0]);
            }
        });

        pdfInput.addEventListener('change', () => {
            if (pdfInput.files.length > 0) {
                handlePdfSelected(pdfInput.files[0]);
            }
        });

        async function handlePdfSelected(file) {
            if (!file || !file.name.toLowerCase().endsWith('.pdf')) {
                alert('Please select a valid PDF file.');
                return;
            }

            promptBox.style.display = 'none';
            selectedBox.style.display = 'block';
            fileNameEl.textContent = file.name;
            const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
            fileMetaEl.textContent = `${sizeMb} MB • Scanning pages...`;

            if (!titleInput.value.trim()) {
                const cleanName = file.name.replace(/\.pdf$/i, '').replace(/[-_]/g, ' ');
                titleInput.value = cleanName;
            }

            try {
                const arrayBuffer = await file.arrayBuffer();
                const typedArray = new Uint8Array(arrayBuffer);
                const pdf = await pdfjsLib.getDocument({ data: typedArray }).promise;
                
                const numPages = pdf.numPages;
                totalPagesInput.value = numPages;
                fileMetaEl.textContent = `${sizeMb} MB • ${numPages} Pages detected`;

                let sampleText = '';
                const pagesToScan = Math.min(numPages, 4);
                for (let p = 1; p <= pagesToScan; p++) {
                    const page = await pdf.getPage(p);
                    const textContent = await page.getTextContent();
                    sampleText += textContent.items.map(i => i.str).join(' ') + '\n';
                }
                textSampleInput.value = sampleText.substring(0, 4000);
            } catch (err) {
                fileMetaEl.textContent = `${sizeMb} MB`;
            }

            if (window.lucide) window.lucide.createIcons();
        }

        coverInput.addEventListener('change', () => {
            if (coverInput.files && coverInput.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    coverPreviewBox.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
                };
                reader.readAsDataURL(coverInput.files[0]);
            }
        });

        form.addEventListener('submit', function () {
            progressContainer.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i data-lucide="loader-2" class="spin" style="width:18px;height:18px;"></i> Uploading...`;

            let progress = 10;
            progressBar.style.width = '10%';
            progressPercent.textContent = '10%';

            const interval = setInterval(() => {
                if (progress < 90) {
                    progress += 10;
                    progressBar.style.width = progress + '%';
                    progressPercent.textContent = progress + '%';
                    if (progress >= 60) {
                        progressStatus.textContent = 'Processing PDF and formulating AI interactive suite...';
                    }
                } else {
                    clearInterval(interval);
                }
            }, 600);
        });
    });
</script>
@endsection
