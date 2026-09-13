@extends('layouts.app')

@section('title', 'Edit ' . $ebook->title . ' • Politeknik Besut')

@section('content')
<div style="max-width: 750px; margin: 1.5rem auto 4rem;">
    <!-- Breadcrumb -->
    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">
        <a href="{{ route('library') }}" style="color: var(--primary);">Library</a>
        <span>/</span>
        <a href="{{ route('ebooks.show', $ebook->slug) }}" style="color: var(--primary);">{{ Str::limit($ebook->title, 25) }}</a>
        <span>/</span>
        <span style="color: var(--dark); font-weight: 600;">Edit</span>
    </div>

    <div class="card" style="padding: 2.25rem;">
        <div style="margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="edit-3" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--dark);">Edit E-Book Metadata</h1>
                    <p style="font-size: 0.875rem; color: var(--text-muted);">Update course textbook details and publication visibility</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('ebooks.update', $ebook->slug) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="form-group">
                <label for="title" class="form-label">Book Title <span style="color: var(--danger);">*</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title', $ebook->title) }}" 
                    class="form-control" 
                    required
                >
                @error('title')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Author & Status Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label for="author" class="form-label">Author / Instructor Name</label>
                    <input 
                        type="text" 
                        id="author" 
                        name="author" 
                        value="{{ old('author', $ebook->author) }}" 
                        class="form-control"
                    >
                    @error('author')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Publication Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="published" {{ old('status', $ebook->status) === 'published' ? 'selected' : '' }}>Published (Visible to all students)</option>
                        <option value="draft" {{ old('status', $ebook->status) === 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                        <option value="archived" {{ old('status', $ebook->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    @error('status')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Total Pages -->
            <div class="form-group">
                <label for="total_pages" class="form-label">Total Pages</label>
                <input 
                    type="number" 
                    id="total_pages" 
                    name="total_pages" 
                    value="{{ old('total_pages', $ebook->total_pages) }}" 
                    class="form-control" 
                    min="1"
                >
                @error('total_pages')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Replace Cover Image -->
            <div class="form-group">
                <label for="cover" class="form-label">Replace Cover Image (Optional)</label>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div id="current-cover-box" style="width: 70px; height: 95px; border-radius: var(--radius-sm); border: 1px solid var(--border); overflow: hidden; background: var(--bg-page); flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        @if($ebook->cover_path)
                            <img src="{{ route('ebooks.cover', $ebook->slug) }}" alt="{{ $ebook->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i data-lucide="image" style="width: 20px; height: 20px; color: var(--text-light);"></i>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <input type="file" name="cover" id="cover" accept="image/jpeg,image/png,image/webp" class="form-control">
                        <p class="form-help">Leave empty to keep existing cover.</p>
                    </div>
                </div>
                @error('cover')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Course Description</label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-control" 
                    rows="5"
                >{{ old('description', $ebook->description) }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ebooks.show', $ebook->slug) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
