# API Testing Strategies

## Table of Contents
- [RESTful API Testing](#restful-api-testing)
- [Authentication Testing](#authentication-testing)
- [Validation Testing](#validation-testing)
- [Pagination Testing](#pagination-testing)
- [File Upload Testing](#file-upload-testing)
- [Rate Limiting Testing](#rate-limiting-testing)
- [Error Response Testing](#error-response-testing)

---

## RESTful API Testing

### CRUD Operations Test Suite

```php
<?php

namespace Tests\Feature\Api\v1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/posts';

    // ==================== INDEX ====================

    public function test_list_posts_returns_paginated_results(): void
    {
        Post::factory()->count(25)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'excerpt', 'created_at', 'updated_at']
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonCount(15, 'data') // Default pagination
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_list_posts_can_filter_by_status(): void
    {
        Post::factory()->count(5)->published()->create();
        Post::factory()->count(3)->draft()->create();

        $response = $this->getJson("{$this->baseUrl}?status=published");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_list_posts_can_search(): void
    {
        Post::factory()->create(['title' => 'Laravel Testing']);
        Post::factory()->create(['title' => 'Vue Components']);
        Post::factory()->create(['title' => 'Laravel Collections']);

        $response = $this->getJson("{$this->baseUrl}?search=Laravel");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_posts_can_sort(): void
    {
        $oldest = Post::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Post::factory()->create(['created_at' => now()]);

        // Sort ascending
        $response = $this->getJson("{$this->baseUrl}?sort=created_at&order=asc");
        $response->assertJsonPath('data.0.id', $oldest->id);

        // Sort descending
        $response = $this->getJson("{$this->baseUrl}?sort=created_at&order=desc");
        $response->assertJsonPath('data.0.id', $newest->id);
    }

    // ==================== SHOW ====================

    public function test_show_post_returns_full_details(): void
    {
        $post = Post::factory()
            ->hasComments(3)
            ->create();

        $response = $this->getJson("{$this->baseUrl}/{$post->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'author' => ['id', 'name', 'email'],
                    'comments' => [
                        '*' => ['id', 'content', 'user_id']
                    ],
                ],
            ]);
    }

    public function test_show_nonexistent_post_returns_404(): void
    {
        $response = $this->getJson("{$this->baseUrl}/99999");

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Post not found',
            ]);
    }

    // ==================== STORE ====================

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson($this->baseUrl, [
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();

        $response = $this->postJson($this->baseUrl, [
            'title' => 'New Post Title',
            'content' => 'This is the post content with enough length.',
            'excerpt' => 'Short excerpt',
            'category_id' => $category->id,
            'tags' => ['laravel', 'testing'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'New Post Title')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('posts', [
            'title' => 'New Post Title',
            'user_id' => $user->id,
        ]);
    }

    // ==================== UPDATE ====================

    public function test_user_can_update_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        Sanctum::actingAs($user);

        $response = $this->putJson("{$this->baseUrl}/{$post->id}", [
            'title' => 'Updated Title',
            'content' => $post->content,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_post(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner, 'author')->create();

        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson("{$this->baseUrl}/{$post->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertForbidden();
    }

    // ==================== DESTROY ====================

    public function test_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("{$this->baseUrl}/{$post->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted($post);
    }
}
```

---

## Authentication Testing

### Sanctum Authentication Tests

```php
<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

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

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_token_abilities_are_checked(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['read']); // Only read ability

        // Should work - read operation
        $response = $this->getJson('/api/v1/posts');
        $response->assertOk();

        // Should fail - write operation
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ]);
        $response->assertForbidden();
    }
}
```

---

## Validation Testing

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'The email must be a valid email address.');
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_must_meet_requirements(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function test_validation_rejects_invalid_data(array $data, array $expectedErrors): void
    {
        $response = $this->postJson('/api/v1/posts', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'empty data' => [
                [],
                ['title', 'content'],
            ],
            'title too short' => [
                ['title' => 'AB', 'content' => 'Valid content here'],
                ['title'],
            ],
            'content too short' => [
                ['title' => 'Valid Title', 'content' => 'Short'],
                ['content'],
            ],
            'invalid category' => [
                ['title' => 'Valid', 'content' => 'Valid content', 'category_id' => 999],
                ['category_id'],
            ],
        ];
    }
}
```

---

## Pagination Testing

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_pagination_is_15_items(): void
    {
        Post::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/posts');

        $response->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_can_specify_per_page(): void
    {
        Post::factory()->count(50)->create();

        $response = $this->getJson('/api/v1/posts?per_page=10');

        $response->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.last_page', 5);
    }

    public function test_can_navigate_pages(): void
    {
        Post::factory()->count(30)->create();

        // Page 1
        $page1 = $this->getJson('/api/v1/posts?per_page=10&page=1');
        $page1->assertJsonPath('meta.current_page', 1);

        // Page 2
        $page2 = $this->getJson('/api/v1/posts?per_page=10&page=2');
        $page2->assertJsonPath('meta.current_page', 2);

        // Verify different items
        $this->assertNotEquals(
            $page1->json('data.0.id'),
            $page2->json('data.0.id')
        );
    }

    public function test_pagination_links_are_correct(): void
    {
        Post::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/posts?per_page=10&page=2');

        $response->assertJsonStructure([
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);

        $this->assertStringContainsString('page=1', $response->json('links.prev'));
        $this->assertStringContainsString('page=3', $response->json('links.next'));
    }

    public function test_per_page_has_maximum_limit(): void
    {
        Post::factory()->count(200)->create();

        // Request more than max
        $response = $this->getJson('/api/v1/posts?per_page=200');

        // Should be capped at max (e.g., 100)
        $this->assertLessThanOrEqual(100, count($response->json('data')));
    }
}
```

---

## File Upload Testing

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('avatars');

        $user = Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->postJson('/api/v1/user/avatar', [
            'avatar' => $file,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['avatar_url']]);

        Storage::disk('avatars')->assertExists($file->hashName());
    }

    public function test_avatar_must_be_image(): void
    {
        Storage::fake('avatars');

        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/v1/user/avatar', [
            'avatar' => $file,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_has_size_limit(): void
    {
        Storage::fake('avatars');

        Sanctum::actingAs(User::factory()->create());

        // Create file larger than 2MB
        $file = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->postJson('/api/v1/user/avatar', [
            'avatar' => $file,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_can_upload_multiple_files(): void
    {
        Storage::fake('documents');

        Sanctum::actingAs(User::factory()->create());

        $files = [
            UploadedFile::fake()->create('doc1.pdf', 100),
            UploadedFile::fake()->create('doc2.pdf', 100),
            UploadedFile::fake()->create('doc3.pdf', 100),
        ];

        $response = $this->postJson('/api/v1/documents', [
            'documents' => $files,
        ]);

        $response->assertCreated()
            ->assertJsonCount(3, 'data');

        foreach ($files as $file) {
            Storage::disk('documents')->assertExists($file->hashName());
        }
    }
}
```

---

## Rate Limiting Testing

```php
<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    public function test_api_has_rate_limiting(): void
    {
        // Most APIs have rate limits, test that headers are present
        $response = $this->getJson('/api/v1/posts');

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    public function test_rate_limit_returns_429_when_exceeded(): void
    {
        // Simulate hitting rate limit
        $this->app->make('config')->set('app.rate_limit', 1);

        // First request should succeed
        $response1 = $this->getJson('/api/v1/posts');
        $response1->assertOk();

        // Second request should be rate limited
        $response2 = $this->getJson('/api/v1/posts');
        $response2->assertStatus(429)
            ->assertJson([
                'message' => 'Too Many Attempts',
            ]);
    }
}
```

---

## Error Response Testing

```php
<?php

namespace Tests\Feature\Api;

use App\Exceptions\CustomException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorResponseTest extends TestCase
{
    public function test_404_returns_consistent_format(): void
    {
        $response = $this->getJson('/api/v1/posts/99999');

        $response->assertNotFound()
            ->assertJsonStructure([
                'message',
                'error',
            ])
            ->assertJson([
                'error' => 'not_found',
            ]);
    }

    public function test_401_unauthenticated_format(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertUnauthorized()
            ->assertJsonStructure([
                'message',
                'error',
            ])
            ->assertJson([
                'error' => 'unauthenticated',
            ]);
    }

    public function test_403_forbidden_format(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertForbidden()
            ->assertJsonStructure([
                'message',
                'error',
            ])
            ->assertJson([
                'error' => 'forbidden',
            ]);
    }

    public function test_422_validation_error_format(): void
    {
        $response = $this->postJson('/api/v1/posts', []);

        $response->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_500_production_hides_details(): void
    {
        $this->app['config']->set('app.env', 'production');

        // Force an error
        $response = $this->getJson('/api/v1/error-trigger');

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Server error',
            ])
            ->assertJsonMissing(['exception', 'file', 'line']);
    }
}
```
