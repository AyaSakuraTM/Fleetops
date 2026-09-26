<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TwoFactorLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_login_sends_a_code_and_redirects_to_the_two_factor_challenge(): void
    {
        $this->assertLoginStartsTwoFactorForRole('Admin');
    }

    public function test_active_staff_login_sends_a_code_and_redirects_to_the_two_factor_challenge(): void
    {
        $this->assertLoginStartsTwoFactorForRole('Staff');
    }

    public function test_smtp_failure_does_not_return_server_error_or_leave_an_unsent_code(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'scheme' => 'smtp',
                'host' => '127.0.0.1',
                'port' => 1,
                'username' => null,
                'password' => null,
                'timeout' => 1,
                'local_domain' => 'localhost',
            ],
        ]);

        $user = User::factory()->create([
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertLessThan(500, $response->getStatusCode());

        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);
    }

    private function assertLoginStartsTwoFactorForRole(string $role): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => $role,
            'status' => 'active',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $response->assertSessionHas('two_factor.user_id', $user->id);

        $user->refresh();
        $this->assertNotEmpty($user->two_factor_code);
        $this->assertTrue($user->two_factor_expires_at->isFuture());
        Notification::assertSentTo($user, TwoFactorCodeNotification::class);
    }
}