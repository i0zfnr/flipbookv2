@extends('layouts.app')

@section('title', 'About • Politeknik Besut Academic Platform')

@section('content')
<div style="max-width: 1000px; margin: 1.5rem auto 5rem;">
    <!-- Hero Banner -->
    <div style="text-align: center; margin-bottom: 3.5rem;">
        <div class="hero-pill">
            <i data-lucide="sparkles" style="width: 14px; height: 14px; color: var(--secondary);"></i>
            <span>Story & Vision • Politeknik Besut</span>
        </div>

        <h1 style="font-size: 2.75rem; font-weight: 800; color: var(--dark); line-height: 1.25; margin-bottom: 1rem;">
            Academic E-Books Powered by <br>
            <span class="gradient-text">Google Gemini AI</span> & 3D Page Turn Physics
        </h1>

        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 720px; margin: 0 auto; line-height: 1.6;">
            An innovative educational platform developed for <strong style="color: var(--dark);">Politeknik Besut, Terengganu</strong>, combining tactile 3D flipbook reading with curriculum-grade AI tutoring for technical and engineering coursework.
        </p>
    </div>

    <!-- Project Team Cards -->
    <div style="margin-bottom: 4rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.65rem; font-weight: 800; color: var(--dark);">Lecturer Project Team</h2>
            <p style="font-size: 0.925rem; color: var(--text-muted);">Academic researchers & project coordinators at Politeknik Besut</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Lecturer 1 -->
            <div class="card" style="padding: 2rem; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i data-lucide="graduation-cap" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark);">Farah Hayati Binti Che Lah</h3>
                <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600; margin-top: 0.25rem;">Project Team Lecturer</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <i data-lucide="mail" style="width: 14px; height: 14px;"></i> farah@polibesut.edu.my
                </div>
                <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">Politeknik Besut, Terengganu</div>
            </div>

            <!-- Lecturer 2 -->
            <div class="card" style="padding: 2rem; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); color: var(--secondary); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i data-lucide="graduation-cap" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark);">Wan Izyani Binti Wan Jusoh</h3>
                <div style="font-size: 0.85rem; color: var(--secondary); font-weight: 600; margin-top: 0.25rem;">Project Team Lecturer</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <i data-lucide="mail" style="width: 14px; height: 14px;"></i> izyani@polibesut.edu.my
                </div>
                <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">Politeknik Besut, Terengganu</div>
            </div>

            <!-- Lecturer 3 -->
            <div class="card" style="padding: 2rem; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(6, 182, 212, 0.1); color: var(--accent); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                    <i data-lucide="graduation-cap" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark);">Wee Siew Ping</h3>
                <div style="font-size: 0.85rem; color: var(--accent); font-weight: 600; margin-top: 0.25rem;">Project Team Lecturer</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <i data-lucide="mail" style="width: 14px; height: 14px;"></i> wee@polibesut.edu.my
                </div>
                <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">Politeknik Besut, Terengganu</div>
            </div>
        </div>
    </div>

    <!-- Core Technology Pillars -->
    <div class="card" style="padding: 2.5rem; margin-bottom: 4rem; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark); margin-bottom: 1.75rem; text-align: center;">
            Platform Capabilities & Innovation
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--primary); font-size: 1.05rem; margin-bottom: 0.5rem;">
                    <i data-lucide="cpu" style="width: 20px; height: 20px;"></i>
                    <span>Google Gemini 3.6/3.7 Flash AI</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                    Curriculum-grade assessment generation that scans PDF textbook text and creates analytical multiple-choice quizzes with detailed step-by-step explanations.
                </p>
            </div>

            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--secondary); font-size: 1.05rem; margin-bottom: 0.5rem;">
                    <i data-lucide="gamepad-2" style="width: 20px; height: 20px;"></i>
                    <span>Gamified Terminology Match</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                    Interactive 3D flashcards with flip physics and an arcade Speed Match Game that tests technical term recall directly embedded within chapter pages.
                </p>
            </div>

            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--accent); font-size: 1.05rem; margin-bottom: 0.5rem;">
                    <i data-lucide="bot" style="width: 20px; height: 20px;"></i>
                    <span>Aura AI Academic Tutor</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                    A personalized study companion that operates in real-time inside the 3D reader and in a dedicated study room, understanding the exact page and subject being read.
                </p>
            </div>

            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--success); font-size: 1.05rem; margin-bottom: 0.5rem;">
                    <i data-lucide="book-open" style="width: 20px; height: 20px;"></i>
                    <span>Tactile 3D Page Physics</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                    Realistic page curling, dynamic shadows, keyboard arrow flipping, zoom magnification, and full in-text PDF search with zero distraction.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
