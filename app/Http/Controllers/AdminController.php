<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\BlackList;
use App\Support\LegacyPassword;
use App\Models\User;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $user = User::where('UserName', $credentials['username'])->first();

        if ($user && LegacyPassword::check($credentials['password'], $user->getAuthPassword())) {
            Auth::login($user);
            $request->session()->regenerate();

            return match ($user->role) {
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
        if ($encoded === null || $encoded === '') {
            return [];
        }

        try {
            return $this->decodeApplicantRecord(Crypt::decryptString($encoded));
        } catch (DecryptException $exception) {
            $raw = base64_decode($encoded, true);
            $payload = $raw === false ? null : json_decode($raw, true);

            if (is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac'])) {
                Log::warning('Failed to decrypt applicant record with the Laravel application key.');
                return [];
            }
        }

        return $this->decryptLegacyRecord($encoded);
    }

    private function decryptLegacyRecord(string $encoded): array
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 17) {
            return [];
        }

        $key = (string) config('app.record_encryption_key');
        if ($key === '') {
            Log::error('ENCRYPTION_KEY is not set; cannot decrypt a legacy applicant record.');
            return [];
        }

        $iv = substr($raw, 0, 16);
        $ciphertext = substr($raw, 16);
        $plaintext = openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            Log::warning('Failed to decrypt legacy applicant record.');
            return [];
        }

        return $this->decodeApplicantRecord($plaintext);
    }

    private function decodeApplicantRecord(string $plaintext): array
    {
        $payload = json_decode($plaintext, true);
        if (!is_array($payload)) {
            Log::warning('Decrypted applicant record is not valid JSON.');
            return [];
        }

        return $payload;
    }

}