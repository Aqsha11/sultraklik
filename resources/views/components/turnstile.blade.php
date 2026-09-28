@php
    /**
     * Widget Cloudflare Turnstile.
     *
     * Form HTML biasa (komentar): token dikirim sendiri oleh widget sebagai
     * input `cf-turnstile-response`, jadi tidak ada Livewire yang terlibat.
     *
     * Halaman login Filament: form-nya milik Livewire dan token harus masuk
     * ke state Livewire lewat atribut $livewire (nama property).
     *
     *   <x-turnstile action="comment" />
     *   <x-turnstile action="login" livewire="turnstileToken" />
     */
    $turnstileAction = $action ?? \App\Support\Turnstile::ACTION_COMMENT;
    $turnstileTokenProperty = $livewire ?? null;
@endphp

@if(\App\Support\Turnstile::enabled())
    <div class="sl-turnstile-host my-4">
        @if(filled($turnstileTokenProperty))
            {{-- wire:ignore supaya morph Livewire tidak pernah menyentuh DOM
                 widget; Alpine dan $wire tetap bekerja normal. --}}
            <div
                wire:ignore
                x-data
                x-on:sl-turnstile-token.window="$wire.set(@js($turnstileTokenProperty), $event.detail)"
                x-on:sl-turnstile-clear.window="$wire.set(@js($turnstileTokenProperty), null)"
                x-on:sl-turnstile-failed.window="$wire.set(@js($turnstileTokenProperty), null); window.turnstile?.reset()"
            >
                <div
                    class="cf-turnstile"
                    data-sitekey="{{ \App\Support\Turnstile::siteKey() }}"
                    data-action="{{ $turnstileAction }}"
                    data-theme="auto"
                    data-callback="slTurnstileCallback"
                    data-error-callback="slTurnstileErrorCallback"
                ></div>
            </div>

            {{-- Callback Turnstile hanya bisa dipanggil sebagai fungsi global,
                 jadi token diteruskan lewat CustomEvent ke Alpine, lalu ke
                 state Livewire. --}}
            <script>
                window.slTurnstileCallback = function (token) {
                    window.dispatchEvent(new CustomEvent('sl-turnstile-token', { detail: token }));
                };

                window.slTurnstileErrorCallback = function () {
                    window.dispatchEvent(new CustomEvent('sl-turnstile-clear'));
                };
            </script>
        @else
            <div
                class="cf-turnstile"
                data-sitekey="{{ \App\Support\Turnstile::siteKey() }}"
                data-action="{{ $turnstileAction }}"
                data-theme="auto"
            ></div>
        @endif

        {{-- Pemuat script. Guard window.turnstile membuat aman dijalankan ulang,
             dan penempatannya sesudah div memastikan widget sudah ada di DOM
             ketika api.js memindai halaman. --}}
        <script>
            if (!window.turnstile) {
                var slTurnstileScript = document.createElement('script');
                slTurnstileScript.src = @js(\App\Support\Turnstile::scriptUrl());
                slTurnstileScript.async = true;
                slTurnstileScript.defer = true;
                document.head.appendChild(slTurnstileScript);
            }
        </script>
    </div>
@endif
