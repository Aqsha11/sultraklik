@php
    $loginPrimary = \App\Models\Setting::get('theme.primary_color', '#dc2626');
    $loginPalette = \App\Support\ColorPalette::shades((string) $loginPrimary);
    $loginSiteName = \App\Models\Setting::get('general.name', 'SULTRAKLIK');
    $loginTagline = \App\Models\Setting::get('general.tagline');
    $loginLogoPath = \App\Models\Setting::get('theme.logo');
    $loginHasLogo = filled($loginLogoPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($loginLogoPath);
    $loginFeatures = [
        ['heroicon-m-pencil-square', 'Tulis & terbitkan artikel'],
        ['heroicon-m-squares-2x2', 'Atur kategori, wilayah, dan tag'],
        ['heroicon-m-chart-bar', 'Pantau performa berita'],
    ];
@endphp

<div
    class="sl-page"
    x-data="{ caps: false }"
    style="--sl-50: {{ $loginPalette[50] }}; --sl-100: {{ $loginPalette[100] }}; --sl-600: {{ $loginPalette[600] }}; --sl-700: {{ $loginPalette[700] }}; --sl-800: {{ $loginPalette[800] }}; --sl-900: {{ $loginPalette[900] }}; --sl-950: {{ $loginPalette[950] }};"
>
    <style>
        /* Hilangkan chrome kartu bawaan Filament; seluruh tampilan digantikan di bawah. */
        .fi-simple-layout {
            display: block;
            min-height: 100vh;
        }

        .fi-simple-main-ctn,
        .fi-simple-main {
            display: contents;
        }

        .sl-page {
            min-height: 100vh;
            background-color: #f1f5f9;
            background-image:
                radial-gradient(900px 480px at 100% 0%, var(--sl-100), transparent 60%),
                radial-gradient(700px 420px at 0% 100%, var(--sl-50), transparent 55%);
        }

        .sl-grid {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 1fr;
        }

        .sl-aside {
            display: none;
            position: relative;
            overflow: hidden;
            padding: 3rem;
            color: #fff;
            background-image: linear-gradient(155deg, var(--sl-800) 0%, var(--sl-950) 58%, #0a0a12 100%);
        }

        .sl-aside::before,
        .sl-aside::after {
            content: '';
            position: absolute;
            border-radius: 9999px;
            filter: blur(70px);
            opacity: 0.5;
            pointer-events: none;
        }

        .sl-aside::before {
            top: -6rem;
            right: -5rem;
            width: 22rem;
            height: 22rem;
            background-color: var(--sl-600);
        }

        .sl-aside::after {
            bottom: -8rem;
            left: -6rem;
            width: 20rem;
            height: 20rem;
            background-color: var(--sl-700);
            opacity: 0.4;
        }

        .sl-aside-inner {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100%;
            flex-direction: column;
            justify-content: space-between;
            gap: 2.5rem;
            animation: sl-rise 0.5s ease-out both;
        }

        .sl-brand {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .sl-brand-box {
            display: flex;
            height: 3.5rem;
            width: 3.5rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background-color: #fff;
            box-shadow: 0 10px 25px -12px rgb(0 0 0 / 0.6);
        }

        .sl-brand-name {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .sl-brand-tagline {
            margin-top: 0.25rem;
            font-size: 0.8125rem;
            color: rgb(255 255 255 / 0.7);
        }

        .sl-pitch {
            margin-top: 3.5rem;
        }

        .sl-pitch h2 {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .sl-pitch p {
            margin-top: 1rem;
            max-width: 26rem;
            font-size: 0.9375rem;
            line-height: 1.7;
            color: rgb(255 255 255 / 0.72);
        }

        .sl-feats {
            margin-top: 2rem;
            display: grid;
            gap: 0.75rem;
        }

        .sl-feat {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            color: rgb(255 255 255 / 0.9);
        }

        .sl-feat-icon {
            display: flex;
            height: 1.75rem;
            width: 1.75rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background-color: rgb(255 255 255 / 0.14);
        }

        .sl-aside-foot {
            font-size: 0.75rem;
            color: rgb(255 255 255 / 0.5);
        }

        .sl-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .sl-card {
            width: 100%;
            max-width: 26rem;
            border-radius: 1.25rem;
            background-color: #fff;
            padding: 1.75rem 1.5rem;
            box-shadow: 0 25px 60px -25px rgb(15 23 42 / 0.35);
            outline: 1px solid rgb(15 23 42 / 0.06);
            animation: sl-rise 0.45s ease-out both;
        }

        .sl-card-head {
            text-align: center;
        }

        .sl-mobile-brand {
            margin: 0 auto 1.5rem;
            display: flex;
            width: fit-content;
            align-items: center;
            gap: 0.625rem;
        }

        .sl-mobile-brand .sl-brand-box {
            height: 3rem;
            width: 3rem;
            border-radius: 0.875rem;
            box-shadow: 0 8px 20px -12px rgb(15 23 42 / 0.45);
        }

        .sl-mobile-brand .sl-brand-name {
            color: #0f172a;
            font-size: 1.125rem;
        }

        .sl-mobile-brand .sl-brand-tagline {
            color: #64748b;
        }

        .sl-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .sl-subtitle {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #64748b;
        }

        .sl-form {
            margin-top: 1.75rem;
        }

        .sl-form .fi-fo-field-wrp-label span {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
        }

        .sl-form .fi-input-wrp {
            border-radius: 0.875rem;
            background-color: #f8fafc;
            box-shadow: inset 0 0 0 1px #e2e8f0;
        }

        .sl-form .fi-input-wrp:focus-within {
            background-color: #fff;
            box-shadow:
                inset 0 0 0 1.5px var(--sl-600),
                0 0 0 4px color-mix(in srgb, var(--sl-600) 18%, transparent);
        }

        .sl-form .fi-input-wrp.fi-invalid {
            box-shadow: inset 0 0 0 1.5px #e11d48;
        }

        .sl-form .fi-input {
            height: 3rem;
            font-size: 0.9375rem;
            background-color: transparent;
        }

        .sl-form .fi-input-wrp-icon {
            font-size: 0.9375rem;
        }

        .sl-form input[type='checkbox'] {
            height: 1.05rem;
            width: 1.05rem;
            border-radius: 0.3rem;
            accent-color: var(--sl-600);
        }

        .sl-form .fi-form-actions .fi-btn {
            width: 100%;
            height: 3rem;
            border-radius: 0.875rem;
            font-weight: 700;
            box-shadow: 0 12px 24px -14px var(--sl-700);
        }

        .sl-caps {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            border-radius: 0.75rem;
            background-color: #fffbeb;
            padding: 0.625rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #92400e;
        }

        .sl-help {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
            font-size: 0.8125rem;
            color: #64748b;
        }

        .sl-help a {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-weight: 600;
            color: var(--sl-700);
            text-decoration: none;
        }

        .sl-help a:hover {
            text-decoration: underline;
        }

        @keyframes sl-rise {
            from {
                opacity: 0;
                transform: translateY(0.75rem);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (min-width: 640px) {
            .sl-main {
                padding: 2.5rem;
            }

            .sl-card {
                padding: 2.5rem 2.25rem;
            }
        }

        @media (min-width: 1024px) {
            .sl-grid {
                grid-template-columns: 1.05fr 1fr;
            }

            .sl-aside {
                display: flex;
            }

            .sl-card {
                animation-delay: 0.08s;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .sl-aside-inner,
            .sl-card {
                animation: none;
            }
        }

        .dark .sl-page {
            background-color: #020617;
        }

        .dark .sl-card {
            background-color: #0f172a;
            outline-color: rgb(255 255 255 / 0.1);
        }

        .dark .sl-title {
            color: #fff;
        }

        .dark .sl-form .fi-input-wrp {
            background-color: rgb(255 255 255 / 0.04);
            box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.12);
        }
    </style>

    <div class="sl-grid">
        <aside class="sl-aside">
            <div class="sl-aside-inner">
                <div>
                    <div class="sl-brand">
                        <div class="sl-brand-box">
                            @if($loginHasLogo)
                                <img src="{{ Storage::disk('public')->url($loginLogoPath) }}" alt="{{ $loginSiteName }}" class="h-10 w-auto max-w-[10rem] object-contain">
                            @else
                                <span class="text-lg font-black tracking-tight text-gray-900">{{ \Illuminate\Support\Str::limit($loginSiteName, 12, '') }}</span>
                            @endif
                        </div>
                        <div>
                            <p class="sl-brand-name">{{ $loginSiteName }}</p>
                            @if(filled($loginTagline))
                                <p class="sl-brand-tagline">{{ $loginTagline }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="sl-pitch">
                        <h2>Kelola redaksi Anda dengan lebih cepat.</h2>
                        <p>
                            Tulis berita, atur kategori &amp; wilayah, pantau statistik pembaca, serta kelola iklan
                            dari satu dashboard yang ringkas.
                        </p>

                        <div class="sl-feats">
                            @foreach($loginFeatures as [$icon, $label])
                                <div class="sl-feat">
                                    <span class="sl-feat-icon">
                                        <x-dynamic-component :component="$icon" class="h-4 w-4" />
                                    </span>
                                    <span>{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p class="sl-aside-foot">&copy; {{ now()->year }} {{ $loginSiteName }}. Seluruh hak cipta dilindungi.</p>
            </div>
        </aside>

        <main class="sl-main">
            <div class="sl-card">
                <div class="sl-mobile-brand lg:hidden">
                    <div class="sl-brand-box">
                        @if($loginHasLogo)
                            <img src="{{ Storage::disk('public')->url($loginLogoPath) }}" alt="{{ $loginSiteName }}" class="h-10 w-auto max-w-[10rem] object-contain">
                        @else
                            <span class="text-base font-black tracking-tight text-gray-900">{{ \Illuminate\Support\Str::limit($loginSiteName, 12, '') }}</span>
                        @endif
                    </div>
                    <div class="text-left">
                        <p class="sl-brand-name">{{ $loginSiteName }}</p>
                        @if(filled($loginTagline))
                            <p class="sl-brand-tagline">{{ $loginTagline }}</p>
                        @endif
                    </div>
                </div>

                <div class="sl-card-head">
                    <h1 class="sl-title">Masuk ke Dashboard</h1>
                    <p class="sl-subtitle">Gunakan email dan password akun redaksi Anda untuk melanjutkan.</p>
                </div>

                <x-filament-panels::form id="form" wire:submit="authenticate" class="sl-form">
                    {{ $this->form }}

                    <div x-show="caps" x-cloak class="sl-caps">
                        <x-heroicon-m-exclamation-triangle class="h-4 w-4" />
                        Caps Lock sedang aktif. Nonaktifkan agar password tersimpan dengan benar.
                    </div>

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>

                <div class="sl-help">
                    <a href="{{ url('/') }}" class="lg:hidden">
                        <x-heroicon-m-arrow-left class="h-4 w-4" />
                        Kembali ke beranda
                    </a>
                    <p>Kesulitan masuk? Hubungi super admin redaksi.</p>
                </div>
            </div>
        </main>
    </div>
</div>
