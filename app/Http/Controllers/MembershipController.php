<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Applicant;
use App\Models\BlackList;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

class MembershipController extends Controller
{
    public function memberForm(string $applicationType)
    {
        return view('membership.memberForm', ['applicationType' => $applicationType]);
    }

    public function familyForm()
    {
        return view('membership.familyForm');
    }

    public function canceled(Request $request, string $sessionId)
    {
        $stripe = new \Stripe\StripeClient(config('app.stripe_secret_key'));
        // Retrieve checkout session if provided
        $uuids = [];

        if (isset($sessionId) && !empty($sessionId)) {
            try {
                $checkout_session = $stripe->checkout->sessions->retrieve($sessionId);
                if (!empty($checkout_session['metadata'])) {
                    $meta = $checkout_session['metadata'];
                    if (!empty($meta['applicant_uuids'])) {
                        $v = $meta['applicant_uuids'];
                        if (is_string($v) && strpos($v, ',') !== false) {
                            foreach (explode(',', $v) as $part) {
                                $part = trim($part);
                                if ($part !== '') $uuids[] = $part;
                            }
                        } else if (!empty($v)) {
                            $uuids[] = $v;
                        }
                    }
                }
            } catch (Throwable $e) {
                error_log('Failed to retrieve checkout session: ' . $e->getMessage());
            }
        }

        // If there are UUIDs, update DB
        if (!empty($uuids)) {
            foreach($uuids as $uuid) {
                $applicant = Applicant::where('uuid', $uuid)->firstOrFail();
                if ($applicant) {
                    $applicant->update(['canceled_at' => now()]);
                }
                else {
                    error_log('No applicant record found for UUID: ' . $uuid);
                }
            }
        }
        else {
                error_log('No UUIDs: ' . implode(', ', $uuids));
        }
        // return the canceled view
        return view('membership.canceled');
    }

    public function success(Request $request, string $sessionId)
    {
        $stripe = new \Stripe\StripeClient(config('app.stripe_secret_key'));

        // Retrieve the Checkout Session for the successful payment flow that just
        // completed. This will be displayed in a `pre` tag as json in this file.
        try {
            $checkout_session = $stripe->checkout->sessions->retrieve($sessionId);
        } catch (Throwable $e) {
            Log::error('Failed to retrieve Stripe checkout session: ' . $e->getMessage());
            return redirect()->route('membership.canceled')->withErrors(['email' => 'Checkout Session not found.']);
        }


        // --- Additional step: retrieve encrypted records by UUID from DB, decrypt, and email in batch ---
        // Find UUID keys in metadata
        $uuids = [];
        if (!empty($checkout_session['metadata'])) {
            $meta = $checkout_session['metadata'];
            $v = $meta['applicant_uuids'] ?? '';

            // if value contains multiple uuids separated by commas, split
            if (is_string($v) && strpos($v, ',') !== false) {
                foreach (explode(',', $v) as $part) {
                    $part = trim($part);
                    if ($part !== '') $uuids[] = $part;
                }
            } else {
                $uuids[] = $v;
            }
        }

        // Initialize output variables
        $collectedBodyHTML = [];
        $collectedRecords = [];
        $photoAttachments = [];
        $attachmentNames = [];

        if (!empty($uuids)) {
            foreach ($uuids as $u) {
                Applicant::where('uuid', $u)->update(['transaction_id' => $sessionId]);
            }

            // STEP 1: Retrieve and decrypt all records
            foreach ($uuids as $u) {
                $res = Applicant::where('uuid', $u)->first();
                if ($res) {
                    try {
                        $plain = Crypt::decryptString($res->data);
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        Log::error('Failed to decrypt applicant record for uuid ' . $u, ['exception' => $e]);
                        continue;
                    }

                    // decrypted payload is JSON; decode to associative array
                    $decoded = json_decode($plain, true);
                    if (!is_array($decoded)) {
                        Log::error('Decrypted record is not valid JSON for uuid ' . $u);
                        continue;
                    }

                    // Store the record for batch processing
                    $collectedRecords[] = $decoded;
                } else {
                    error_log('No DB record found for uuid ' . $u);
                }
            }

            // STEP 2: Build batch email from all collected records
            if (!empty($collectedRecords)) {
                $batchBodyHTML = '';
                $batchBodyText = '';

                foreach ($collectedRecords as $plain) {
                    // Initialize per-record variables
                    $bodyHTML = '';
                    $bodyText = '';
                    $isUpdate = isset($plain['applicationType']) && $plain['applicationType'] === 'update';

                    switch( $plain['applicationType'] ) {
                    case 'new':
                        $appType = 'New';
                        break;
                    case 'half':
                        $appType = 'New Half-Year';
                        break;
                    case 'renew':
                        $appType = 'Renewal';
                        break;
                    case 'update':
                        $appType = 'Update';
                        break;
                    default:
                        $appType = 'New';
                    }

                    switch( $plain['membershipFee']){
                    case 'general':
                        $feeType = 'General Membership';
                        break;
                    case 'senior':
                        $feeType = 'Senior Membership';
                        break;
                    case 'junior':
                        $feeType = 'Junior Membership';
                        break;
                    case 'archery':
                        $feeType = 'Archery Membership';
                        break;
                    case 'update':
                        $feeType= 'Family Membership';
                        break;
                    default:
                        $feeType = 'General Membership';
                    }

                    $firstName = $plain['firstname'];
                    $lastName = $plain['lastname'];

                    if(!$isUpdate){
                        $dob = $plain['dob'];
                        $address = $plain['address'].', '.$plain['city'].', '.$plain['province'].', '.$plain['postal'];
                        $email = $plain['email'];
                        $disciplines = implode(',', $plain['disciplines']);
                    }
                    $extraCards = $plain['extra'];

                    $bodyHTML = "<strong>$appType $feeType</strong><br />";
                    $bodyHTML .= (strlen($plain['pfgaNumber']) > 0) ? '<strong>Card #</strong>: '.$plain['pfgaNumber'].'<br />' : '';
                    $bodyHTML .= "<strong>First Name</strong>: $firstName<br />";
                    $bodyHTML .= "<strong>Last Name</strong>: $lastName<br />";
                    if(!$isUpdate){
                        $bodyHTML .= (strlen($plain['alias']) > 0) ? '<strong>Preferred Name</strong>: '.$plain['alias'].'<br />' : '';
                        $bodyHTML .= "<strong>Date of Birth</strong>: $dob<br />";
                        $bodyHTML .= "<strong>Address</strong>: $address<br />";
                        $bodyHTML .= (strlen($plain['homephone']) > 0) ? '<strong>Home Phone</strong>: '.$plain['homephone'].'<br />':'';
                        $bodyHTML .= (strlen($plain['cellphone']) > 0) ? '<strong>Cell Phone</strong>: '.$plain['cellphone'].'<br />':'';
                        $bodyHTML .= "<strong>Email</strong>: $email<br />";
                        $bodyHTML .= ($plain['palType']=='noPal') ? 'None<br />' : "<strong>PAL</strong>: ".$plain['palNum'].' expires: '.$plain['palExpiry'].'<br />' ;
                        $bodyHTML .= (strlen($plain['palDate'])>0) ? '<strong>Approx PAL Date</strong>: '.$plain['palDate'].'<br />' :'';
                        $bodyHTML .= "<strong>Disciplines</strong>: $disciplines<br />";
                    }
                    $bodyHTML .= "<strong>Extra Cards Ordered</strong>: $extraCards<br />";
                    if ($plain['family'] > 0) {
                        $bodyHTML .= '<p><strong>Family Members</strong><br/>';
                        foreach ($plain['family'] as $f) {
                            $bodyHTML .= $f['firstname'].' '.$f['lastname'].', PAL: '.$f['pal'].' '.$f['palExpiry'].', DoB: '.$f['dob'].'<br />';
                        };
                        $bodyHTML .= "</p>";
                    };
                    if(!$isUpdate){
                        if(isset($plain['clubs']) && is_array($plain['clubs'])){
                            $bodyHTML .= '<p><strong>Club Affiliations</strong><br />';
                            foreach ( $plain['clubs'] as $c) {
                            $bodyHTML .= $c['name'].', '.$c['city'].', '.$c['from'].' -'. $c['to'].'<br />';
                            };
                            $bodyHTML .= "</p>";
                        };
                        if(isset($plain['courses']) && is_array($plain['courses'])){
                            $bodyHTML .= '<strong>Course/Training</strong><br />';
                            foreach ($plain['courses'] as $c) {
                            $bodyHTML .= $c['desc'].', '.$c['location'].', '.$c['trainer'].', '. $c['date'].'<br />';
                            };
                            $bodyHTML .= "</p>";
                        };
                    }

                    $bodyText = "$appType $feeType\n";
                    $bodyText .= (strlen($plain['pfgaNumber']) > 0) ? 'Card #: '.$plain['pfgaNumber'].'\n' : '';
                    $bodyText .= "First Name: $firstName\n";
                    $bodyText .= "Last Name: $lastName\n";
                    if($appType != 'Update'){
                        $bodyText .= (strlen($plain['alias']) > 0) ? 'Preferred Name: '.$plain['alias'].'\n' : '';
                        $bodyText .= "Date of Birth: $dob\n";
                        $bodyText .= "Address: $address\n";
                        $bodyText .= (strlen($plain['homephone']) > 0) ? 'Home Phone: '.$plain['homephone'].'\n':'';
                        $bodyText .= (strlen($plain['cellphone']) > 0) ? 'Cell Phone: '.$plain['cellphone'].'\n':'';
                        $bodyText .= "Email: $email\n";
                        $bodyText .= ($plain['palType']=='noPal') ? 'None' : "PAL: ".$plain['palNum'].' Expires: '.$plain['palExpiry']."\n";
                        $bodyText .= (strlen($plain['palDate'])>0) ? 'Approx PAL Date: '.$plain['palDate']."\n" :'';
                        $bodyText .= "Disciplines: $disciplines\n";
                    }
                    $bodyText .= "Extra Cards Ordered: $extraCards\n";
                    if ($plain['family'] > 0) {
                        $bodyText .= 'Family Members\n';
                        foreach ($plain['family'] as $f) {
                            $bodyText .= $f['firstname'].' '.$f['lastname'].', PAL: '.$f['pal'].' '.$f['palExpiry'].', DoB: '.$f['dob'].'\n';
                        };
                    };

                    if(!$isUpdate){
                        if(isset($plain['clubs']) && is_array($plain['clubs'])){
                            $bodyText .= 'Club Affiliations\n';
                            foreach ( $plain['clubs'] as $c) {
                            $bodyText .= $c['name'].', '.$c['city'].', '.$c['from'].' -'. $c['to'].'\n';
                            };
                        };
                        if(isset($plain['courses']) && is_array($plain['courses'])){
                            $bodyText .= 'Course/Training\n';
                            foreach ($plain['courses'] as $c) {
                            $bodyText .= $c['desc'].', '.$c['location'].', '.$c['trainer'].', '. $c['date'].'\n';
                            };
                        };
                    }

                    if (isset($plain['photo']['data']) && strlen($plain['photo']['data']) > 0) {
                        error_log($plain['photo']['name']);
                        error_log($plain['photo']['data']);
                        $photoBytes = base64_decode($plain['photo']['data']);
                        if ($photoBytes !== false) {
                            $photoType = trim($plain['photo']['type'] ?? '');
                            $photoName = trim($plain['photo']['name'] ?? '');
                            $photoName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $photoName);
                            $extension = '';
                            if ($photoType === 'image/jpeg' || $photoType === 'image/jpg') {
                                $extension = 'jpg';
                            } elseif ($photoType === 'image/png') {
                                $extension = 'png';
                            } elseif ($photoName !== '') {
                                $detected = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
                                if (in_array($detected, ['jpg','jpeg','png'], true)) {
                                    $extension = $detected === 'jpeg' ? 'jpg' : $detected;
                                }
                            }
                            if ($extension === '') {
                                $extension = 'jpg';
                            }
                            if ($photoName === '' || pathinfo($photoName, PATHINFO_EXTENSION) === '') {
                                $photoName = sprintf('%s_%s_photo.%s', preg_replace('/[^A-Za-z0-9]+/', '_', $firstName), preg_replace('/[^A-Za-z0-9]+/', '_', $lastName), $extension);
                            } else {
                                $photoName = pathinfo($photoName, PATHINFO_FILENAME) . '.' . $extension;
                            }
                            $uniqueName = $photoName;
                            $suffix = 1;
                            while (isset($attachmentNames[$uniqueName])) {
                                $uniqueName = pathinfo($photoName, PATHINFO_FILENAME) . '-' . $suffix . '.' . $extension;
                                $suffix++;
                            }
                            $attachmentNames[$uniqueName] = true;
                            $photoAttachments[] = [
                                'data' => $photoBytes,
                                'filename' => $uniqueName,
                                'type' => $photoType ?: 'application/octet-stream',
                                'applicant' => trim($firstName . ' ' . $lastName)
                            ];
                        } else {
                            error_log('Invalid photo base64 for applicant ' . $firstName . ' ' . $lastName);
                        }
                    }
                    // Attach any family member photos for this applicant
                    if (isset($plain['family']) && is_array($plain['family'])) {
                        foreach ($plain['family'] as $f) {
                            if (!is_array($f)) continue;
                            if (!isset($f['photo']) || !isset($f['photo']['data']) || strlen($f['photo']['data']) === 0) continue;
                            $photoBytes = base64_decode($f['photo']['data']);
                            if ($photoBytes === false) {
                                error_log('Invalid family photo base64 for ' . ($f['firstname'] ?? '') . ' ' . ($f['lastname'] ?? ''));
                                continue;
                            }
                            $photoType = trim($f['photo']['type'] ?? '');
                            $photoName = trim($f['photo']['name'] ?? '');
                            $photoName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $photoName);
                            $extension = '';
                            if ($photoType === 'image/jpeg' || $photoType === 'image/jpg') {
                                $extension = 'jpg';
                            } elseif ($photoType === 'image/png') {
                                $extension = 'png';
                            } elseif ($photoName !== '') {
                                $detected = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
                                if (in_array($detected, ['jpg','jpeg','png'], true)) {
                                    $extension = $detected === 'jpeg' ? 'jpg' : $detected;
                                }
                            }
                            if ($extension === '') $extension = 'jpg';
                            if ($photoName === '' || pathinfo($photoName, PATHINFO_EXTENSION) === '') {
                                $photoName = sprintf('%s_%s_photo.%s', preg_replace('/[^A-Za-z0-9]+/', '_', ($f['firstname'] ?? '')), preg_replace('/[^A-Za-z0-9]+/', '_', ($f['lastname'] ?? '')), $extension);
                            } else {
                                $photoName = pathinfo($photoName, PATHINFO_FILENAME) . '.' . $extension;
                            }
                            $uniqueName = $photoName;
                            $suffix = 1;
                            while (isset($attachmentNames[$uniqueName])) {
                                $uniqueName = pathinfo($photoName, PATHINFO_FILENAME) . '-' . $suffix . '.' . $extension;
                                $suffix++;
                            }
                            $attachmentNames[$uniqueName] = true;
                            $photoAttachments[] = [
                                'data' => $photoBytes,
                                'filename' => $uniqueName,
                                'type' => $photoType ?: 'application/octet-stream',
                                'applicant' => trim(($f['firstname'] ?? '') . ' ' . ($f['lastname'] ?? ''))
                            ];
                        }
                    }
                    // Store for display on page
                    $collectedBodyHTML[] = $bodyHTML;

                    // Append to batch body (separator between applicants)
                    $batchBodyHTML .= $bodyHTML . "<br /><hr><br />";
                    $batchBodyText .= $bodyText . "\n---\n";
                }

                // STEP 3: Send single batch email with all applicants
                $customer = $checkout_session['customer_details'];
                $ccEmail = $customer['email'];

                $appCountStr = count($collectedRecords) === 1 ? 'Applicant' : 'Applicants (' . count($collectedRecords) . ')';
                $subject = "PFGA $appType $appCountStr from Stripe";
                $htmlBody = "<html><body><p>Hello Membership Secretary,</p><p>The following $appCountStr submitted via Stripe:</p>" . $batchBodyHTML . "<p>Thanks,<br />The PFGA Stripe Application</p></body></html>";
                $textBody = "Hello Membership Secretary,\n\nThe following $appCountStr submitted via Stripe:\n\n" . $batchBodyText . "\n\nThanks,\nThe PFGA Stripe Application";

                try {
                    Mail::send([], [], function ($message) use ($ccEmail, $photoAttachments, $subject, $htmlBody, $textBody) {
                        $message->from((string) config('mail.from.address'), (string) config('mail.from.name'))
                            ->to((string) config('mail.from.address'))
                            ->subject($subject)
                            ->html($htmlBody);

                        $message->text($textBody);
                        if (!empty($ccEmail)) {
                            $message->cc($ccEmail);
                        }
                        foreach ($photoAttachments as $attachment) {
                            $message->attachData(
                                $attachment['data'],
                                $attachment['filename'],
                                ['mime' => $attachment['type']]
                            );
                        }
                    });

                    $emailSuccess[] = 'Batch email sent for ' . count($collectedRecords) . ' applicant(s)';

                    $transactionId = $checkout_session['payment_intent'];

                    // Mark all records as emailed in DB
                    foreach ($uuids as $uuid) {
                        try {
                            $applicant = Applicant::where('uuid', $uuid)->firstOrFail();
                            if ($applicant) {
                                $applicant->update([
                                    'emailed' => true,
                                    'emailed_at' => now(),
                                    'transaction_id' => $transactionId
                                ]);
                            } else {
                                error_log('No applicant record found for UUID: ' . $uuid);
                            }
                        } catch (Throwable $e) {
                            error_log('Failed to update emailed status for uuid ' . $uuid . ': ' . $e->getMessage());
                             Log::error('Failed to update emailed status for uuid ' . $uuid . ': ' . $e->getMessage());
                        }
                    }
                } catch (Throwable $e) {
                    Log::error('Failed to send batch email: ' . $e->getMessage());
                }
            }
        }

        return view('membership.success', [
            'collectedBodyHTML' => $collectedBodyHTML,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $domainUrl = rtrim((string) config('app.domain', config('app.url')), '/');
        $successUrl = $domainUrl . '/' . ltrim((string) config('app.success_path'), '/');
        $cancelUrl = $domainUrl . '/' . ltrim((string) config('app.cancel_path'), '/');
        $stripe = new \Stripe\StripeClient(config('app.stripe_secret_key'));

        // Helper function to build items for an applicant
        function buildItemsForApplicant($applicationType, $membershipFee, $env) {
            $items = [];

            switch ($applicationType){
                case ('new'):
                    $items = [[
                        'price' => (string) config('app.price_initiation'),
                        'quantity' => 1
                        ]];
                    switch($membershipFee){
                        case 'general':
                            $items[] = [
                                // General New
                                'price' => (string) config('app.price_general'),
                                'quantity' => 1
                                ];
                            break;
                        case 'senior':
                            $items[] = [
                                // Senior New
                                'price' => (string) config('app.price_senior'),
                                'quantity' => 1,
                            ];
                            break;
                        case 'junior':
                            $items[] = [
                                // Junior New
                                'price' => (string) config('app.price_junior'),
                                'quantity' => 1
                            ];
                            break;
                        default:
                        $items[] = [
                            // General New
                            'price' => (string) config('app.price_general'),
                            'quantity' => 1
                            ];
                    }
                    break;
                case 'half':
                    $items = [[
                        'price' => (string) config('app.price_initiation'),
                        'quantity' => 1
                        ]];
                    switch($membershipFee){
                        case 'general':
                            $items[] = [
                                // General New
                                'price' => (string) config('app.price_general_half'),
                                'quantity' => 1
                                ];
                            break;
                        case 'senior':
                            $items[] = [
                                // Senior New
                                'price' => (string) config('app.price_senior_half'),
                                'quantity' => 1,
                            ];
                            break;
                        case 'junior':
                            $items[] = [
                                // Junior New
                                'price' => (string) config('app.price_junior_half'),
                                'quantity' => 1
                            ];
                            break;
                        case 'archery':
                            $items[] = [
                                // General New
                                'price' => (string) config('app.price_general_half'),
                                'quantity' => 1
                                ];
                            break;
                        default:
                        $items[] = [
                            // General New
                            'price' => (string) config('app.price_general_half'),
                            'quantity' => 1
                            ];
                    }
                    break;
                case 'renew':
                    switch($membershipFee){
                        case 'general':
                            $items[] = [
                                // General New
                                'price' => (string) config('app.price_general'),
                                'quantity' => 1
                                ];
                            break;
                        case 'senior':
                            $items[] = [
                                // Senior New
                                'price' => (string) config('app.price_senior'),
                                'quantity' => 1,
                            ];
                            break;
                        case 'junior':
                            $items[] = [
                                // Junior New
                                'price' => (string) config('app.price_junior'),
                                'quantity' => 1
                            ];
                            break;
                        case 'archery':
                            $items[] = [
                                // General New
                                'price' => (string) config('app.price_general_half'),
                                'quantity' => 1
                                ];
                            break;
                        default:
                        $items[] = [
                            // General New
                            'price' => (string) config('app.price_general_half'),
                            'quantity' => 1
                            ];
                    }
                    break;
                case 'update':
                break;
                default:
                    $items = [[
                        'price' => (string) config('app.price_initiation'),
                        'quantity' => 1
                        ]];
                    $items[] = [
                        // General New
                        'price' => (string) config('app.price_general'),
                        'quantity' => 1
                        ];
            }

            return $items;
        }

        // Process applicants list early
        $applicants_list = [];
        $stored_records = [];
        $applicant_uuids = [];

        if (!empty($request->members_json)) {
            $payload = json_decode($request->members_json, true);
            if (is_array($payload)) {
                // Support two shapes:
                // 1) array of applicant objects (added via member form addApplicant)
                // 2) legacy single-object shape { applicant: {...}, members: [...] }
                $applicants_list = $payload;
            }
        }

        // Build items for all applicants and prepare encryption
        $all_items = [];
        try
        {
            $blacklist = BlackList::all()->pluck('email')->toArray();
        } catch (Throwable $e) {
            Log::error('Error fetching blacklist: ' . $e->getMessage());
            $blacklist = [];
        }

        foreach ($applicants_list as $ap) {
            $appObj = is_array($ap) ? $ap : [];

            $afn = trim((string)($appObj['firstname'] ?? ''));
            $aln = trim((string)($appObj['lastname'] ?? ''));
            $aem = trim((string)($appObj['email'] ?? ''));

            if ($afn === '' || $aln === '' || $aem === '') {
                continue; // Skip invalid applicants
            }

            // Check blacklist for this applicant's email; if found, cancel flow
            if(in_array(strtolower($aem), array_map('strtolower', $blacklist))) {
                Log::warning('Applicant email is blacklisted: ' . $aem);
                return redirect('canceled')->withErrors(['email' => 'The email address ' . $aem . ' is blacklisted. Please contact support.']);
            }

            // Generate UUID for this applicant
            $app_uuid = Str::uuid();
            $applicant_uuids[] = $app_uuid;

            // Build items for this applicant
            $appType = $appObj['applicationType'] ?? 'new';
            $membershipFee = $appObj['membershipFee'] ?? 'general';
            $family = (int)($appObj['familyCount'] ?? 0);
            $extra = (int)($appObj['extra'] ?? 0);

            $applicant_items = buildItemsForApplicant($appType, $membershipFee, $_ENV);

            // Add family members items for this applicant
            if (isset($family) && $family != 0) {
                $applicant_items[] = [
                'price' => (string) config('app.price_family'),
                'quantity' => $family,
                'adjustable_quantity' => [
                    'enabled' => true,
                    'minimum' => 0,
                    'maximum' => 10
                ]
                ];
            }

            // Add extra items for this applicant
            if (isset($extra) && $extra != 0) {
                $applicant_items[] = [
                'price' => (string) config('app.price_extra'),
                'quantity' => $extra,
                'adjustable_quantity' => [
                    'enabled' => true,
                    'minimum' => 0,
                    'maximum' => 10
                ]
                ];
            }

            // Add all items with metadata linking to applicant UUID
            foreach ($applicant_items as $item) {
                $all_items[] = $item;
            }


            // Encrypt and store applicant record (now including family array if present)
            $app_json = json_encode($appObj, JSON_THROW_ON_ERROR);
            $stored_records[] = [
                'uuid' => $app_uuid,
                'type' => $appObj['applicationType'] ?? 'new',
                'data' => Crypt::encryptString($app_json),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        // Build metadata with only UUIDs
        $meta = [];
        if (!empty($applicant_uuids)) {
            $meta['applicant_uuids'] = implode(',', $applicant_uuids);
            $meta['total_applicants'] = count($applicant_uuids);
        }

        // Create checkout session with all applicants' items
        $checkout_session = $stripe->checkout->sessions->create([
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'mode' => 'payment',
            'automatic_tax' => ['enabled' => false],
            'line_items' => $all_items,
            'metadata' => $meta
        ]);

        // Store encrypted applicant records
        if (!empty($stored_records)) {

            foreach ($stored_records as $rec) {
                Log::info('Storing encrypted applicant record: UUID=' . $rec['uuid'] . ', Type=' . $rec['type']);
                Applicant::create([
                    'uuid' => $rec['uuid'],
                    'type' => $rec['type'],
                    'data' => $rec['data'],
                    'created_at' => $rec['created_at']
                ]);
            }

        }
        return redirect()->away($checkout_session->url, 303);
    }
}
