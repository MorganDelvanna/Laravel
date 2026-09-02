<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\BlackList;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return match (Auth::user()->role) {
                'admin' => redirect()->route('admin.emailList'),
                'calendar' => redirect()->route('calendar.admin'),
                'news' => redirect()->route('news.admin'),
                default => redirect()->route('home'),
            };
        } else {
            // Authentication failed...
            Log::warning('Failed admin login attempt for username: ' . $credentials['username']);
            return redirect()->back()->withErrors(['login' => 'Invalid credentials.']);
        }
    }

    public function emailList()
    {
        $applicants = Applicant::query()
            ->whereNotNull('transaction_id')
            ->where('emailed', false)
            ->get()
            ->each(function (Applicant $applicant) {
                $applicant->setAttribute('decrypted_data', $this->decryptRecord($applicant->data));
            });

        return view('admin.emailList', compact('applicants'));
    }

    public function blacklist(): JsonResponse
    {
        return response()->json(BlackList::query()->orderBy('email')->get());
    }

    private function decryptRecord(?string $encoded): array
    {
        $raw = base64_decode($encoded ?? '', true);
        if ($raw === false || strlen($raw) < 17) {
            return [];
        }

        $iv = substr($raw, 0, 16);
        $ciphertext = substr($raw, 16);
        $plaintext = openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            (string) config('app.record_encryption_key'),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            return [];
        }

        $payload = json_decode($plaintext, true);
        return is_array($payload) ? $payload : [];
    }

}