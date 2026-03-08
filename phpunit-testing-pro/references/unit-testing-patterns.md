# Unit Testing Patterns

## Table of Contents
- [Test Organization](#test-organization)
- [Test Naming Conventions](#test-naming-conventions)
- [Test Isolation Strategies](#test-isolation-strategies)
- [Common Patterns](#common-patterns)
- [Value Object Testing](#value-object-testing)
- [Service Layer Testing](#service-layer-testing)
- [Test Doubles Reference](#test-doubles-reference)

---

## Test Organization

### Directory Structure Best Practices

```
tests/
├── Unit/
│   ├── Domain/                    # Domain models and value objects
│   │   ├── ValueObjects/
│   │   │   ├── EmailTest.php
│   │   │   └── MoneyTest.php
│   │   └── Entities/
│   │       └── UserTest.php
│   ├── Application/               # Application services
│   │   └── Services/
│   │       ├── PaymentServiceTest.php
│   │       └── NotificationServiceTest.php
│   └── Infrastructure/            # Infrastructure concerns
│       └── Repositories/
│           └── UserRepositoryTest.php
└── Feature/
    └── Api/
        └── v1/
            └── UserControllerTest.php
```

### Base Test Class Pattern

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

abstract class BaseUnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Common setup for all unit tests
    }

    protected function tearDown(): void
    {
        // Common cleanup
        parent::tearDown();
    }

    /**
     * Helper to create a mock that returns itself for fluent interfaces
     */
    protected function createFluentMock(string $className): object
    {
        $mock = $this->createMock($className);
        $mock->method('andReturn')->willReturnSelf();
        return $mock;
    }
}
```

---

## Test Naming Conventions

### The Given/When/Then Pattern

```php
// Good naming follows: test_[method]_[scenario]_[expected_result]

public function test_calculateDiscount_withPremiumMember_returnsTwentyPercent(): void
{
    // Given
    $member = Member::premium();
    $order = Order::withAmount(100);

    // When
    $discount = (new DiscountCalculator())->calculate($order, $member);

    // Then
    $this->assertEquals(20.0, $discount);
}

// Alternative: Method_result_when_condition
public function test_calculateDiscount_returnsZero_whenMemberIsNull(): void
{
    $calculator = new DiscountCalculator();
    $order = Order::withAmount(100);

    $discount = $calculator->calculate($order, null);

    $this->assertEquals(0.0, $discount);
}

// Using @test annotation (allows spaces in method name)
/** @test */
public function it_calculates_discount_for_premium_members(): void
{
    // Test implementation
}
```

### Naming Anti-Patterns to Avoid

```php
// BAD - Too vague
public function test_discount(): void {}

// BAD - Doesn't describe expected behavior
public function test_discount_1(): void {}

// BAD - Implementation details
public function test_if_variable_is_set_to_20(): void {}

// GOOD - Describes behavior and expected outcome
public function test_calculateDiscount_appliesTwentyPercent_whenUserIsPremium(): void {}
```

---

## Test Isolation Strategies

### No External Dependencies

```php
<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\TaxCalculator;
use App\Contracts\TaxRateProviderInterface;

class TaxCalculatorTest extends TestCase
{
    public function test_calculates_tax_using_injected_rate_provider(): void
    {
        // Create a mock instead of hitting an API
        $rateProvider = $this->createMock(TaxRateProviderInterface::class);
        $rateProvider->method('getRate')
            ->with('US', 'CA')
            ->willReturn(0.0825);

        $calculator = new TaxCalculator($rateProvider);

        $tax = $calculator->calculate(100.00, 'US', 'CA');

        $this->assertEquals(8.25, $tax);
    }
}
```

### Testing Pure Functions

```php
<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    /**
     * @dataProvider slugProvider
     */
    public function test_slugify_converts_to_url_safe_string(string $input, string $expected): void
    {
        $this->assertEquals($expected, StringHelper::slugify($input));
    }

    public static function slugProvider(): array
    {
        return [
            'simple string' => ['Hello World', 'hello-world'],
            'special chars' => ['Hello! @World#', 'hello-world'],
            'multiple spaces' => ['Hello    World', 'hello-world'],
            'unicode' => ['Café Müller', 'cafe-muller'],
            'numbers included' => ['Test 123', 'test-123'],
            'already slug' => ['already-slug', 'already-slug'],
        ];
    }
}
```

---

## Common Patterns

### Builder Pattern for Complex Test Data

```php
<?php

namespace Tests\Unit\Builders;

use App\Models\Order;
use App\Models\User;
use App\ValueObjects\Money;

class OrderBuilder
{
    private Order $order;

    public function __construct()
    {
        $this->order = new Order([
            'id' => 1,
            'user_id' => 1,
            'total' => Money::fromDecimal(100.00),
            'status' => 'pending',
        ]);
    }

    public static function anOrder(): self
    {
        return new self();
    }

    public function withTotal(float $amount): self
    {
        $this->order->total = Money::fromDecimal($amount);
        return $this;
    }

    public function forUser(User $user): self
    {
        $this->order->user_id = $user->id;
        return $this;
    }

    public function completed(): self
    {
        $this->order->status = 'completed';
        return $this;
    }

    public function build(): Order
    {
        return $this->order;
    }
}

// Usage in tests
public function test_process_completed_order(): void
{
    $order = OrderBuilder::anOrder()
        ->withTotal(500.00)
        ->forUser($user)
        ->completed()
        ->build();

    // Test with the built order
}
```

### Test Data Mother Pattern

```php
<?php

namespace Tests\Unit\Mother;

use App\Models\User;
use App\ValueObjects\Email;

class UserMother
{
    public static function aUser(): User
    {
        return new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => Email::fromString('john@example.com'),
            'active' => true,
        ]);
    }

    public static function anAdmin(): User
    {
        $user = self::aUser();
        $user->role = 'admin';
        return $user;
    }

    public static function anInactiveUser(): User
    {
        $user = self::aUser();
        $user->active = false;
        return $user;
    }

    public static function withEmail(string $email): User
    {
        $user = self::aUser();
        $user->email = Email::fromString($email);
        return $user;
    }
}
```

---

## Value Object Testing

```php
<?php

namespace Tests\Unit\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\ValueObjects\Email;
use InvalidArgumentException;

class EmailTest extends TestCase
{
    public function test_creates_email_from_valid_string(): void
    {
        $email = Email::fromString('test@example.com');

        $this->assertEquals('test@example.com', $email->toString());
    }

    public function test_throws_exception_for_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        Email::fromString('invalid-email');
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_rejects_various_invalid_formats(string $invalidEmail): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString($invalidEmail);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty' => [''],
            'no @' => ['testexample.com'],
            'no domain' => ['test@'],
            'no local' => ['@example.com'],
            'spaces' => ['test @example.com'],
        ];
    }

    public function test_two_emails_with_same_value_are_equal(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');

        $this->assertTrue($email1->equals($email2));
    }

    public function test_can_extract_domain(): void
    {
        $email = Email::fromString('test@example.com');

        $this->assertEquals('example.com', $email->domain());
    }
}
```

---

## Service Layer Testing

```php
<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\OrderProcessor;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Contracts\NotificationServiceInterface;
use App\Events\OrderProcessed;
use App\Exceptions\PaymentFailedException;

class OrderProcessorTest extends TestCase
{
    private OrderRepositoryInterface $orderRepository;
    private PaymentGatewayInterface $paymentGateway;
    private NotificationServiceInterface $notificationService;
    private OrderProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentGateway = $this->createMock(PaymentGatewayInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);

        $this->processor = new OrderProcessor(
            $this->orderRepository,
            $this->paymentGateway,
            $this->notificationService
        );
    }

    public function test_processes_order_successfully(): void
    {
        $order = OrderBuilder::anOrder()->withTotal(100.00)->build();

        // Expect payment to be processed
        $this->paymentGateway->expects($this->once())
            ->method('charge')
            ->with($this->equalTo(100.00), $this->anything())
            ->willReturn(['status' => 'success', 'transaction_id' => 'txn_123']);

        // Expect order to be saved with transaction ID
        $this->orderRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $savedOrder) {
                return $savedOrder->transaction_id === 'txn_123'
                    && $savedOrder->status === 'completed';
            }));

        // Expect notification to be sent
        $this->notificationService->expects($this->once())
            ->method('sendOrderConfirmation')
            ->with($order);

        $result = $this->processor->process($order);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('txn_123', $result->transactionId());
    }

    public function test_fails_when_payment_is_declined(): void
    {
        $order = OrderBuilder::anOrder()->build();

        $this->paymentGateway->method('charge')
            ->willThrowException(new PaymentFailedException('Card declined'));

        $this->orderRepository->expects($this->never())
            ->method('save');

        $this->notificationService->expects($this->never())
            ->method('sendOrderConfirmation');

        $result = $this->processor->process($order);

        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('declined', $result->errorMessage());
    }
}
```

---

## Test Doubles Reference

### Stub - Returns Canned Responses

```php
// A stub provides fixed responses, doesn't verify interactions
$stub = $this->createMock(UserRepository::class);
$stub->method('find')
    ->willReturn(new User(['id' => 1, 'name' => 'John']));

// Can return different values based on arguments
$stub->method('findByRole')
    ->willReturnMap([
        ['admin', [$adminUser1, $adminUser2]],
        ['user', [$regularUser1, $regularUser2]],
    ]);
```

### Mock - Verifies Interactions

```php
// A mock verifies that specific methods were called
$mock = $this->createMock(EmailSender::class);
$mock->expects($this->once())
    ->method('send')
    ->with(
        $this->equalTo('john@example.com'),
        $this->stringContains('Welcome')
    );
```

### Spy - Records Interactions

```php
// Using PHPUnit's mock as a spy
$spy = $this->createMock(LoggerInterface::class);
$spy->expects($this->exactly(3))
    ->method('info');

// After running code, verify the calls
// The mock automatically tracks call count
```

### Fake - Working Implementation

```php
<?php

namespace Tests\Unit\Fakes;

use App\Contracts\PaymentGatewayInterface;

class FakePaymentGateway implements PaymentGatewayInterface
{
    private array $charges = [];
    private bool $shouldFail = false;
    private ?string $failureMessage = null;

    public function charge(float $amount, array $options): array
    {
        if ($this->shouldFail) {
            throw new PaymentFailedException($this->failureMessage ?? 'Payment failed');
        }

        $charge = [
            'id' => 'ch_' . uniqid(),
            'amount' => $amount,
            'status' => 'succeeded',
        ];

        $this->charges[] = $charge;

        return $charge;
    }

    public function setShouldFail(bool $fail, string $message = null): void
    {
        $this->shouldFail = $fail;
        $this->failureMessage = $message;
    }

    public function getCharges(): array
    {
        return $this->charges;
    }

    public function getTotalCharged(): float
    {
        return array_sum(array_column($this->charges, 'amount'));
    }

    public function reset(): void
    {
        $this->charges = [];
        $this->shouldFail = false;
        $this->failureMessage = null;
    }
}
```

### Using Fakes in Tests

```php
public function test_processes_multiple_payments(): void
{
    $gateway = new FakePaymentGateway();

    $processor = new OrderProcessor($gateway);

    $processor->process(OrderBuilder::anOrder()->withTotal(100)->build());
    $processor->process(OrderBuilder::anOrder()->withTotal(50)->build());

    $this->assertEquals(150.0, $gateway->getTotalCharged());
    $this->assertCount(2, $gateway->getCharges());
}

public function test_handles_payment_failure(): void
{
    $gateway = new FakePaymentGateway();
    $gateway->setShouldFail(true, 'Insufficient funds');

    $processor = new OrderProcessor($gateway);

    $result = $processor->process(OrderBuilder::anOrder()->build());

    $this->assertFalse($result->isSuccess());
    $this->assertStringContainsString('Insufficient funds', $result->errorMessage());
}
```
