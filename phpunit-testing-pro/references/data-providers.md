# Data Providers & Advanced Patterns

## Table of Contents
- [Data Provider Basics](#data-provider-basics)
- [Named Data Sets](#named-data-sets)
- [Multiple Data Providers](#multiple-data-providers)
- [Dynamic Data Providers](#dynamic-data-providers)
- [Data Provider with Dependencies](#data-provider-with-dependencies)
- [PHPUnit 10+ Attributes](#phpunit-10-attributes)

---

## Data Provider Basics

### Basic Structure

```php
<?php

use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /**
     * @dataProvider additionProvider
     */
    public function test_add(int $a, int $b, int $expected): void
    {
        $this->assertEquals($expected, $a + $b);
    }

    public static function additionProvider(): array
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 0, 1],
            [1, 1, 2],
        ];
    }
}
```

### With PHPUnit 10+ Attributes

```php
<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    #[DataProvider('additionProvider')]
    public function test_add(int $a, int $b, int $expected): void
    {
        $this->assertEquals($expected, $a + $b);
    }

    public static function additionProvider(): array
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 0, 1],
            [1, 1, 2],
        ];
    }
}
```

---

## Named Data Sets

Named data sets make test output more descriptive:

```php
<?php

use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase
{
    /**
     * @dataProvider emailProvider
     */
    public function test_validates_emails(string $email, bool $expected): void
    {
        $validator = new EmailValidator();

        $this->assertEquals($expected, $validator->isValid($email));
    }

    public static function emailProvider(): array
    {
        return [
            'valid simple email' => ['user@example.com', true],
            'valid with subdomain' => ['user@mail.example.com', true],
            'valid with plus tag' => ['user+tag@example.com', true],
            'valid with numbers' => ['user123@example.com', true],
            'invalid no at sign' => ['userexample.com', false],
            'invalid no domain' => ['user@', false],
            'invalid no local part' => ['@example.com', false],
            'invalid double dots' => ['user..name@example.com', false],
            'empty string' => ['', false],
        ];
    }
}
```

Output when test fails:
```
FAILED: EmailValidatorTest::test_validates_emails with data set "invalid no at sign"
```

---

## Multiple Data Providers

### Using Multiple Providers

```php
<?php

use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    /**
     * @dataProvider lowercaseProvider
     * @dataProvider uppercaseProvider
     * @dataProvider mixedCaseProvider
     */
    public function test_string_transformations(string $input, string $expected): void
    {
        $helper = new StringHelper();

        $this->assertEquals($expected, $helper->transform($input));
    }

    public static function lowercaseProvider(): array
    {
        return [
            'all lowercase' => ['hello', 'HELLO'],
            'simple word' => ['world', 'WORLD'],
        ];
    }

    public static function uppercaseProvider(): array
    {
        return [
            'all uppercase' => ['HELLO', 'hello'],
            'caps word' => ['WORLD', 'world'],
        ];
    }

    public static function mixedCaseProvider(): array
    {
        return [
            'mixed case' => ['HeLLo', 'hEllO'],
            'camelCase' => ['myVariable', 'MYvARIABLE'],
        ];
    }
}
```

### Different Tests, Different Providers

```php
<?php

use PHPUnit\Framework\TestCase;

class UserValidatorTest extends TestCase
{
    /**
     * @dataProvider validUserProvider
     */
    public function test_validates_correct_user_data(array $data, bool $expected): void
    {
        $validator = new UserValidator();

        $this->assertTrue($validator->validate($data));
    }

    /**
     * @dataProvider invalidUserProvider
     */
    public function test_rejects_invalid_user_data(array $data, array $expectedErrors): void
    {
        $validator = new UserValidator();
        $errors = $validator->validate($data);

        $this->assertEquals($expectedErrors, $errors);
    }

    public static function validUserProvider(): array
    {
        return [
            'complete user' => [[
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
            ], true],
            'minimal user' => [[
                'name' => 'Jane',
                'email' => 'jane@example.com',
            ], true],
        ];
    }

    public static function invalidUserProvider(): array
    {
        return [
            'missing name' => [[
                'email' => 'test@example.com',
            ], ['name']],
            'invalid email' => [[
                'name' => 'Test',
                'email' => 'not-an-email',
            ], ['email']],
            'underage' => [[
                'name' => 'Test',
                'email' => 'test@example.com',
                'age' => 15,
            ], ['age']],
        ];
    }
}
```

---

## Dynamic Data Providers

### From External Source

```php
<?php

use PHPUnit\Framework\TestCase;

class CsvDataProviderTest extends TestCase
{
    /**
     * @dataProvider csvDataProvider
     */
    public function test_with_csv_data(string $input, string $expected): void
    {
        $this->assertEquals($expected, strtoupper($input));
    }

    public static function csvDataProvider(): array
    {
        $data = [];
        $csvFile = fopen(__DIR__ . '/fixtures/test_data.csv', 'r');

        while ($row = fgetcsv($csvFile)) {
            $data[$row[0]] = [$row[0], $row[1]];
        }

        fclose($csvFile);

        return $data;
    }
}
```

### From Database (For Integration Tests)

```php
<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleEngineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider ruleProvider
     */
    public function test_rules_apply_correctly(array $conditions, array $expected): void
    {
        $rule = Rule::create(['conditions' => $conditions]);

        $result = $rule->evaluate();

        $this->assertEquals($expected, $result);
    }

    public static function ruleProvider(): array
    {
        // Note: Can't use database here as it runs before tests
        // Use factories or seeded data instead
        return [
            'simple condition' => [
                ['field' => 'age', 'operator' => '>', 'value' => 18],
                ['result' => 'adult'],
            ],
            'compound condition' => [
                ['AND' => [
                    ['field' => 'age', 'operator' => '>', 'value' => 18],
                    ['field' => 'status', 'operator' => '=', 'value' => 'active'],
                ]],
                ['result' => 'eligible'],
            ],
        ];
    }
}
```

### Generator-Based Provider

```php
<?php

use PHPUnit\Framework\TestCase;

class LargeDatasetTest extends TestCase
{
    /**
     * @dataProvider generatorProvider
     */
    public function test_with_large_dataset(int $input, int $expected): void
    {
        $this->assertEquals($expected, $input * 2);
    }

    public static function generatorProvider(): \Generator
    {
        for ($i = 0; $i < 1000; $i++) {
            yield "iteration {$i}" => [$i, $i * 2];
        }
    }
}
```

---

## Data Provider with Dependencies

```php
<?php

use PHPUnit\Framework\TestCase;

class OrderProcessingTest extends TestCase
{
    private Order $testOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testOrder = new Order(['id' => 1, 'total' => 100]);
    }

    /**
     * @dataProvider discountProvider
     */
    public function test_applies_correct_discount(string $customerType, float $expectedDiscount): void
    {
        $processor = new OrderProcessor();
        $customer = new Customer(['type' => $customerType]);

        $discount = $processor->calculateDiscount($this->testOrder, $customer);

        $this->assertEquals($expectedDiscount, $discount);
    }

    public static function discountProvider(): array
    {
        return [
            'regular customer' => ['regular', 0.0],
            'bronze member' => ['bronze', 5.0],
            'silver member' => ['silver', 10.0],
            'gold member' => ['gold', 15.0],
            'platinum member' => ['platinum', 20.0],
        ];
    }
}
```

---

## PHPUnit 10+ Attributes

### TestWith Attribute

```php
<?php

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ModernTest extends TestCase
{
    #[TestWith([0, 0, 0])]
    #[TestWith([0, 1, 1])]
    #[TestWith([1, 0, 1])]
    #[TestWith([1, 1, 2])]
    public function test_add(int $a, int $b, int $expected): void
    {
        $this->assertEquals($expected, $a + $b);
    }

    // With named data
    #[TestWith('zeros', [0, 0, 0])]
    #[TestWith('positive numbers', [2, 3, 5])]
    public function test_add_named(int $a, int $b, int $expected): void
    {
        $this->assertEquals($expected, $a + $b);
    }
}
```

### DataProviderExternal Attribute

```php
<?php

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class ExternalDataProviderTest extends TestCase
{
    #[DataProviderExternal(CommonDataProvider::class, 'provideNumbers')]
    public function test_with_external_provider(int $a, int $b, int $sum): void
    {
        $this->assertEquals($sum, $a + $b);
    }
}

// Separate file: tests/DataProviders/CommonDataProvider.php
class CommonDataProvider
{
    public static function provideNumbers(): array
    {
        return [
            [1, 2, 3],
            [5, 5, 10],
            [100, 200, 300],
        ];
    }
}
```

### Complete PHPUnit 10+ Example

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new Calculator();
    }

    #[Test]
    #[TestDox('Adding $a and $b equals $expected')]
    #[DataProvider('additionProvider')]
    public function add_returns_correct_sum(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $this->calculator->add($a, $b));
    }

    #[DataProvider('additionProvider')]
    public function testAdd(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $this->calculator->add($a, $b));
    }

    public static function additionProvider(): array
    {
        return [
            'zero plus zero' => [0, 0, 0],
            'zero plus one' => [0, 1, 1],
            'one plus one' => [1, 1, 2],
            'positive numbers' => [5, 3, 8],
            'negative numbers' => [-5, -3, -8],
            'mixed numbers' => [5, -3, 2],
        ];
    }

    #[Test]
    #[Ticket('PROJECT-123')]
    public function division_by_zero_throws_exception(): void
    {
        $this->expectException(DivisionByZeroException::class);

        $this->calculator->divide(10, 0);
    }

    #[Test]
    #[TestDox('Division of $dividend by $divisor equals $expected')]
    public function divide_returns_correct_quotient(): void
    {
        $result = $this->calculator->divide(10, 2);

        $this->assertEquals(5.0, $result);
    }
}
```

---

## Edge Case Testing Pattern

```php
<?php

use PHPUnit\Framework\TestCase;

class BoundaryTestingTest extends TestCase
{
    /**
     * @dataProvider boundaryProvider
     */
    public function test_age_boundaries(int $age, string $category): void
    {
        $classifier = new AgeClassifier();

        $this->assertEquals($category, $classifier->classify($age));
    }

    public static function boundaryProvider(): array
    {
        return [
            // Boundary values
            'exactly 0' => [0, 'infant'],
            'exactly 1' => [1, 'toddler'],
            'exactly 12' => [12, 'child'],
            'exactly 13' => [13, 'teenager'],
            'exactly 19' => [19, 'teenager'],
            'exactly 20' => [20, 'adult'],
            'exactly 64' => [64, 'adult'],
            'exactly 65' => [65, 'senior'],

            // Just outside boundaries
            'one below infant limit' => [-1, 'invalid'],
            'one above senior' => [130, 'senior'],

            // Common ages
            'typical working age' => [35, 'adult'],
            'retirement age' => [67, 'senior'],
        ];
    }
}
```
