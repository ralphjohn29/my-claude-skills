---
name: phpunit-testing-pro
description: Senior-level PHPUnit testing skill for Laravel/PHP applications. Use PROACTIVELY when writing tests, creating test suites, mocking dependencies, testing APIs, database testing, or improving test coverage. Covers PHPUnit 10+, Laravel testing helpers, data providers, mocks/stubs, Pest PHP comparison, and testing best practices. Trigger for test creation, test debugging, coverage improvement, or any testing-related questions.
---

# PHPUnit Testing Pro

A comprehensive testing skill for PHP/Laravel applications following industry best practices. Covers PHPUnit 10+, Laravel testing helpers, and modern testing patterns.

## Core Philosophy

Tests should be:
- **Fast** - Isolated, no external dependencies in unit tests
- **Isolated** - Each test is independent, can run in any order
- **Repeatable** - Same result every time, no flaky tests
- **Self-validating** - Clear pass/fail assertions
- **Timely** - Written alongside or before code (TDD)

---

## Quick Start

### Running Tests

```bash
# Run all tests
php artisan test
# or
vendor/bin/phpunit

# Run specific file
php artisan test --filter=UserTest

# Run specific method
php artisan test --filter=test_user_can_login

# Run by group
php artisan test --group=api

# Run in parallel (faster)
php artisan test --parallel

# With coverage
php artisan test --coverage
php artisan test --coverage --min=80
```

### Test Structure (AAA Pattern)

```php
public function test_user_can_create_post(): void
{
    // Arrange - Set up test data
    $user = User::factory()->create();
    $category = Category::factory()->create();

    // Act - Perform the action
    $response = $this->actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Content here',
            'category_id' => $category->id,
        ]);

    // Assert - Verify the outcome
    $response->assertCreated()
        ->assertJsonPath('data.title', 'Test Post');

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
        'user_id' => $user->id,
    ]);
}
```

---

## Test Organization

```
tests/
├── Unit/                    # Fast, isolated tests
│   ├── Models/
│   │   └── UserTest.php
│   ├── Services/
│   │   └── PaymentServiceTest.php
│   └── Helpers/
│       └── StringHelperTest.php
├── Feature/                 # HTTP/API tests
│   ├── Api/
│   │   └── v1/
│   │       ├── PostControllerTest.php
│   │       └── UserControllerTest.php
│   ├── Auth/
│   │   └── AuthenticationTest.php
│   └── Web/
│       └── DashboardTest.php
├── Integration/             # Database/API integration
│   └── OrderProcessingTest.php
└── TestCase.php             # Base test class
```

---

## Essential Test Types

### 1. Unit Tests (No Laravel)

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\TaxCalculator;

class TaxCalculatorTest extends TestCase
{
    public function test_calculates_tax_correctly(): void
    {
        $calculator = new TaxCalculator();

        $result = $calculator->calculate(100, 0.20);

        $this->assertEquals(20.0, $result);
    }

    public function test_throws_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $calculator = new TaxCalculator();
        $calculator->calculate(-100, 0.20);
    }
}
```

### 2. Feature Tests (Full Laravel)

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'title' => 'Test Post',
                'content' => 'Test content here',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'content', 'created_at'],
            ]);
    }
}
```

---

## Data Providers

Use data providers for testing multiple scenarios:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase
{
    /**
     * @dataProvider validEmailProvider
     */
    public function test_validates_correct_emails(string $email): void
    {
        $this->assertTrue(EmailValidator::isValid($email));
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_rejects_invalid_emails(string $email): void
    {
        $this->assertFalse(EmailValidator::isValid($email));
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple email' => ['user@example.com'],
            'with subdomain' => ['user@sub.example.com'],
            'with plus' => ['user+tag@example.com'],
            'with numbers' => ['user123@example.com'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'no @ symbol' => ['userexample.com'],
            'no domain' => ['user@'],
            'no local part' => ['@example.com'],
            'multiple @' => ['user@@example.com'],
            'empty string' => [''],
        ];
    }
}
```

---

## Mocking Strategies

### Mocking Services

```php
public function test_uses_payment_service(): void
{
    $paymentService = $this->mock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('charge')
            ->once()
            ->with(100.00, 'usd')
            ->andReturn(['status' => 'success', 'id' => 'txn_123']);
    });

    // Or using partial mock
    $paymentService = $this->partialMock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('charge')->once();
    });

    $this->app->instance(PaymentService::class, $paymentService);

    // Test your code
}
```

### Mocking Facades

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

public function test_caches_result(): void
{
    Cache::shouldReceive('remember')
        ->once()
        ->with('posts.all', 3600, \Closure::class)
        ->andReturn(collect());

    $response = $this->get('/api/posts');
}

public function test_dispatches_job(): void
{
    Queue::fake();

    // Act
    $response = $this->post('/api/orders', $orderData);

    Queue::assertPushed(ProcessOrder::class, function ($job) use ($orderData) {
        return $job->orderId === $orderData['id'];
    });
}

public function test_sends_email(): void
{
    Mail::fake();

    $user = User::factory()->create();
    $user->notify(new OrderShipped($order));

    Mail::assertSent(OrderShippedEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
}
```

---

## Database Testing

### RefreshDatabase vs DatabaseTransactions

```php
// Use RefreshDatabase for feature tests (migrates once)
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;
}

// Use DatabaseMigrations for each test to have fresh migrations
use Illuminate\Foundation\Testing\DatabaseMigrations;

// Use DatabaseTransactions for unit tests (faster, wraps in transaction)
use Illuminate\Foundation\Testing\DatabaseTransactions;
```

### Database Assertions

```php
// Check record exists
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
    'active' => true,
]);

// Check record doesn't exist
$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

// Check count
$this->assertDatabaseCount('posts', 5);

// Check model exists
$this->assertModelExists($post);

// Check model is missing (soft deleted)
$this->assertModelMissing($post);

// Check soft deletes
$this->assertSoftDeleted($post);
```

---

## HTTP Test Assertions

```php
// Status codes
$response->assertOk();           // 200
$response->assertCreated();      // 201
$response->assertAccepted();     // 202
$response->assertNoContent();    // 204
$response->assertBadRequest();   // 400
$response->assertUnauthorized(); // 401
$response->assertForbidden();    // 403
$response->assertNotFound();     // 404
$response->assertUnprocessable();// 422

// JSON assertions
$response->assertJson(['message' => 'Success']);
$response->assertJsonPath('data.user.name', 'John');
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(5, 'data');
$response->assertJsonFragment(['status' => 'active']);

// Validation errors
$response->assertJsonValidationErrors(['email', 'password']);
$response->assertJsonMissingValidationErrors(['name']);

// Session assertions
$response->assertSessionHas('message', 'Success!');
$response->assertSessionHasErrors(['email']);
$response->assertSessionHasNoErrors();

// View assertions
$response->assertViewIs('posts.index');
$response->assertViewHas('posts');
$response->assertSee('Post Title');
$response->assertDontSee('Hidden Content');
```

---

## File Upload Testing

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

public function test_avatar_upload(): void
{
    Storage::fake('avatars');

    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    $response = $this->post('/api/user/avatar', [
        'avatar' => $file,
    ]);

    Storage::disk('avatars')->assertExists($file->hashName());
}

public function test_document_upload(): void
{
    Storage::fake('documents');

    $file = UploadedFile::fake()
        ->create('document.pdf', 1000, 'application/pdf');

    $response = $this->post('/api/documents', [
        'document' => $file,
    ]);

    $response->assertOk();
}
```

---

## Exception Testing

```php
// PHPUnit style
public function test_throws_exception(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid value');
    $this->expectExceptionCode(100);

    throw new InvalidArgumentException('Invalid value', 100);
}

// Laravel style
public function test_exception_is_reported(): void
{
    Exceptions::fake();

    $response = $this->get('/api/error');

    Exceptions::assertReported(CustomException::class);
}

// AssertThrows
public function test_assert_throws(): void
{
    $this->assertThrows(
        fn() => (new PaymentService())->charge(-100),
        InvalidArgumentException::class
    );
}
```

---

## Time Manipulation

```php
use Illuminate\Support\Carbon;

public function test_expires_after_week(): void
{
    Carbon::setTestNow('2024-01-01 00:00:00');

    $link = InviteLink::create(['expires_at' => now()->addWeek()]);

    $this->travel(6)->days();
    $this->assertFalse($link->isExpired());

    $this->travel(1)->days();  // Now 7 days
    $this->assertTrue($link->isExpired());

    $this->travelBack(); // Reset time

    // Or with closure
    $this->travelTo(now()->addYear(), function () {
        // Test future behavior
    });
}
```

---

## Reference Files

For detailed patterns, see:
- `references/unit-testing-patterns.md` - Unit test organization and patterns
- `references/mocking-guide.md` - Complete mocking reference
- `references/api-testing.md` - REST API testing strategies
- `references/data-providers.md` - Advanced data provider patterns
- `references/assertions-cheatsheet.md` - Complete assertion reference

---

## Test Templates

Copy-ready test templates for common scenarios:
- `templates/UnitServiceTest.php` - Unit test template for service classes
- `templates/UnitModelTest.php` - Unit test template for Eloquent models
- `templates/FeatureApiTest.php` - Feature test template for API controllers
- `templates/FeatureAuthTest.php` - Feature test template for authentication
- `templates/PestComparison.php` - PHPUnit vs Pest PHP comparison guide

---

## Common Commands

```bash
# Create test
php artisan make:test Feature/PostControllerTest
php artisan make:test Unit/Services/PaymentServiceTest --unit

# Run with filter
php artisan test --filter="UserTest"
php artisan test --filter="test_user_can"

# Run by path
php artisan test tests/Feature/Api

# Parallel execution
php artisan test --parallel --processes=4

# Stop on failure
php artisan test --stop-on-failure

# Verbose output
php artisan test -v

# Coverage report
php artisan test --coverage-html=coverage
```

---

## Best Practices Checklist

- [ ] One assertion concept per test (but multiple assertions OK)
- [ ] Descriptive test names: `test_user_can_login_with_valid_credentials`
- [ ] Use `$this->actingAs($user)` for authenticated requests
- [ ] Prefer `postJson` for API tests
- [ ] Use factories for test data
- [ ] Mock external services and API calls
- [ ] Use data providers for edge cases
- [ ] Keep unit tests isolated (no database/framework)
- [ ] Use RefreshDatabase for database tests
- [ ] Test both happy path and error cases
- [ ] Assert JSON structure, not just values
- [ ] Use `$response->dump()` for debugging
