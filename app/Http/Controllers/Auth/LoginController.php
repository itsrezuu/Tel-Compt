<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const DEFAULT_DOMAIN = 'student.telkomuniversity.ac.id';

    /** @var list<string> */
    private const ALLOWED_DOMAINS = [
        self::DEFAULT_DOMAIN,
        'telkomuniversity.ac.id',
    ];

    public function show(): View
    {
        return view('auth.login', [
            'allowedDomains' => self::ALLOWED_DOMAINS,
            'defaultDomain' => self::DEFAULT_DOMAIN,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower(trim($data['email']));
        $domain = Str::afterLast($email, '@');

        if (! in_array($domain, self::ALLOWED_DOMAINS, true)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $data['password']])) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 422);
        }

        $user = Auth::user();
        $redirect = match ($user?->role) {
            'mahasiswa' => route('bimbingan.index'),
            'admin' => route('admin-hima.notifikasi.index'),
            'dosen', 'kaprodi' => route('dosen.bimbingan.index'),
            default => null,
        };

        if ($redirect === null) {
            Auth::logout();

            return response()->json(['message' => 'Peran akun tidak dapat masuk.'], 403);
        }

        $request->session()->regenerate();

        return response()->json(['redirect' => $redirect]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
