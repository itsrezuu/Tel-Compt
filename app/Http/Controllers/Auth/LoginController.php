<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /** Satu-satunya domain email yang boleh masuk. */
    private const ALLOWED_DOMAIN = 'student.telkomuniversity.ac.id';

    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower(trim($data['email']));

        // Aturan utama: selain akun mahasiswa Telkom University, akses ditolak (403).
        if (Str::afterLast($email, '@') !== self::ALLOWED_DOMAIN) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $data['password']])) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            // Ganti '/' dengan halaman tujuan setelah login, mis. route('dashboard').
            'redirect' => redirect()->intended('/')->getTargetUrl(),
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
