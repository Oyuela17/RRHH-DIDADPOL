<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    // ✅ Acepta cualquier proxy
    protected $proxies = '*';

    // ✅ Usa todos los headers estándar de proxy
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
