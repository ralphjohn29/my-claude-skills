---
name: laravel-api-expert
description: Expert Laravel API backend development for Laravel 10/11+. Use this skill whenever the user mentions Laravel API, REST API with Laravel, building backend APIs, Laravel controllers, API routes, Laravel authentication (Sanctum/Passport), API resources, form requests, API versioning, or any Laravel backend development task. Also trigger for Laravel migration, model, seeder, factory creation, and API testing.
---
# Laravel API Expert

A comprehensive skill for building production-grade REST APIs with Laravel 10/11+. Covers architecture, authentication, validation, response transformation, testing, and deployment best practices.

## Core Philosophy

Build APIs that are:
- **Consistent** - Uniform response formats and error handling
- **Secure** - Proper authentication, authorization, and input validation
- **Performant** - Optimized queries, caching, and resource management
- **Maintainable** - Clean architecture, versioning, and documentation
- **Testable** - Comprehensive test coverage with factories and seeders

---

## Project Structure

Organize API code with clear separation of concerns:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── v1/
│   │           ├── UserController.php
│   │           └── PostController.php
│   ├── Requests/
│   │   └── Api/
│   │       └── v1/
│   │           ├── StoreUserRequest.php
│   │           └── UpdatePostRequest.php
│   └── Resources/
│       └── v1/
│           ├── UserResource.php
│           └── PostResource.php
├── Models/
│   ├── User.php
│   └── Post.php
├── DTOs/                    # Data Transfer Objects
│   └── UserDTO.php
├── Services/                # Business logic layer
│   └── UserService.php
├── Repositories/            # Data access layer (optional)
│   └── UserRepository.php
└── Exceptions/
    └── ApiException.php

routes/
└── api.php                  # API routes (auto-prefixed with /api)

database/
├── migrations/
├── factories/
└── seeders/
```

---

## Routing (routes/api.php)

### Basic API Routes

```php
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

// Public endpoints
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Read-only public resources
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
});

// Authenticated endpoints
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/user', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'updateProfile']);

    // CRUD resources
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    Route::apiResource('users.comments', CommentController::class)->shallow();
});
```

### Route Resource Methods

`Route::apiResource()` automatically creates these routes:

| Method | URI | Action | Route Name |
|--------|-----|--------|------------|
| GET | `/posts` | index | posts.index |
| GET | `/posts/{id}` | show | posts.show |
| POST | `/posts` | store | posts.store |
| PUT/PATCH | `/posts/{id}` | update | posts.update |
| DELETE | `/posts/{id}` | destroy | posts.destroy |

---

## Controllers

Generate API controllers with:

```bash
php artisan make:controller Api/v1/PostController --api --model=Post
```

### Standard API Controller Pattern

```php
<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StorePostRequest;
use App\Http\Requests\Api\v1\UpdatePostRequest;
use App\Http\Resources\v1\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Display a listing of posts.
     */
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->with(['author', 'category'])
            ->published()
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Post created successfully',
            'data' => new PostResource($post),
        ], 201);
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): PostResource
    {
        $post->load(['author', 'category', 'comments.user']);

        return new PostResource($post);
    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post = $this->postService->update($post, $request->validated());

        return response()->json([
            'message' => 'Post updated successfully',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ], 204);
    }
}
```

---

## Form Requests (Validation)

Generate with:

```bash
php artisan make:request Api/v1/StorePostRequest
```

### Form Request Pattern

```php
<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Or auth logic: $this->user()->can('create', Post::class)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'min:3'],
            'content' => ['required', 'string', 'min:10'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A post title is required',
            'content.min' => 'Post content must be at least 10 characters',
            'category_id.exists' => 'The selected category is invalid',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str($this->title)->slug(),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
```

### Common Validation Rules

```php
// Strings
'title' => ['required', 'string', 'min:3', 'max:255'],
'slug' => ['required', 'string', 'alpha_dash', 'unique:posts,slug'],

// Numbers
'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
'quantity' => ['required', 'integer', 'min:1'],

// Dates
'start_date' => ['required', 'date', 'after:today'],
'end_date' => ['required', 'date', 'after:start_date'],

// Relationships
'category_id' => ['required', 'exists:categories,id'],
'tags' => ['array'],
'tags.*' => ['exists:tags,id'],

// Files
'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
'document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],

// Email & Password
'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[0-9]/'],

// Conditional
'reminder' => ['required_if:status,pending', 'nullable', 'date'],
```

---

## API Resources (Response Transformation)

Generate with:

```bash
php artisan make:resource v1/PostResource
php artisan make:resource v1/PostCollection
```

### Single Resource

```php
<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when(
                $request->routeIs('posts.show'),
                $this->content
            ),
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships (loaded only if available)
            'author' => UserResource::make($this->whenLoaded('author')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            // Computed fields
            'read_time' => $this->read_time,
            'comments_count' => $this->whenCounted('comments'),

            // Conditional metadata
            'meta' => $this->when(
                $request->user()?->isAdmin(),
                ['views' => $this->views, 'internal_notes' => $this->notes]
            ),

            // Links
            'links' => [
                'self' => route('api.v1.posts.show', $this->id),
                'author' => route('api.v1.users.show', $this->author_id),
            ],
        ];
    }

    /**
     * Customize the outgoing response.
     */
    public function withResponse(Request $request, $response): void
    {
        $response->setStatusCode(200);
        $response->header('X-Resource-Type', 'Post');
    }
}
```

### Resource Collection with Metadata

```php
<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'from' => $this->firstItem(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }
}
```

---

## Authentication

### Laravel Sanctum (Recommended for APIs)

**Installation:**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Configuration (bootstrap/app.php in Laravel 11):**

```php
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
})
```

**AuthController:**

```php
<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use App\Http\Requests\Api\v1\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Login and issue token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken(
            $request->device_name ?? 'api-token',
            $request->abilities ?? ['*']
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices.
     */
    public function logoutAll(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }
}
```

**Token Abilities (Scopes):**

```php
// Issue token with specific abilities
$token = $user->createToken('api-token', ['read', 'write'])->plainTextToken;

// Check abilities in middleware or controller
if ($request->user()->tokenCan('write')) {
    // Allow write operations
}

// Custom middleware for abilities
Route::middleware(['auth:sanctum', 'abilities:write'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
});
```

---

## Models & Eloquent Best Practices

### Model with All Features

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'author_id',
        'status',
        'featured_image',
        'published_at',
    ];

    protected $casts = [
        'status' => PostStatus::class,  // Enum casting
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $with = ['category'];  // Auto-load relationship

    protected $appends = ['read_time'];  // Computed attributes

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (self $post) {
            $post->slug = $post->slug ?? Str::slug($post->title);
            $post->author_id = $post->author_id ?? auth()->id();
        });

        static::updating(function (self $post) {
            if ($post->isDirty('title') && !$post->isDirty('slug')) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps()
            ->withPivot('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished($query)
    {
        return $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PostStatus::Draft);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getReadTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return (int) ceil($wordCount / 200);  // 200 words per minute
    }

    public function getExcerptAttribute($value): string
    {
        return $value ?? Str::limit(strip_tags($this->content), 150);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published
            && $this->published_at?->isPast();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->author_id === $user->id;
    }
}
```

### PHP Enum for Status

```php
<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Published => 'green',
            self::Archived => 'red',
        };
    }
}
```

---

## Migrations

### Comprehensive Migration Example

```php
<?php

use App\Enums\PostStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();

            $table->string('status')->default(PostStatus::Draft->value);
            $table->timestamp('published_at')->nullable();

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Metrics
            $table->unsignedInteger('views')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['status', 'published_at']);
            $table->index('created_at');
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

---

## Error Handling

### Custom API Exception Handler

In Laravel 11, customize exception handling in `bootstrap/app.php`:

```php
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (Throwable $e, Request $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return match (true) {
                $e instanceof ValidationException => response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422),

                $e instanceof NotFoundHttpException => response()->json([
                    'message' => 'Resource not found',
                    'error' => 'not_found',
                ], 404),

                $e instanceof \Illuminate\Auth\AuthenticationException => response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'unauthenticated',
                ], 401),

                $e instanceof \Illuminate\Auth\Access\AuthorizationException => response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'forbidden',
                ], 403),

                default => response()->json([
                    'message' => app()->isLocal() ? $e->getMessage() : 'Server error',
                    'error' => 'server_error',
                ], 500),
            };
        }
    });
})
```

### Consistent API Response Trait

```php
<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message, int $code, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function createdResponse(mixed $data, string $message = 'Resource created'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }
}
```

---

## API Versioning

### URL-Based Versioning (Recommended)

```
app/Http/Controllers/Api/
├── v1/
│   ├── PostController.php
│   └── UserController.php
└── v2/
    ├── PostController.php
    └── UserController.php
```

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // v1 routes
});

Route::prefix('v2')->group(function () {
    // v2 routes with new features
});
```

### Header-Based Versioning

```php
// In a middleware
public function handle($request, Closure $next)
{
    $version = $request->header('Accept-Version', 'v1');

    if ($version === 'v2') {
        $request->attributes->set('api_version', 'v2');
    }

    return $next($request);
}
```

---

## Query Optimization

### Prevent N+1 Queries

```php
// BAD - N+1 problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;  // Additional query for each post
}

// GOOD - Eager loading
$posts = Post::with(['author', 'category', 'tags'])->get();

// GOOD - Lazy eager loading when needed
$posts = Post::all();
$posts->load('author');  // Load only when needed
```

### Query Scopes for Filtering

```php
// In controller
public function index(Request $request)
{
    $query = Post::query()->with(['author', 'category']);

    // Apply filters
    $query->when($request->category, fn($q, $category) =>
        $q->where('category_id', $category)
    );

    $query->when($request->status, fn($q, $status) =>
        $q->where('status', $status)
    );

    $query->when($request->search, fn($q, $search) =>
        $q->where('title', 'LIKE', "%{$search}%")
    );

    $query->when($request->has('published'), fn($q) =>
        $q->published()
    );

    // Date range filter
    $query->when($request->from_date, fn($q, $date) =>
        $q->whereDate('created_at', '>=', $date)
    );

    // Sorting
    $sortField = $request->input('sort_by', 'created_at');
    $sortOrder = $request->input('sort_order', 'desc');
    $query->orderBy($sortField, $sortOrder);

    return PostResource::collection($query->paginate($request->per_page ?? 15));
}
```

---

## Testing API Endpoints

### Feature Tests

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

    /** @test */
    public function guest_can_list_published_posts(): void
    {
        Post::factory()->count(5)->published()->create();
        Post::factory()->count(3)->draft()->create();

        $response = $this->getJson('/api/v1/posts');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'excerpt', 'author'],
                ],
                'meta' => ['current_page', 'total'],
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_post(): void
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $postData = [
            'title' => 'Test Post',
            'content' => 'This is the content of the test post.',
            'category_id' => Category::factory()->create()->id,
        ];

        $response = $this->postJson('/api/v1/posts', $postData);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Test Post');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'author_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_can_only_update_own_posts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $post = Post::factory()->for($otherUser, 'author')->create();

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function validation_fails_for_invalid_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/posts', [
            'title' => '',  // Required
            'content' => 'Short',  // Min 10 chars
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content']);
    }
}
```

### Factory Example

```php
<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'status' => PostStatus::Draft,
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function forAuthor(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'author_id' => $user->id,
        ]);
    }
}
```

---

## Common Commands Reference

```bash
# Create controller
php artisan make:controller Api/v1/PostController --api --model=Post

# Create resource
php artisan make:resource v1/PostResource
php artisan make:resource v1/PostCollection

# Create form request
php artisan make:request Api/v1/StorePostRequest

# Create model with everything
php artisan make:model Post -mfsc
# -m migration, -f factory, -s seeder, -c controller

# Create migration
php artisan make:migration create_posts_table
php artisan make:migration add_status_to_posts_table --table=posts

# Run migrations
php artisan migrate
php artisan migrate:fresh --seed  # Reset and seed

# Create policy
php artisan make:policy PostPolicy --model=Post

# Create test
php artisan make:test Feature/Api/v1/PostControllerTest

# Run tests
php artisan test --filter=PostController
php artisan test --parallel  # Run in parallel

# Cache (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache
php artisan optimize:clear
```

---

## Security Checklist

1. **Input Validation** - Always validate with Form Requests
2. **Authorization** - Use Policies for resource access control
3. **Authentication** - Use Sanctum tokens, never expose sensitive data
4. **SQL Injection** - Use Eloquent or parameterized queries
5. **Rate Limiting** - Configure in `AppServiceProvider` or routes
6. **CORS** - Configure in `config/cors.php`
7. **HTTPS** - Always use HTTPS in production
8. **Sensitive Data** - Never log tokens, passwords, or PII
9. **File Uploads** - Validate file types, sizes, and store outside public
10. **API Tokens** - Use short-lived tokens with refresh capability

---

## Quick Reference: Response Status Codes

| Code | Meaning | Use Case |
|------|---------|----------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST creating resource |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but not authorized |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Unexpected server error |
