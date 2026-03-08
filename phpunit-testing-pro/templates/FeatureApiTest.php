<?php

declare(strict_types=1);

namespace Tests\Feature\Api\v1;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * Feature test template for API controllers.
 *
 * This template demonstrates:
 * - Full Laravel integration testing
 * - REST API CRUD operations
 * - Authentication testing
 * - Validation testing
 * - File upload testing
 * - Queue/Job testing
 * - HTTP response assertions
 *
 * Run with: php artisan test --filter=PostControllerTest
 */
#[Group('api')]
class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/posts';

    // ==================== INDEX TESTS ====================

    #[Test]
    #[TestDox('It lists posts with pagination')]
    public function lists_posts_with_pagination(): void
    {
        // Arrange
        Post::factory()->count(25)->create();

        // Act
        $response = $this->getJson($this->baseUrl);

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'excerpt', 'created_at', 'updated_at']
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.current_page', 1);
    }

    #[Test]
    #[TestDox('It filters posts by status')]
    public function filters_posts_by_status(): void
    {
        // Arrange
        Post::factory()->count(5)->published()->create();
        Post::factory()->count(3)->draft()->create();

        // Act
        $response = $this->getJson("{$this->baseUrl}?status=published");

        // Assert
        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    #[TestDox('It searches posts by title')]
    public function searches_posts_by_title(): void
    {
        // Arrange
        Post::factory()->create(['title' => 'Laravel Testing Guide']);
        Post::factory()->create(['title' => 'Vue.js Components']);
        Post::factory()->create(['title' => 'Laravel Best Practices']);

        // Act
        $response = $this->getJson("{$this->baseUrl}?search=Laravel");

        // Assert
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    #[TestDox('It sorts posts by date')]
    public function sorts_posts_by_date(): void
    {
        // Arrange
        $oldest = Post::factory()->create(['created_at' => now()->subDays(2)]);
        $middle = Post::factory()->create(['created_at' => now()->subDay()]);
        $newest = Post::factory()->create(['created_at' => now()]);

        // Act - Sort ascending
        $response = $this->getJson("{$this->baseUrl}?sort=created_at&order=asc");

        // Assert
        $response->assertJsonPath('data.0.id', $oldest->id);

        // Act - Sort descending
        $response = $this->getJson("{$this->baseUrl}?sort=created_at&order=desc");

        // Assert
        $response->assertJsonPath('data.0.id', $newest->id);
    }

    // ==================== SHOW TESTS ====================

    #[Test]
    #[TestDox('It shows a single post with details')]
    public function shows_single_post(): void
    {
        // Arrange
        $post = Post::factory()
            ->hasComments(3)
            ->create();

        // Act
        $response = $this->getJson("{$this->baseUrl}/{$post->id}");

        // Assert
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
                    'comments' => ['*' => ['id', 'content', 'user_id']],
                ],
            ]);
    }

    #[Test]
    #[TestDox('It returns 404 for non-existent post')]
    public function returns_404_for_nonexistent_post(): void
    {
        // Act
        $response = $this->getJson("{$this->baseUrl}/99999");

        // Assert
        $response->assertNotFound()
            ->assertJson([
                'message' => 'Post not found',
            ]);
    }

    // ==================== STORE TESTS ====================

    #[Test]
    #[TestDox('Guest cannot create post')]
    public function guest_cannot_create_post(): void
    {
        // Act
        $response = $this->postJson($this->baseUrl, [
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        // Assert
        $response->assertUnauthorized();
    }

    #[Test]
    #[TestDox('Authenticated user can create post')]
    public function authenticated_user_can_create_post(): void
    {
        // Arrange
        $user = Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();

        // Act
        $response = $this->postJson($this->baseUrl, [
            'title' => 'New Post Title',
            'content' => 'This is the post content with enough length.',
            'excerpt' => 'Short excerpt',
            'category_id' => $category->id,
            'tags' => ['laravel', 'testing'],
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonPath('data.title', 'New Post Title')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('posts', [
            'title' => 'New Post Title',
            'user_id' => $user->id,
        ]);
    }

    // ==================== VALIDATION TESTS ====================

    #[Test]
    #[DataProvider('invalidPostDataProvider')]
    #[TestDox('It validates post data: $scenario')]
    public function validates_post_data(string $scenario, array $data, array $expectedErrors): void
    {
        // Arrange
        Sanctum::actingAs(User::factory()->create());

        // Act
        $response = $this->postJson($this->baseUrl, $data);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    public static function invalidPostDataProvider(): array
    {
        return [
            'empty data' => [
                'scenario' => 'empty data',
                'data' => [],
                'expectedErrors' => ['title', 'content'],
            ],
            'title too short' => [
                'scenario' => 'title too short',
                'data' => ['title' => 'AB', 'content' => 'Valid content here'],
                'expectedErrors' => ['title'],
            ],
            'content too short' => [
                'scenario' => 'content too short',
                'data' => ['title' => 'Valid Title', 'content' => 'Short'],
                'expectedErrors' => ['content'],
            ],
            'invalid category' => [
                'scenario' => 'invalid category',
                'data' => ['title' => 'Valid', 'content' => 'Valid content', 'category_id' => 999],
                'expectedErrors' => ['category_id'],
            ],
        ];
    }

    // ==================== UPDATE TESTS ====================

    #[Test]
    #[TestDox('User can update own post')]
    public function user_can_update_own_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        Sanctum::actingAs($user);

        // Act
        $response = $this->putJson("{$this->baseUrl}/{$post->id}", [
            'title' => 'Updated Title',
            'content' => $post->content,
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    #[TestDox('User cannot update others post')]
    public function user_cannot_update_others_post(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner, 'author')->create();

        Sanctum::actingAs(User::factory()->create());

        // Act
        $response = $this->putJson("{$this->baseUrl}/{$post->id}", [
            'title' => 'Hacked Title',
        ]);

        // Assert
        $response->assertForbidden();
    }

    // ==================== DESTROY TESTS ====================

    #[Test]
    #[TestDox('User can delete own post')]
    public function user_can_delete_own_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->for($user, 'author')->create();

        Sanctum::actingAs($user);

        // Act
        $response = $this->deleteJson("{$this->baseUrl}/{$post->id}");

        // Assert
        $response->assertNoContent();

        $this->assertSoftDeleted($post);
    }

    // ==================== FILE UPLOAD TESTS ====================

    #[Test]
    #[TestDox('User can upload featured image')]
    public function user_can_upload_featured_image(): void
    {
        // Arrange
        Storage::fake('public');
        $user = Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        // Act
        $response = $this->postJson("{$this->baseUrl}/upload", [
            'image' => $file,
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['data' => ['url']]);

        Storage::disk('public')->assertExists('posts/' . $file->hashName());
    }

    #[Test]
    #[TestDox('Image upload validates file type')]
    public function image_upload_validates_file_type(): void
    {
        // Arrange
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        // Act
        $response = $this->postJson("{$this->baseUrl}/upload", [
            'image' => $file,
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    // ==================== QUEUE TESTS ====================

    #[Test]
    #[TestDox('Creating post dispatches notification job')]
    public function creating_post_dispatches_notification_job(): void
    {
        // Arrange
        Queue::fake();
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();

        // Act
        $this->postJson($this->baseUrl, [
            'title' => 'New Post',
            'content' => 'Content for the post.',
            'category_id' => $category->id,
        ]);

        // Assert
        Queue::assertPushed(\App\Jobs\SendPostNotification::class);
    }
}
