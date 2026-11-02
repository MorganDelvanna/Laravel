<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\LegacyPassword;
use Tests\TestCase;

class ApiSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_access_applicants_with_scoped_token(): void
    {
        $user = User::create([
            'UserName' => 'api-admin',
            'Password' => 'secret-password',
            'Role' => 'admin',
        ]);
        $this->createEncryptedMembersTable();

        $login = $this->postJson('/api/login', [
            'username' => 'api-admin',
            'password' => 'secret-password',
        ]);

        $login->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.username', 'api-admin');
        $this->assertSame('admin', $user->Role);

        $token = $user->tokens()->firstOrFail();
        $this->assertSame(['applicants:read'], $token->abilities);
        $this->assertNotNull($token->expires_at);

        $this->withToken($login->json('token'))
            ->getJson('/api/applicants')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_web_login_uses_the_legacy_password_hash(): void
    {
        $user = User::create([
            'UserName' => 'api-admin',
            'Password' => 'secret-password',
            'Role' => 'admin',
        ]);

        $this->post('/login', [
            'username' => 'api-admin',
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.emailList'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_non_admin_cannot_obtain_an_api_token(): void
    {
        User::create([
            'UserName' => 'regular-user',
            'Password' => 'secret-password',
            'Role' => 'user',
        ]);

        $this->postJson('/api/login', [
            'username' => 'regular-user',
            'password' => 'secret-password',
        ])->assertForbidden();
    }

    public function test_invalid_password_is_rejected(): void
    {
        User::create([
            'UserName' => 'api-admin',
            'Password' => 'secret-password',
            'Role' => 'admin',
        ]);

        $this->postJson('/api/login', [
            'username' => 'api-admin',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_legacy_application_password_hash_is_accepted(): void
    {
        $user = User::create([
            'UserName' => 'api-admin',
            'Password' => 'secret-password',
            'Role' => 'admin',
        ]);
        $expectedHash = hash('ripemd128', 'qv&lm*secret-passwordpl@!x');
        DB::table('users')->where('id', $user->id)->update(['Password' => $expectedHash]);

        $this->postJson('/api/login', [
            'username' => 'api-admin',
            'password' => 'secret-password',
        ])->assertOk();

        $this->assertSame($expectedHash, $user->fresh()->getAuthPassword());
        $this->assertTrue(LegacyPassword::check('secret-password', $expectedHash));
    }

    public function test_valid_non_admin_credentials_are_distinguished_from_invalid_credentials(): void
    {
        User::create([
            'UserName' => 'calendar-user',
            'Password' => 'secret-password',
            'Role' => 'calendar',
        ]);

        $this->postJson('/api/login', [
            'username' => 'calendar-user',
            'password' => 'secret-password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'This account is not authorized to access the API.');
    }

    public function test_applicant_endpoint_requires_a_token_with_the_read_ability(): void
    {
        $user = User::create([
            'UserName' => 'api-admin',
            'Password' => 'secret-password',
            'Role' => 'admin',
        ]);
        $this->createEncryptedMembersTable();
        $token = $user->createToken('wrong-scope', ['other:read'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/applicants')
            ->assertForbidden();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        User::create([
            'UserName' => 'api-admin',
            'Password' => 'secret-password',
            'Role' => 'admin',
        ]);

        $token = $this->postJson('/api/login', [
            'username' => 'api-admin',
            'password' => 'secret-password',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function createEncryptedMembersTable(): void
    {
        Schema::create('encrypted_members', function (Blueprint $table) {
            $table->id();
            $table->boolean('processed')->default(false);
            $table->string('transaction_id')->nullable();
        });
    }
}
