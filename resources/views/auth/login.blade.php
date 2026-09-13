@extends('layouts.app')

@section('title', 'Sign In • Politeknik Besut FlipBook')

@section('content')
<div style="max-width: 440px; margin: 2.5rem auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                <i data-lucide="lock" style="width: 24px; height: 24px;"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Academic Sign In</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Access your e-book publishing and study tools</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', 'admin@polibesut.edu.my') }}" 
                    class="form-control" 
                    required 
                    autofocus
                    placeholder="lecturer@polibesut.edu.my"
                >
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    value="password123"
                    required
                    placeholder="••••••••"
                >
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); cursor: pointer;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember this browser</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem;">
                <i data-lucide="log-in" style="width: 18px; height: 18px;"></i> Sign In
            </button>
        </form>

        <!-- Quick Demo Credentials Box -->
        <div style="margin-top: 1.5rem; padding: 0.9rem 1rem; background: var(--border-light); border-radius: var(--radius-sm); border: 1px dashed #cbd5e1; font-size: 0.825rem; color: var(--text-muted);">
            <div style="font-weight: 700; color: var(--dark); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">
                <i data-lucide="key" style="width: 14px; height: 14px; color: var(--primary);"></i> Default Academic Admin Account:
            </div>
            <div>Email: <strong style="color: var(--text-main);">admin@polibesut.edu.my</strong></div>
            <div>Password: <strong style="color: var(--text-main);">password123</strong></div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
            Don't have an account? 
            <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 600;">Register here</a>
        </div>
    </div>
</div>
@endsection
