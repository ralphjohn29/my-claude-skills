# Mocking Guide

## Table of Contents
- [Quick Reference](#quick-reference)
- [Laravel Mock Helpers](#laravel-mock-helpers)
- [Mockery Patterns](#mockery-patterns)
- [Facade Mocking](#facade-mocking)
- [HTTP Mocking](#http-mocking)
- [Queue & Event Mocking](#queue--event-mocking)
- [Advanced Mocking](#advanced-mocking)

---

## Quick Reference

```php
// Full mock - all methods return null by default
$mock = $this->mock(Service::class);

// Partial mock - real methods unless mocked
$mock = $this->partialMock(Service::class);

// Spy - records calls, real methods execute
$spy = $this->spy(Service::class);

// Native PHPUnit mock
$mock = $this->createMock(Service::class);

// Mock with constructor args
$mock = $this->mock(Service::class, [$arg1, $arg2]);

// Instance binding (replaces in container)
$this->app->instance(Service::class, $mock);

// Mock builder for complex mocks
$mock = $this->getMockBuilder(Service::class)
    ->setConstructorArgs([$dependency])
    ->onlyMethods(['methodToMock'])
    ->getMock();
```

---

## Laravel Mock Helpers

### Basic Mocking

```php
<?php

namespace Tests\Feature;

use App\Services\PaymentService;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    public function test_mock_payment_service(): void
    {
        // Simple mock
        $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('process')
                ->once()
                ->andReturn(['status' => 'success']);
        });

        // Or with partial mock
        $this->partialMock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('validateCard')
                ->andReturn(true);
            // process() will use real implementation
        });

        $response = $this->post('/api/payments', [
            'amount' => 100,
            'card' => '4242424242424242',
        ]);

        $response->assertOk();
    }

    public function test_spy_on_service(): void
    {
        $spy = $this->spy(EmailService::class);

        // Real methods execute, but we can verify calls
        $response = $this->post('/api/notifications', ['message' => 'Hello']);

        // Verify the method was called
        $spy->shouldHaveReceived('send')
            ->with('Hello')
            ->once();
    }
}
```

### Mocking with Return Values

```php
public function test_mock_with_various_return_types(): void
{
    $this->mock(UserRepository::class, function ($mock) {
        // Return single value
        $mock->shouldReceive('find')
            ->with(1)
            ->andReturn(User::factory()->make());

        // Return different values for different calls
        $mock->shouldReceive('all')
            ->andReturn(
                collect([User::factory()->make()]), // First call
                collect([]),                        // Second call
                collect([User::factory()->make(), User::factory()->make()]) // Third call
            );

        // Return using closure (dynamic)
        $mock->shouldReceive('search')
            ->andReturnUsing(function ($term) {
                return User::where('name', 'like', "%{$term}%")->get();
            });

        // Return multiple values in sequence
        $mock->shouldReceive('getStatus')
            ->andReturnValues(['pending', 'processing', 'completed']);

        // Return null explicitly
        $mock->shouldReceive('findDeleted')
            ->andReturn(null);
    });
}
```

---

## Mockery Patterns

### Expectation Modifiers

```php
public function test_mockery_expectations(): void
{
    $mock = $this->mock(NotificationService::class);

    // Call count
    $mock->shouldReceive('send')
        ->once()          // Exactly once
        ->times(3)        // Exactly 3 times
        ->twice()         // Exactly 2 times
        ->never()         // Never called
        ->atLeast()->once()    // At least once
        ->atMost()->times(5)   // At most 5 times
        ->between(2, 5);       // Between 2 and 5 times

    // Argument matching
    $mock->shouldReceive('notify')
        ->with('value')                    // Exact match
        ->withAnyArgs()                    // Any arguments
        ->withNoArgs()                     // No arguments
        ->with(\Mockery::type('string'))   // Type match
        ->with(\Mockery::subset(['key' => 'value'])) // Array subset
        ->with(\Mockery::pattern('/^\d+$/')); // Regex match

    // Ordered calls
    $mock->shouldReceive('first')->once()->ordered();
    $mock->shouldReceive('second')->once()->ordered();
    $mock->shouldReceive('third')->once()->ordered();
}
```

### Argument Matchers

```php
public function test_argument_matchers(): void
{
    $mock = $this->mock(PaymentGateway::class);

    // Type matchers
    $mock->shouldReceive('charge')
        ->with(
            \Mockery::type('int'),      // Integer
            \Mockery::type('float'),    // Float
            \Mockery::type('string'),   // String
            \Mockery::type('array'),    // Array
            \Mockery::type('bool'),     // Boolean
            \Mockery::type('null'),     // Null
            \Mockery::type(User::class) // Class instance
        );

    // Custom matcher
    $mock->shouldReceive('process')
        ->with(\Mockery::on(function ($argument) {
            return $argument instanceof Order && $argument->total > 100;
        }));

    // Any value of any type
    $mock->shouldReceive('log')
        ->with(\Mockery::any());

    // Duck type (has method)
    $mock->shouldReceive('handle')
        ->with(\Mockery::ducktype('process', 'validate'));

    // Array matcher
    $mock->shouldReceive('create')
        ->with(\Mockery::contains('name', 'email')); // Array contains keys

    // Closure matcher
    $mock->shouldReceive('register')
        ->with(\Mockery::capture($capturedValue)); // Capture for later use
}
```

---

## Facade Mocking

### Cache

```php
use Illuminate\Support\Facades\Cache;

public function test_cache_facade(): void
{
    // Mock cache retrieval
    Cache::shouldReceive('get')
        ->with('user:1')
        ->andReturn(['name' => 'John']);

    Cache::shouldReceive('remember')
        ->with('posts.all', 3600, \Closure::class)
        ->andReturn(collect());

    Cache::shouldReceive('put')
        ->once()
        ->with('key', 'value', 60);

    Cache::shouldReceive('forget')
        ->with('user:1')
        ->andReturn(true);

    // Your code that uses Cache
}
```

### Database

```php
use Illuminate\Support\Facades\DB;

public function test_db_facade(): void
{
    // Mock raw query
    DB::shouldReceive('select')
        ->with('SELECT * FROM users WHERE id = ?', [1])
        ->andReturn([(object) ['id' => 1, 'name' => 'John']]);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });
}
```

### Config

```php
use Illuminate\Support\Facades\Config;

public function test_config_facade(): void
{
    Config::shouldReceive('get')
        ->with('app.timezone')
        ->andReturn('UTC');

    // Or just set config directly (simpler)
    Config::set('app.debug', true);
}
```

### Storage

```php
use Illuminate\Support\Facades\Storage;

public function test_storage_facade(): void
{
    Storage::fake('avatars');

    Storage::shouldReceive('put')
        ->once()
        ->with('avatars/test.jpg', 'content');

    Storage::shouldReceive('exists')
        ->with('avatars/test.jpg')
        ->andReturn(true);

    Storage::shouldReceive('delete')
        ->with('avatars/test.jpg')
        ->andReturn(true);
}
```

---

## HTTP Mocking

### Http::fake() for External APIs

```php
use Illuminate\Support\Facades\Http;

public function test_external_api_call(): void
{
    Http::fake([
        // Specific URL
        'api.stripe.com/v1/charges' => Http::response([
            'id' => 'ch_123',
            'status' => 'succeeded',
        ], 200),

        // Wildcard pattern
        'api.github.com/*' => Http::response([
            'user' => 'john',
        ], 200),

        // Status codes
        'api.example.com/error' => Http::response(null, 500),

        // Sequential responses
        'api.example.com/queue' => Http::sequence()
            ->push(['status' => 'pending'], 202)
            ->push(['status' => 'processing'], 202)
            ->push(['status' => 'completed'], 200),

        // Default for all other URLs
        '*' => Http::response(['error' => 'Not found'], 404),
    ]);

    $response = Http::post('api.stripe.com/v1/charges', [
        'amount' => 1000,
    ]);

    $this->assertEquals('succeeded', $response->json('status'));
}

public function test_http_assertions(): void
{
    Http::fake();

    Http::withToken('secret')
        ->post('api.example.com/users', ['name' => 'John']);

    // Assert a request was sent
    Http::assertSent(function ($request) {
        return $request->url() === 'api.example.com/users' &&
               $request->hasHeader('Authorization', 'Bearer secret') &&
               $request['name'] === 'John';
    });

    // Assert sent to specific URL
    Http::assertSentTo('api.example.com/*', 'POST');

    // Assert not sent
    Http::assertNotSent(function ($request) {
        return $request->url() === 'api.example.com/admin';
    });

    // Assert count
    Http::assertSentCount(3);
}
```

### Testing Rate Limiting

```php
public function test_rate_limiting(): void
{
    Http::fake([
        'api.example.com/*' => Http::response([
            'data' => 'test'
        ], 200, [
            'X-RateLimit-Remaining' => 99,
            'X-RateLimit-Limit' => 100,
        ]),
    ]);

    $response = Http::get('api.example.com/data');

    $this->assertEquals(99, $response->header('X-RateLimit-Remaining'));
}
```

---

## Queue & Event Mocking

### Queue Faking

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessOrder;
use App\Jobs\SendWelcomeEmail;

public function test_queue_dispatch(): void
{
    Queue::fake();

    // Dispatch job
    ProcessOrder::dispatch($order);

    // Assert pushed
    Queue::assertPushed(ProcessOrder::class);

    // Assert pushed with callback
    Queue::assertPushed(ProcessOrder::class, function ($job) use ($order) {
        return $job->order->id === $order->id;
    });

    // Assert pushed on specific queue
    Queue::assertPushedOn('orders', ProcessOrder::class);

    // Assert pushed with chain
    Queue::assertPushedWithChain(ProcessOrder::class, [
        SendWelcomeEmail::class,
    ]);

    // Assert not pushed
    Queue::assertNotPushed(AnotherJob::class);

    // Assert count
    Queue::assertPushed(ProcessOrder::class, 2);
}
```

### Event Faking

```php
use Illuminate\Support\Facades\Event;
use App\Events\OrderPlaced;
use App\Events\UserRegistered;
use App\Listeners\SendOrderConfirmation;

public function test_event_dispatch(): void
{
    Event::fake();

    // Trigger event
    OrderPlaced::dispatch($order);

    // Assert dispatched
    Event::assertDispatched(OrderPlaced::class);

    // Assert with callback
    Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });

    // Assert times dispatched
    Event::assertDispatched(OrderPlaced::class, 2);

    // Assert not dispatched
    Event::assertNotDispatched(UserRegistered::class);

    // Assert nothing dispatched
    Event::assertNothingDispatched();

    // Listen for specific listener
    Event::assertListening(OrderPlaced::class, SendOrderConfirmation::class);
}
```

### Faking Specific Events

```php
use Illuminate\Auth\Events\Registered;

public function test_fake_specific_events(): void
{
    // Only fake specific events, others dispatch normally
    Event::fake([OrderPlaced::class]);

    // This is faked
    OrderPlaced::dispatch($order);

    // This dispatches normally
    Registered::dispatch($user);

    Event::assertDispatched(OrderPlaced::class);
}
```

---

## Advanced Mocking

### Mocking Constructor

```php
public function test_mock_with_constructor(): void
{
    // Disable constructor
    $mock = $this->getMockBuilder(Service::class)
        ->disableOriginalConstructor()
        ->getMock();

    // Or with Mockery
    $mock = \Mockery::mock(Service::class . '[methodToMock]', [$constructorArg]);
}
```

### Mocking Static Methods

```php
// Use Facades for Laravel static methods
Cache::shouldReceive('get')->andReturn('value');

// For custom static methods, use alias mocking
public function test_static_method(): void
{
    $mock = \Mockery::mock('alias:App\Utilities\Helper');
    $mock->shouldReceive('staticMethod')
        ->andReturn('mocked result');

    // This only works for future calls, not already loaded classes
}

// Better: Refactor to use instance methods or dependency injection
```

### Mocking Self

```php
public function test_mock_current_class_methods(): void
{
    $mock = $this->getMockBuilder(Service::class)
        ->onlyMethods(['internalMethod'])
        ->getMock();

    $mock->expects($this->once())
        ->method('internalMethod')
        ->willReturn('mocked');

    // Call method that uses internalMethod
    $result = $mock->publicMethod();

    $this->assertEquals('expected', $result);
}
```

### Mocking Traits

```php
public function test_trait_methods(): void
{
    $mock = $this->getMockForTrait(LoggableTrait::class);

    $mock->expects($this->once())
        ->method('log');

    $mock->performAction();
}
```

### Mocking Abstract Classes

```php
public function test_abstract_class(): void
{
    $mock = $this->getMockForAbstractClass(BaseRepository::class);

    $mock->expects($this->any())
        ->method('abstractMethod')
        ->willReturn('value');

    $result = $mock->concreteMethod();
}
```

### Verifying Method Calls

```php
public function test_verify_calls(): void
{
    $mock = $this->mock(Repository::class);

    // Run code
    $service = new Service($mock);
    $service->process($data);

    // Verify call order
    $mock->shouldHaveReceived('start')
        ->once()
        ->ordered();

    $mock->shouldHaveReceived('validate')
        ->once()
        ->ordered();

    $mock->shouldHaveReceived('save')
        ->once()
        ->ordered();

    // Verify not called
    $mock->shouldNotHaveReceived('delete');
}
```
