<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Feature test template for authentication flows.
 *
 * This template demonstrates:
 * - User registration
 * - Login/logout
 * - Password reset
 * - Email verification
 * - Token management
 * - Session handling
 *
 * Run with: php artisan test --filter=AuthenticationTest
 */
#[Group('auth')]
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ==================== REGISTRATION TESTS ====================

    #[Test]
    #[TestDox('User can register with valid credentials')]
    public function user_can_register(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        // Assert password is hashed
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('SecurePassword123!', $user->password));
    }

    #[Test]
    #[TestDox('Registration fails with duplicate email')]
    public function registration_fails_with_duplicate_email(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        // Act
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // ==================== LOGIN TESTS ====================

    #[Test]
    #[TestDox('User can login with valid credentials')]
    public function user_can_login(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    #[Test]
    #[TestDox('Login fails with invalid credentials')]
    public function login_fails_with_invalid_credentials(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    #[TestDox('Login fails for non-existent user')]
    public function login_fails_for_nonexistent_user(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // ==================== LOGOUT TESTS ====================

    #[Test]
    #[TestDox('User can logout')]
    public function user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->postJson('/api/v1/auth/logout');

        // Assert
        $response->assertOk();
        $this->assertCount(0, $user->fresh()->tokens);
    }

    #[Test]
    #[TestDox('Guest cannot logout')]
    public function guest_cannot_logout(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/logout');

        // Assert
        $response->assertUnauthorized();
    }

    // ==================== PASSWORD RESET TESTS ====================

    #[Test]
    #[TestDox('User can request password reset link')]
    public function user_can_request_password_reset(): void
    {
        // Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Act
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'message' => 'Password reset link sent.',
            ]);

        Notification::assertSentTo(
            $user,
            ResetPassword::class
        );
    }

    #[Test]
    #[TestDox('Password reset fails for non-existent email')]
    public function password_reset_fails_for_nonexistent_email(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Assert - Should still return OK to prevent email enumeration
        $response->assertOk();
    }

    #[Test]
    #[TestDox('User can reset password with valid token')]
    public function user_can_reset_password(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        // Act
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        // Assert
        $response->assertOk();

        // Verify new password works
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'NewSecurePassword123!',
        ])->assertOk();
    }

    // ==================== EMAIL VERIFICATION TESTS ====================

    #[Test]
    #[TestDox('User can verify email')]
    public function user_can_verify_email(): void
    {
        // Arrange
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->postJson('/api/v1/auth/email/verification-notification');

        // Assert
        $response->assertAccepted();
    }

    #[Test]
    #[TestDox('Verified user cannot request verification')]
    public function verified_user_cannot_request_verification(): void
    {
        // Arrange
        $user = User::factory()->create(); // Already verified
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson('/api/v1/auth/email/verify/' . $user->id . '/' . sha1($user->email));

        // Assert
        $response->assertForbidden();
    }

    // ==================== PROTECTED ROUTE TESTS ====================

    #[Test]
    #[TestDox('Authenticated user can access protected routes')]
    public function authenticated_user_can_access_protected_routes(): void
    {
        // Arrange
        $user = Sanctum::actingAs(User::factory()->create());

        // Act
        $response = $this->getJson('/api/v1/user');

        // Assert
        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    #[Test]
    #[TestDox('Guest cannot access protected routes')]
    public function guest_cannot_access_protected_routes(): void
    {
        // Act
        $response = $this->getJson('/api/v1/user');

        // Assert
        $response->assertUnauthorized();
    }

    // ==================== TOKEN ABILITY TESTS ====================

    #[Test]
    #[TestDox('Token abilities are checked')]
    public function token_abilities_are_checked(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['read']); // Only read ability

        // Act & Assert - Read operation should work
        $this->getJson('/api/v1/posts')->assertOk();

        // Act & Assert - Write operation should fail
        $this->postJson('/api/v1/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ])->assertForbidden();
    }

    // ==================== SESSION TESTS ====================

    #[Test]
    #[TestDox('User session can be validated')]
    public function user_session_can_be_validated(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson('/api/v1/auth/check');

        // Assert
        $response->assertOk()
            ->assertJson([
                'authenticated' => true,
            ]);
    }

    #[Test]
    #[TestDox('Expired token is rejected')]
    public function expired_token_is_rejected(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test', ['*'], now()->subHour());

        // Act
        $response = $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/user');

        // Assert
        $response->assertUnauthorized();
    }
}
