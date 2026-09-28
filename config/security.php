<?php

return [

    'headers' => [
        'x-content-type-options' => 'nosniff',
        'x-frame-options' => 'SAMEORIGIN',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'permissions-policy' => 'geolocation=(), microphone=(), camera=(), payment=()',
    ],

    'content_security_policy' => [
        "default-src 'self'",

        // 'unsafe-inline' + 'unsafe-eval' wajib: Tailwind CDN memakai eval,
        // Alpine dan Filament menyuntik <script> inline.
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net",

        // Bunny Fonts adalah font provider default Filament (HasFont::$fontProvider),
        // jadi host stylesheet-nya wajib ada di style-src - hanya font-src tidak cukup.
        "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.bunny.net",

        "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://fonts.bunny.net",

        // Artikel boleh memakai foto dari luar (featured_image/OG image bisa URL eksternal).
        "img-src 'self' data: blob: https: http:",

        "connect-src 'self'",

        "frame-src 'self' https://www.youtube-nocookie.com https://www.youtube.com",

        "object-src 'none'",

        "base-uri 'self'",

        "form-action 'self'",

        "frame-ancestors 'self'",
    ],

];
