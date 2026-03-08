<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Unit test template for Eloquent models.
 *
 * This template demonstrates:
 * - Model relationships
 * - Accessors and mutators
 * - Scopes
 * - Factory states
 * - Model attributes
 * - Business logic in models
 *
 * Run with: php artisan test --filter=UserModelTest
 */
#[CoversClass(User::class)]
class UserModelTest extends TestCase
{
    use RefreshDatabase;

    // ==================== RELATIONSHIP TESTS ====================

    #[Test]
    #[TestDox('User has many posts')]
    public function has_many_posts(): void
    {
        // Arrange
        $user = User::factory()
            ->has(Post::factory()->count(3))
            ->create();

        // Act
        $posts = $user->posts;

        // Assert
        $this->assertInstanceOf(Collection::class, $posts);
        $this->assertCount(3, $posts);
        $this->assertTrue($posts->first()->user_id === $user->id);
    }

    #[Test]
    #[TestDox('User has many comments')]
    public function has_many_comments(): void
    {
        // Arrange
        $user = User::factory()
            ->has(Comment::factory()->count(5))
            ->create();

        // Act & Assert
        $this->assertCount(5, $user->comments);
    }

    #[Test]
    #[TestDox('User belongs to many roles')]
    public function belongs_to_many_roles(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->roles()->attach([
            Role::factory()->create(['name' => 'admin'])->id,
            Role::factory()->create(['name' => 'editor'])->id,
        ]);

        // Act
        $roles = $user->roles;

        // Assert
        $this->assertCount(2, $roles);
        $this->assertTrue($roles->contains('name', 'admin'));
        $this->assertTrue($roles->contains('name', 'editor'));
    }

    // ==================== ACCESSOR TESTS ====================

    #[Test]
    #[TestDox('User full name accessor combines first and last name')]
    public function full_name_accessor(): void
    {
        // Arrange
        $user = User::factory()->make([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Act & Assert
        $this->assertEquals('John Doe', $user->full_name);
    }

    #[Test]
    #[TestDox('User avatar accessor returns default when not set')]
    public function avatar_returns_default(): void
    {
        // Arrange
        $user = User::factory()->make(['avatar' => null]);

        // Act & Assert
        $this->assertStringContainsString('default-avatar', $user->avatar);
    }

    #[Test]
    #[TestDox('Email is always lowercased')]
    public function email_is_lowercased(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'JOHN.DOE@EXAMPLE.COM',
        ]);

        // Act & Assert
        $this->assertEquals('john.doe@example.com', $user->email);
    }

    // ==================== MUTATOR TESTS ====================

    #[Test]
    #[TestDox('Password is automatically hashed')]
    public function password_is_hashed(): void
    {
        // Arrange
        $plainPassword = 'secure-password-123';

        // Act
        $user = User::factory()->create([
            'password' => $plainPassword,
        ]);

        // Assert
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    #[Test]
    #[TestDox('Settings are serialized as JSON')]
    public function settings_serialized_as_json(): void
    {
        // Arrange
        $settings = ['theme' => 'dark', 'notifications' => true];

        // Act
        $user = User::factory()->create(['settings' => $settings]);

        // Assert
        $this->assertEquals($settings, $user->settings);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    // ==================== SCOPE TESTS ====================

    #[Test]
    #[TestDox('Active scope filters active users')]
    public function active_scope(): void
    {
        // Arrange
        User::factory()->count(3)->active()->create();
        User::factory()->count(2)->inactive()->create();

        // Act
        $activeUsers = User::active()->get();

        // Assert
        $this->assertCount(3, $activeUsers);
        $activeUsers->each(fn($user) => $this->assertTrue($user->is_active));
    }

    #[Test]
    #[TestDox('Admin scope filters admin users')]
    public function admin_scope(): void
    {
        // Arrange
        User::factory()->count(2)->admin()->create();
        User::factory()->count(5)->create();

        // Act
        $admins = User::admins()->get();

        // Assert
        $this->assertCount(2, $admins);
    }

    #[Test]
    #[TestDox('Search scope searches by name or email')]
    public function search_scope(): void
    {
        // Arrange
        User::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@test.com']);
        User::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.com']);

        // Act
        $results = User::search('john')->get();

        // Assert
        $this->assertCount(2, $results); // John Smith and bob@example.com
    }

    // ==================== BUSINESS LOGIC TESTS ====================

    #[Test]
    #[TestDox('User can be marked as verified')]
    public function can_be_verified(): void
    {
        // Arrange
        $user = User::factory()->unverified()->create();

        // Act
        $user->markAsVerified();

        // Assert
        $this->assertNotNull($user->email_verified_at);
    }

    #[Test]
    #[TestDox('User can check if admin')]
    public function can_check_if_admin(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        // Act & Assert
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    #[Test]
    #[TestDox('User can have role assigned')]
    public function can_assign_role(): void
    {
        // Arrange
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'editor']);

        // Act
        $user->assignRole($role);

        // Assert
        $this->assertTrue($user->hasRole('editor'));
    }

    #[Test]
    #[TestDox('User can have multiple roles')]
    public function can_have_multiple_roles(): void
    {
        // Arrange
        $user = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $editorRole = Role::factory()->create(['name' => 'editor']);

        // Act
        $user->roles()->sync([$adminRole->id, $editorRole->id]);

        // Assert
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertFalse($user->hasRole('moderator'));
    }

    // ==================== FACTORY STATE TESTS ====================

    #[Test]
    #[TestDox('Factory creates admin user')]
    public function factory_creates_admin(): void
    {
        // Act
        $user = User::factory()->admin()->make();

        // Assert
        $this->assertEquals('admin', $user->role);
    }

    #[Test]
    #[TestDox('Factory creates unverified user')]
    public function factory_creates_unverified(): void
    {
        // Act
        $user = User::factory()->unverified()->make();

        // Assert
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    #[TestDox('Factory creates user with specific attributes')]
    public function factory_with_specific_attributes(): void
    {
        // Act
        $user = User::factory()->create([
            'name' => 'Custom Name',
            'email' => 'custom@example.com',
        ]);

        // Assert
        $this->assertEquals('Custom Name', $user->name);
        $this->assertEquals('custom@example.com', $user->email);
    }

    // ==================== CASTING TESTS ====================

    #[Test]
    #[TestDox('Boolean fields are cast correctly')]
    public function boolean_casting(): void
    {
        // Act
        $user = User::factory()->create([
            'is_active' => 1,
            'receive_notifications' => 0,
        ]);

        // Assert
        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->receive_notifications);
    }

    #[Test]
    #[TestDox('Date fields are cast to Carbon')]
    public function date_casting(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->updated_at);
    }

    #[Test]
    #[TestDox('JSON fields are cast to array')]
    public function json_casting(): void
    {
        // Arrange
        $user = User::factory()->create([
            'preferences' => ['theme' => 'dark', 'language' => 'en'],
        ]);

        // Assert
        $this->assertIsArray($user->preferences);
        $this->assertEquals('dark', $user->preferences['theme']);
    }

    // ==================== VALIDATION TESTS ====================

    #[Test]
    #[TestDox('Email must be unique')]
    public function email_must_be_unique(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        // Act & Assert
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'existing@example.com']);
    }

    // ==================== SOFT DELETE TESTS ====================

    #[Test]
    #[TestDox('User can be soft deleted')]
    public function can_be_soft_deleted(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $user->delete();

        // Assert
        $this->assertSoftDeleted($user);
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    #[Test]
    #[TestDox('Soft deleted user can be restored')]
    public function can_be_restored(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->delete();

        // Act
        $user->restore();

        // Assert
        $this->assertNotNull(User::find($user->id));
        $this->assertNull($user->deleted_at);
    }
}
