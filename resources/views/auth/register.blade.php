@extends('layouts.app')

@section('title', 'Register • Politeknik Besut FlipBook')

@section('content')
<div style="max-width: 460px; margin: 2.5rem auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                <i data-lucide="user-plus" style="width: 24px; height: 24px;"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark); margin-bottom: 0.25rem;">Create Account</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Join the Politeknik Besut Academic Platform</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    class="form-control" 
                    required 
                    autofocus
                    placeholder="e.g. Ahmad bin Razak"
                >
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    class="form-control" 
                    required
                    placeholder="student@polibesut.edu.my"
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
                    required
                    placeholder="At least 8 characters"
                >
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-control" 
                    required
                    placeholder="Re-enter your password"
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; margin-top: 0.5rem;">
                <i data-lucide="check" style="width: 18px; height: 18px;"></i> Create Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
            Already have an account? 
            <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600;">Sign in here</a>
        </div>
    </div>
</div>
@endsection
