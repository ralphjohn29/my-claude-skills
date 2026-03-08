<?php

/**
 * Pest PHP Comparison Guide
 *
 * This file shows equivalent test syntax between PHPUnit and Pest PHP.
 * Pest is a testing framework with a focus on simplicity, built on top of PHPUnit.
 *
 * Installation:
 * composer require pestphp/pest --dev
 * php artisan pest:install
 *
 * Run Pest tests:
 * vendor/bin/pest
 * vendor/bin/pest --parallel
 * vendor/bin/pest --coverage
 */

// ==================== BASIC TEST STRUCTURE ====================

// PHPUnit
class ExampleTest extends TestCase
{
    public function test_basic_assertion(): void
    {
        $this->assertTrue(true);
    }
}

// Pest PHP
test('basic assertion', function () {
    expect(true)->toBeTrue();
});

// ==================== AAA PATTERN ====================

// PHPUnit
public function test_user_can_create_post(): void
{
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ]);

    // Assert
    $response->assertCreated();
}

// Pest PHP
test('user can create post', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ]);

    // Assert
    $response->assertCreated();
});

// ==================== DATA PROVIDERS ====================

// PHPUnit
/**
 * @dataProvider emailProvider
 */
public function test_validates_email(string $email, bool $expected): void
{
    $this->assertEquals($expected, filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
}

public static function emailProvider(): array
{
    return [
        'valid email' => ['test@example.com', true],
        'invalid email' => ['invalid', false],
    ];
}

// Pest PHP
dataset('emails', [
    'valid email' => ['test@example.com', true],
    'invalid email' => ['invalid', false],
]);

test('validates email', function (string $email, bool $expected) {
    expect(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
        ->toBe($expected);
})->with('emails');

// ==================== EXCEPTIONS ====================

// PHPUnit
public function test_throws_exception(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid value');

    throw new InvalidArgumentException('Invalid value');
}

// Pest PHP
test('throws exception', function () {
    throw new InvalidArgumentException('Invalid value');
})->throws(InvalidArgumentException::class, 'Invalid value');

// ==================== HTTP TESTS ====================

// PHPUnit
public function test_get_users(): void
{
    $response = $this->getJson('/api/users');

    $response->assertOk()
        ->assertJsonCount(10, 'data');
}

// Pest PHP
test('get users', function () {
    getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(10, 'data');
});

// ==================== AUTHENTICATION ====================

// PHPUnit
public function test_authenticated_request(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/user');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id);
}

// Pest PHP
test('authenticated request', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

// ==================== DATABASE ASSERTIONS ====================

// PHPUnit
public function test_creates_user(): void
{
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
    ]);
}

// Pest PHP
test('creates user', function () {
    $user = User::factory()->create();

    expect($user->id)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
    ]);
});

// ==================== MOCKING ====================

// PHPUnit
public function test_mock_service(): void
{
    $mock = $this->mock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('charge')
            ->once()
            ->andReturn(['status' => 'success']);
    });

    // Use mock...
}

// Pest PHP
test('mock service', function () {
    mock(PaymentService::class)
        ->shouldReceive('charge')
        ->once()
        ->andReturn(['status' => 'success']);

    // Use mock...
});

// ==================== GROUPS & HOOKS ====================

// PHPUnit
/**
 * @group api
 */
class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup code
    }

    protected function tearDown(): void
    {
        // Cleanup code
        parent::tearDown();
    }
}

// Pest PHP
beforeEach(function () {
    // Setup code
});

afterEach(function () {
    // Cleanup code
});

test('api test', function () {
    // Test code
})->group('api');

// ==================== SKIP & TODO ====================

// PHPUnit
public function test_something(): void
{
    $this->markTestSkipped('Feature not implemented yet');
}

public function test_todo(): void
{
    $this->markTestIncomplete('Need to implement this');
}

// Pest PHP
test('something', function () {
    // Test code
})->skip('Feature not implemented yet');

test('todo', function () {
    // Test code
})->todo();

// ==================== PEST EXPECTATIONS (THE PEST WAY) ====================

// Pest's fluent expectations API
test('user expectations', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user)
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com')
        ->email->toContain('@')
        ->toBeInstanceOf(User::class);
});

// Chained expectations
test('array expectations', function () {
    $data = ['a', 'b', 'c'];

    expect($data)
        ->toBeArray()
        ->toHaveCount(3)
        ->toContain('a', 'b')
        ->not->toContain('d');
});

// ==================== DEPENDENCY INJECTION ====================

// PHPUnit
public function test_with_dependency(): void
{
    $service = app(PaymentService::class);
    // or
    $service = $this->app->make(PaymentService::class);

    $this->assertInstanceOf(PaymentService::class, $service);
}

// Pest PHP
test('with dependency', function (PaymentService $service) {
    expect($service)->toBeInstanceOf(PaymentService::class);
});

// ==================== TESTING TIME ====================

// PHPUnit
public function test_time_manipulation(): void
{
    Carbon::setTestNow('2024-01-01 00:00:00');

    $this->assertEquals('2024-01-01', now()->format('Y-m-d'));

    $this->travel(5)->days();
    $this->assertEquals('2024-01-06', now()->format('Y-m-d'));

    Carbon::setTestNow(); // Reset
}

// Pest PHP
test('time manipulation', function () {
    Carbon::setTestNow('2024-01-01 00:00:00');

    expect(now()->format('Y-m-d'))->toBe('2024-01-01');

    travel(5)->days();

    expect(now()->format('Y-m-d'))->toBe('2024-01-06');

    Carbon::setTestNow();
});

// ==================== PEST ARCH TESTING ====================

// Pest allows architectural testing
test('controllers extend base controller', function () {
    expect('App\Http\Controllers')
        ->toExtend('App\Http\Controllers\Controller');
});

test('models are in Models namespace', function () {
    expect('App\Models')
        ->toBeClasses()
        ->toExtend('Illuminate\Database\Eloquent\Model');
});

// ==================== COMPARISON SUMMARY ====================

/*
| Feature              | PHPUnit                    | Pest PHP                        |
|---------------------|----------------------------|---------------------------------|
| Basic test          | test_method_name()         | test('name', fn())              |
| Assertions          | $this->assertEquals()      | expect()->toBe()                |
| Data providers      | @dataProvider              | ->with('dataset')               |
| Exceptions          | expectException()          | ->throws()                      |
| Setup/Tear down     | setUp()/tearDown()         | beforeEach()/afterEach()        |
| Groups              | @group                     | ->group()                       |
| Skip                | markTestSkipped()          | ->skip()                        |
| HTTP tests          | $this->getJson()           | getJson()                       |
| Authentication      | $this->actingAs()          | actingAs()                      |
| Datasets            | Static method              | dataset() function              |
| Dependency inject   | app() helper               | Function parameters             |
| Arch testing        | Not built-in              | Built-in                        |

WHEN TO USE WHICH:

Use PHPUnit when:
- Team is already familiar with PHPUnit
- Working on existing PHPUnit projects
- Need maximum compatibility with CI tools
- Using PHPUnit-specific features

Use Pest PHP when:
- Starting a new project
- Want more readable, fluent syntax
- Want architectural testing
- Prefer functional testing style
- Want faster test writing with less boilerplate
*/
