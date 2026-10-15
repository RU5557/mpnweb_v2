<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (session('is_admin')) {
            return redirect()->route('admin.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Rate limiting: 5 percobaan per menit per IP + username
        $throttleKey = 'admin-login:'.strtolower($request->username).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->withInput();
        }

        // Ambil dari config/admin.php (aman, tidak ada fallback 'admin' di GitHub)
        $adminUser = config('admin.username');
        $adminPass = config('admin.password');

        // Jika config belum di-set di .env, jangan kasih fallback, langsung error 500 yang jelas
        if (empty($adminUser) || empty($adminPass)) {
            Log::critical('ADMIN_USERNAME atau ADMIN_PASSWORD belum di-set di .env');
            abort(500, 'Konfigurasi admin belum diatur. Hubungi administrator sistem.');
        }

        if ($request->username === $adminUser && $request->password === $adminPass) {
            // Sukses - reset rate limiter & buat session
            RateLimiter::clear($throttleKey);
            session(['is_admin' => true]);
            Cache::forget('mpnweb_rolling_text');

            return redirect()->route('admin.index')->with('success', 'Berhasil login sebagai Admin!');
        }

        // Gagal - hit rate limiter & log
        RateLimiter::hit($throttleKey, 60); // lock 60 detik setelah 5 gagal
        Log::warning('Failed admin login attempt', [
            'username' => $request->username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'username' => 'Username atau password admin salah!',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        session()->forget('is_admin');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('penerimaan.dashboard')->with('success', 'Berhasil keluar dari Panel Admin.');
    }
}
