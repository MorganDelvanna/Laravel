<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Mail;
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

    public function resendEmail(string $uuid): JsonResponse
    {
        $selected = Applicant::where('uuid', $uuid)->firstOrFail();
        if (!$selected->transaction_id) {
            return response()->json(['message' => 'This application has no completed payment.'], 422);
        }

        // Success emails are sent as one batch per Stripe transaction, so resend
        // the complete batch when any applicant in it is selected.
        $records = Applicant::where('transaction_id', $selected->transaction_id)->get();
        $html = '';
        $text = '';
        $attachments = [];
        $emails = [];
        $lastType = 'New';
        foreach ($records as $record) {
            $data = $this->decryptRecord($record->data);
            if (!$data) {
                return response()->json(['message' => 'An application record could not be read.'], 422);
            }
            $type = match ($data['applicationType'] ?? '') {
                'renew' => 'Renewal', 'half' => 'New Half-Year', 'update' => 'Update', default => 'New',
            };
            $fee = match ($data['membershipFee'] ?? '') {
                'senior' => 'Senior Membership', 'junior' => 'Junior Membership', 'archery' => 'Archery Membership', 'update' => 'Family Membership', default => 'General Membership',
            };
            $lastType = $type;
            $first = (string) ($data['firstname'] ?? '');
            $last = (string) ($data['lastname'] ?? '');
            if (!empty($data['email'])) $emails[] = $data['email'];
            $html .= '<strong>'.e("$type $fee")."</strong><br />";
            $html .= !empty($data['pfgaNumber']) ? '<strong>Card #</strong>: '.e($data['pfgaNumber']).'<br />' : '';
            $html .= '<strong>First Name</strong>: '.e($first).'<br /><strong>Last Name</strong>: '.e($last).'<br />';
            if (($data['applicationType'] ?? '') !== 'update') {
                $html .= !empty($data['alias']) ? '<strong>Preferred Name</strong>: '.e($data['alias']).'<br />' : '';
                $html .= '<strong>Date of Birth</strong>: '.e($data['dob'] ?? '').'<br />';
                $html .= '<strong>Address</strong>: '.e(implode(', ', array_filter([$data['address'] ?? '', $data['city'] ?? '', $data['province'] ?? '', $data['postal'] ?? '']))).'<br />';
                $html .= '<strong>Email</strong>: '.e($data['email'] ?? '').'<br />';
                $html .= !empty($data['homephone']) ? '<strong>Home Phone</strong>: '.e($data['homephone']).'<br />' : '';
                $html .= !empty($data['cellphone']) ? '<strong>Cell Phone</strong>: '.e($data['cellphone']).'<br />' : '';
                $html .= ($data['palType'] ?? '') === 'noPal' ? 'None<br />' : '<strong>PAL</strong>: '.e($data['palNum'] ?? '').' expires: '.e($data['palExpiry'] ?? '').'<br />';
                $html .= !empty($data['palDate']) ? '<strong>Approx PAL Date</strong>: '.e($data['palDate']).'<br />' : '';
                $html .= '<strong>Disciplines</strong>: '.e(implode(',', (array) ($data['disciplines'] ?? []))).'<br />';
            }
            $html .= '<strong>Extra Cards Ordered</strong>: '.e((string) ($data['extra'] ?? '')).'<br /><br /><hr><br />';
            if (!empty($data['family']) && is_array($data['family'])) {
                $html .= '<p><strong>Family Members</strong><br />';
                foreach ($data['family'] as $member) {
                    $html .= e(($member['firstname'] ?? '').' '.($member['lastname'] ?? '').', PAL: '.($member['pal'] ?? '').' '.($member['palExpiry'] ?? '').', DoB: '.($member['dob'] ?? '')).'<br />';
                }
                $html .= '</p>';
            }
            $text .= "$type $fee\nFirst Name: $first\nLast Name: $last\n";
            if (!empty($data['photo']['data'])) {
                $bytes = base64_decode($data['photo']['data'], true);
                if ($bytes !== false) $attachments[] = ['data' => $bytes, 'name' => basename($data['photo']['name'] ?? ($first.'_'.$last.'_photo.jpg')), 'type' => $data['photo']['type'] ?? 'application/octet-stream'];
            }
            foreach ((array) ($data['family'] ?? []) as $member) {
                if (empty($member['photo']['data'])) continue;
                $bytes = base64_decode($member['photo']['data'], true);
                if ($bytes !== false) $attachments[] = ['data' => $bytes, 'name' => basename($member['photo']['name'] ?? 'family_photo.jpg'), 'type' => $member['photo']['type'] ?? 'application/octet-stream'];
            }
        }
        $countLabel = count($records) === 1 ? 'Applicant' : 'Applicants ('.count($records).')';
        $subject = "PFGA $lastType $countLabel from Stripe";
        $htmlBody = '<html><body><p>Hello Membership Secretary,</p><p>The following '.$countLabel.' submitted via Stripe:</p>'.$html.'<p>Thanks,<br />The PFGA Stripe Application</p></body></html>';
        $textBody = "Hello Membership Secretary,\n\nThe following $countLabel submitted via Stripe:\n\n$text\nThanks,\nThe PFGA Stripe Application";
        try {
            Mail::send([], [], function ($message) use ($emails, $attachments, $subject, $htmlBody, $textBody) {
                $message->from((string) config('mail.from.address'), (string) config('mail.from.name'))
                    ->to((string) config('mail.from.address'))->subject($subject)->html($htmlBody)->text($textBody);
                foreach (array_unique($emails) as $email) $message->cc($email);
                foreach ($attachments as $attachment) $message->attachData($attachment['data'], $attachment['name'], ['mime' => $attachment['type']]);
            });
        } catch (\Throwable $exception) {
            Log::error('Failed to resend membership batch email: '.$exception->getMessage());
            return response()->json(['message' => 'The email could not be sent.'], 500);
        }
        $records->each(fn (Applicant $record) => $record->update(['emailed' => true, 'emailed_at' => now()]));
        return response()->json(['message' => 'Email resent for '.count($records).' applicant(s).']);
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
