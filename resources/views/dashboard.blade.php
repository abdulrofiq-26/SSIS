@extends('layouts.master')
@section('title', 'Dashboard')

@section('style')
<style>
    .dashboard-page {
        --dashboard-accent: #4154f1;
        --dashboard-accent-dark: #3040d0;
        --dashboard-heading: #0d1c6b;
        --dashboard-text: #4a5568;
        --dashboard-surface: rgba(255, 255, 255, .92);
        font-family: 'Inter', 'Poppins', 'Public Sans', system-ui, sans-serif;
        color: var(--dashboard-text);
    }
    .dashboard-page h1,
    .dashboard-page h2,
    .dashboard-page h3,
    .dashboard-page h4,
    .dashboard-page h5,
    .dashboard-page h6 {
        color: var(--dashboard-heading);
        font-family: 'Inter', 'Poppins', 'Public Sans', system-ui, sans-serif;
        letter-spacing: -.025em;
    }
    .dashboard-welcome {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        min-height: 230px;
        margin-bottom: 24px;
        padding: clamp(24px, 4vw, 42px);
        border: 1px solid rgba(65, 84, 241, .14);
        border-radius: 22px;
        background: linear-gradient(120deg, rgba(255,255,255,.98), rgba(232,238,255,.92));
        box-shadow: 0 14px 42px rgba(65, 84, 241, .10);
    }
    .dashboard-welcome::before,
    .dashboard-welcome::after {
        position: absolute;
        content: '';
        pointer-events: none;
        border-radius: 50%;
        background: rgba(123, 145, 255, .12);
    }
    .dashboard-welcome::before { width: 300px; height: 300px; top: -190px; right: 10%; }
    .dashboard-welcome::after { width: 220px; height: 220px; right: -90px; bottom: -150px; }
    .dashboard-welcome-copy { position: relative; z-index: 1; max-width: 680px; }
    .dashboard-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 13px;
        padding: 7px 12px;
        border: 1px solid rgba(65, 84, 241, .18);
        border-radius: 999px;
        color: var(--dashboard-accent);
        background: rgba(65, 84, 241, .07);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .dashboard-welcome h1 { margin: 0 0 10px; font-size: clamp(1.8rem, 3vw, 2.7rem); font-weight: 800; line-height: 1.15; }
    .dashboard-welcome p { max-width: 580px; margin: 0; color: var(--dashboard-text); font-size: 1rem; line-height: 1.7; }
    .dashboard-welcome-icon {
        position: relative;
        z-index: 1;
        display: grid;
        flex: 0 0 auto;
        width: clamp(100px, 15vw, 156px);
        aspect-ratio: 1;
        place-items: center;
        border: 1px solid rgba(65, 84, 241, .16);
        border-radius: 36px;
        color: #fff;
        background: linear-gradient(145deg, #6478ff, #4154f1 65%, #3040d0);
        box-shadow: 0 16px 32px rgba(65, 84, 241, .24);
        transform: rotate(4deg);
    }
    .dashboard-welcome-icon i { font-size: clamp(3rem, 6vw, 5rem); transform: rotate(-4deg); }
    .dashboard-section-title { display: flex; align-items: center; gap: 10px; margin: 0 0 15px; font-size: 1.08rem; font-weight: 750; }
    .dashboard-section-title i { color: var(--dashboard-accent); }
    .dashboard-role-card,
    .dashboard-shortcut {
        height: 100%;
        border: 1px solid rgba(65, 84, 241, .12);
        border-radius: 16px;
        background: var(--dashboard-surface);
        box-shadow: 0 8px 26px rgba(41, 61, 130, .07);
    }
    .dashboard-role-card { padding: 20px; }
    .dashboard-role-label { display: block; margin-bottom: 12px; color: #71809b; font-size: .82rem; font-weight: 600; }
    .dashboard-role-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .dashboard-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        color: #fff;
        font-size: .78rem;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(20, 30, 70, .12);
    }
    .dashboard-no-role { margin: 0; color: #a12635; font-size: .9rem; line-height: 1.6; }
    .dashboard-shortcut {
        display: flex;
        align-items: center;
        gap: 15px;
        min-height: 94px;
        padding: 17px;
        color: inherit;
        text-decoration: none;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .dashboard-shortcut:hover { transform: translateY(-3px); border-color: rgba(65, 84, 241, .38); box-shadow: 0 14px 28px rgba(65, 84, 241, .13); color: inherit; }
    .dashboard-shortcut-icon {
        display: grid;
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 14px;
        color: var(--dashboard-accent);
        background: rgba(65, 84, 241, .1);
        font-size: 1.35rem;
        transition: color .2s ease, background .2s ease;
    }
    .dashboard-shortcut:hover .dashboard-shortcut-icon { color: #fff; background: var(--dashboard-accent); }
    .dashboard-shortcut-title { display: block; margin-bottom: 3px; color: var(--dashboard-heading); font-size: .94rem; font-weight: 700; }
    .dashboard-shortcut-description { display: block; color: #71809b; font-size: .78rem; line-height: 1.45; }
    html.dark-mode .dashboard-page {
        --dashboard-heading: #e8eeff;
        --dashboard-text: #a0aec0;
        --dashboard-surface: rgba(30, 45, 74, .88);
    }
    html.dark-mode .dashboard-welcome { border-color: rgba(107,124,255,.24); background: linear-gradient(120deg, rgba(21,31,54,.98), rgba(30,45,74,.92)); box-shadow: 0 14px 42px rgba(0,0,0,.22); }
    html.dark-mode .dashboard-welcome::before,
    html.dark-mode .dashboard-welcome::after { background: rgba(107,124,255,.11); }
    html.dark-mode .dashboard-eyebrow { color: #c7ceff; border-color: rgba(107,124,255,.3); background: rgba(107,124,255,.13); }
    html.dark-mode .dashboard-role-card,
    html.dark-mode .dashboard-shortcut { border-color: rgba(107,124,255,.2); box-shadow: 0 8px 26px rgba(0,0,0,.16); }
    html.dark-mode .dashboard-role-label,
    html.dark-mode .dashboard-shortcut-description { color: #a0aec0; }
    html.dark-mode .dashboard-shortcut-icon { color: #c7ceff; background: rgba(107,124,255,.17); }
    html.dark-mode .dashboard-shortcut:hover .dashboard-shortcut-icon { color: #fff; background: #5367ef; }
    html.dark-mode .dashboard-shortcut:hover { border-color: rgba(107,124,255,.5); box-shadow: 0 14px 28px rgba(0,0,0,.26); }
    html.dark-mode .dashboard-no-role { color: #ffb8c0; }
    @media (max-width: 767.98px) {
        .dashboard-welcome { min-height: 0; padding: 24px; }
        .dashboard-welcome-icon { width: 88px; border-radius: 24px; }
        .dashboard-welcome-icon i { font-size: 2.8rem; }
    }
    @media (max-width: 575.98px) {
        .dashboard-welcome { align-items: flex-start; gap: 14px; padding: 20px; border-radius: 17px; }
        .dashboard-welcome-icon { width: 58px; border-radius: 18px; }
        .dashboard-welcome-icon i { font-size: 2rem; }
        .dashboard-welcome h1 { font-size: 1.65rem; }
        .dashboard-welcome p { font-size: .9rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .dashboard-shortcut, .dashboard-shortcut-icon { transition: none; }
    }
</style>
@endsection
@section('content')
<div class="dashboard-page">
    <section class="dashboard-welcome" aria-labelledby="dashboard-welcome-title">
        <div class="dashboard-welcome-copy">
            <span class="dashboard-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Sparepart Stock Information System</span>
            <h1 id="dashboard-welcome-title">Hi, {{ Auth::user()->name }}!</h1>
            <p>{{ __('You\'re logged in!') }} Manage your sparepart information and quickly find the parts you need.</p>
        </div>
        <div class="dashboard-welcome-icon" aria-hidden="true"><i class="bi bi-boxes"></i></div>
    </section>

    <div class="row g-3 g-xl-4 align-items-stretch">
        <div class="col-12 col-xl-4">
            <section class="dashboard-role-card" aria-labelledby="dashboard-access-title">
                <h2 class="dashboard-section-title" id="dashboard-access-title"><i class="bi bi-shield-check" aria-hidden="true"></i> Access rights</h2>
                @if (Auth::user()->roles->count() === 0)
                    <p class="dashboard-no-role"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>Sorry, you don't have access rights. Please contact an administrator.</p>
                @else
                    <span class="dashboard-role-label">Your assigned role{{ Auth::user()->roles->count() > 1 ? 's' : '' }}</span>
                    <div class="dashboard-role-list">
                        @foreach (Auth::user()->roles as $role)
                            <span class="dashboard-role-badge" style="background-color: {{ $role->color ?: '#4154f1' }}"><i class="bi bi-person-badge" aria-hidden="true"></i>{{ $role->name }}</span>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
        <div class="col-12 col-xl-8">
            <h2 class="dashboard-section-title"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Quick access</h2>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <a href="{{ route('search.index') }}" class="dashboard-shortcut">
                        <span class="dashboard-shortcut-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                        <span><span class="dashboard-shortcut-title">Search spareparts</span><span class="dashboard-shortcut-description">Find parts by text or image</span></span>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('spareparts.index') }}" class="dashboard-shortcut">
                        <span class="dashboard-shortcut-icon"><i class="bi bi-gear-wide-connected" aria-hidden="true"></i></span>
                        <span><span class="dashboard-shortcut-title">Spareparts</span><span class="dashboard-shortcut-description">Manage sparepart inventory</span></span>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('brands.index') }}" class="dashboard-shortcut">
                        <span class="dashboard-shortcut-icon"><i class="bi bi-tags-fill" aria-hidden="true"></i></span>
                        <span><span class="dashboard-shortcut-title">Brands</span><span class="dashboard-shortcut-description">Manage sparepart brands</span></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
