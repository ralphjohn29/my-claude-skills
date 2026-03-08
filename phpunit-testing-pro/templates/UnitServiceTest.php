<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\OrderRepositoryInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Contracts\NotificationServiceInterface;
use App\Events\OrderProcessed;
use App\Exceptions\PaymentFailedException;
use App\Models\Order;
use App\Services\OrderProcessor;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Unit test template for service classes.
 *
 * This template demonstrates:
 * - Pure unit tests without Laravel framework
 * - Dependency injection with mocks
 * - Data providers for multiple scenarios
 * - PHPUnit 10+ attributes
 * - Exception testing
 * - Event mocking
 *
 * Run with: php artisan test --filter=OrderProcessorTest
 */
#[CoversClass(OrderProcessor::class)]
class OrderProcessorTest extends TestCase
{
    private OrderRepositoryInterface $orderRepository;
    private PaymentGatewayInterface $paymentGateway;
    private NotificationServiceInterface $notificationService;
    private OrderProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for all dependencies
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentGateway = $this->createMock(PaymentGatewayInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);

        // Inject mocks into the service
        $this->processor = new OrderProcessor(
            $this->orderRepository,
            $this->paymentGateway,
            $this->notificationService
        );
    }

    // ==================== HAPPY PATH TESTS ====================

    #[Test]
    #[TestDox('It processes order successfully with valid payment')]
    public function processes_order_successfully(): void
    {
        // Arrange
        $order = $this->createOrder(100.00);

        // Expect payment to be processed once
        $this->paymentGateway
            ->expects($this->once())
            ->method('charge')
            ->with(
                $this->equalTo(100.00),
                $this->arrayHasKey('currency')
            )
            ->willReturn([
                'status' => 'succeeded',
                'transaction_id' => 'txn_123456',
            ]);

        // Expect order to be saved with transaction ID
        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $order) {
                return $order->transaction_id === 'txn_123456'
                    && $order->status === 'completed';
            }));

        // Expect notification to be sent
        $this->notificationService
            ->expects($this->once())
            ->method('sendOrderConfirmation')
            ->with($order);

        // Act
        $result = $this->processor->process($order);

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('txn_123456', $result->transactionId());
    }

    // ==================== DATA PROVIDER TESTS ====================

    #[Test]
    #[DataProvider('orderAmountProvider')]
    #[TestDox('It charges the correct amount for order with total $amount')]
    public function charges_correct_amount(float $amount, string $expectedStatus): void
    {
        // Arrange
        $order = $this->createOrder($amount);

        $this->paymentGateway
            ->method('charge')
            ->with($amount)
            ->willReturn(['status' => $expectedStatus, 'transaction_id' => 'txn_test']);

        $this->orderRepository->method('save');
        $this->notificationService->method('sendOrderConfirmation');

        // Act
        $result = $this->processor->process($order);

        // Assert
        $this->assertEquals($expectedStatus === 'succeeded', $result->isSuccess());
    }

    public static function orderAmountProvider(): array
    {
        return [
            'minimum amount' => [0.01, 'succeeded'],
            'small order' => [25.00, 'succeeded'],
            'medium order' => [150.50, 'succeeded'],
            'large order' => [1000.00, 'succeeded'],
            'very large order' => [10000.00, 'succeeded'],
        ];
    }

    // ==================== EXCEPTION TESTS ====================

    #[Test]
    #[TestDox('It throws exception when payment is declined')]
    public function fails_when_payment_declined(): void
    {
        // Arrange
        $order = $this->createOrder(100.00);

        $this->paymentGateway
            ->method('charge')
            ->willThrowException(new PaymentFailedException('Card declined'));

        // Repository and notification should NOT be called
        $this->orderRepository->expects($this->never())->method('save');
        $this->notificationService->expects($this->never())->method('sendOrderConfirmation');

        // Act
        $result = $this->processor->process($order);

        // Assert
        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('declined', $result->errorMessage());
    }

    #[Test]
    #[TestDox('It throws exception for invalid order amount')]
    public function throws_for_invalid_amount(): void
    {
        // Arrange
        $order = $this->createOrder(-50.00);

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order amount must be positive');

        $this->processor->process($order);
    }

    // ==================== EDGE CASE TESTS ====================

    #[Test]
    #[TestDox('It handles partial payment correctly')]
    public function handles_partial_payment(): void
    {
        // Arrange
        $order = $this->createOrder(100.00);
        $order->partial_payment = 30.00;

        $this->paymentGateway
            ->expects($this->once())
            ->method('charge')
            ->with(70.00) // Should only charge remaining amount
            ->willReturn(['status' => 'succeeded', 'transaction_id' => 'txn_partial']);

        $this->orderRepository->method('save');
        $this->notificationService->method('sendOrderConfirmation');

        // Act
        $result = $this->processor->process($order);

        // Assert
        $this->assertTrue($result->isSuccess());
    }

    #[Test]
    #[TestDox('It handles notification failure gracefully')]
    public function handles_notification_failure_gracefully(): void
    {
        // Arrange
        $order = $this->createOrder(100.00);

        $this->paymentGateway
            ->method('charge')
            ->willReturn(['status' => 'succeeded', 'transaction_id' => 'txn_test']);

        $this->orderRepository->method('save');

        // Notification fails but should not affect order processing
        $this->notificationService
            ->method('sendOrderConfirmation')
            ->willThrowException(new \RuntimeException('SMTP error'));

        // Act
        $result = $this->processor->process($order);

        // Assert - Order should still be successful
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($result->hasNotificationError());
    }

    // ==================== HELPER METHODS ====================

    private function createOrder(float $amount): Order
    {
        return new Order([
            'id' => 1,
            'total' => $amount,
            'status' => 'pending',
            'currency' => 'USD',
        ]);
    }
}
