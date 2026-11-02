<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ApplicantEncryptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:' . base64_encode(str_repeat('a', 32)),
            'app.cipher' => 'AES-256-CBC',
            'app.record_encryption_key' => str_repeat('b', 32),
        ]);
        Crypt::clearResolvedInstance('encrypter');
    }

    public function test_admin_decrypts_laravel_encrypted_applicant_records(): void
    {
        $record = ['firstname' => 'Ada', 'lastname' => 'Lovelace'];

        $this->assertSame($record, $this->decryptRecord(Crypt::encryptString(json_encode($record, JSON_THROW_ON_ERROR))));
    }

    public function test_admin_decrypts_legacy_applicant_records(): void
    {
        $record = ['firstname' => 'Grace', 'lastname' => 'Hopper'];
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt(
            json_encode($record, JSON_THROW_ON_ERROR),
            'AES-256-CBC',
            config('app.record_encryption_key'),
            OPENSSL_RAW_DATA,
            $iv
        );

        $this->assertNotFalse($ciphertext);
        $this->assertSame($record, $this->decryptRecord(base64_encode($iv . $ciphertext)));
    }

    public function test_admin_does_not_fall_back_to_legacy_decryption_for_tampered_laravel_records(): void
    {
        $record = Crypt::encryptString(json_encode(['firstname' => 'Ada'], JSON_THROW_ON_ERROR));
        $payload = json_decode(base64_decode($record, true), true);
        $payload['mac'] = str_repeat('0', strlen($payload['mac']));
        $tamperedRecord = base64_encode(json_encode($payload, JSON_THROW_ON_ERROR));

        $this->assertSame([], $this->decryptRecord($tamperedRecord));
    }

    private function decryptRecord(string $record): array
    {
        $method = new \ReflectionMethod(AdminController::class, 'decryptRecord');
        $controller = new AdminController();

        return $method->invoke($controller, $record);
    }
}
