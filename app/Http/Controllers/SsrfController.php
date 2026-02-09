<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsrfController extends Controller
{
    public function index()
    {
        return view('ssrf.index');
    }

    // ❌ VULNERABLE (SSRF)
    public function vulnerable(Request $request)
    {
        $url = $request->input('url');

        try {
            $response = Http::timeout(5)->get($url);

            return response()->json([
                'requested_url' => $url,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'requested_url' => $url,
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    // ✅ SEGURO (mitigación SSRF)
    public function secure(Request $request)
    {
        $url = trim((string) $request->input('url', ''));

        // 1) Validar URL
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'URL inválida'], 422);
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';

        // 2) Permitir solo http/https
        if (!in_array($scheme, ['http', 'https'], true)) {
            return response()->json(['error' => 'Solo se permite http/https'], 422);
        }

        if ($host === '') {
            return response()->json(['error' => 'Host inválido'], 422);
        }

        // 3) Bloqueo directo de localhost
        if (strtolower($host) === 'localhost') {
            return response()->json(['error' => 'Destino no permitido (localhost)'], 403);
        }

        // 4) Resolver DNS a IPs
        $ips = $this->resolveHostToIps($host);

        if (empty($ips)) {
            return response()->json(['error' => 'No se pudo resolver el host'], 422);
        }

        // 5) Bloquear IPs privadas / loopback
        foreach ($ips as $ip) {
            if ($this->isPrivateOrLocalIp($ip)) {
                return response()->json([
                    'error' => 'Destino no permitido (IP privada o local)',
                    'resolved_ip' => $ip
                ], 403);
            }
        }

        // 6) Request segura
        try {
            $response = Http::timeout(5)
                ->withoutRedirecting() // evita bypass por redirect
                ->get($url);

            return response()->json([
                'requested_url' => $url,
                'status' => $response->status(),
                'response' => Str::limit($response->body(), 2000)
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'requested_url' => $url,
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    // =========================
    // Helpers
    // =========================

    private function resolveHostToIps(string $host): array
    {
        // Si ya es IP
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $records = @dns_get_record($host, DNS_A);
        if (!$records) {
            return [];
        }

        $ips = [];
        foreach ($records as $r) {
            if (!empty($r['ip'])) {
                $ips[] = $r['ip'];
            }
        }

        return array_values(array_unique($ips));
    }

    private function isPrivateOrLocalIp(string $ip): bool
    {
        // Bloqueo por flags PHP
        if (!filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        )) {
            return true;
        }

        // Bloqueos explícitos (claros para la memoria)
        if (str_starts_with($ip, '127.')) return true;          // loopback
        if (str_starts_with($ip, '169.254.')) return true;      // link-local
        if (str_starts_with($ip, '10.')) return true;           // private
        if (str_starts_with($ip, '192.168.')) return true;      // private
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) return true;

        return false;
    }
}
