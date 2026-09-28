<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as BaseTrustProxies;
use Illuminate\Http\Request;

class TrustProxies extends BaseTrustProxies
{
    /**
     * The trusted proxies for the application.
     *
     * Sengaja dibiarkan null: kalau null, class induk jatuh ke
     * config('trustedproxy.proxies'), yaitu rentang IP Cloudflare.
     * Nilai di situ kosong secara default, jadi development lokal tanpa
     * reverse proxy tidak salah membaca X-Forwarded-For.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = null;

    /**
     * The trusted proxies headers for the application.
     *
     * Sama dengan default Laravel, ditulis eksplisit agar intent jelas.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX;
}
