<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke()
    {
        $checks = [
            'db'    => $this->db(),
            'cache' => $this->cache(),
        ];

        $ok = collect($checks)->every(fn ($v) => $v === 'ok');

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'checks' => $checks,
            'time'   => now()->toIso8601String(),
        ], $ok ? 200 : 503);
    }

    private function db(): string
    {
        try {
            DB::connection()->getPdo();
            return 'ok';
        } catch (\Throwable $e) {
            return 'fail';
        }
    }

    private function cache(): string
    {
        try {
            Cache::store()->put('health:ping', now()->timestamp, 5);
            return Cache::store()->get('health:ping') ? 'ok' : 'fail';
        } catch (\Throwable $e) {
            return 'fail';
        }
    }
}