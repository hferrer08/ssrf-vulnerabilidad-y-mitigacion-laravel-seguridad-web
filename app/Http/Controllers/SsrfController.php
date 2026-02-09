<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SsrfController extends Controller
{
    public function index()
    {
        return view('ssrf.index');
    }

    // ❌ VULNERABLE
    public function vulnerable(Request $request)
    {
        $url = $request->input('url');

        $response = Http::get($url);

        return response()->json([
            'requested_url' => $url,
            'response' => $response->body()
        ]);
    }

    // ✅ SEGURO 
    public function secure(Request $request)
    {
        return response()->json([
            'message' => 'Pendiente de implementar versión segura'
        ]);
    }
}
