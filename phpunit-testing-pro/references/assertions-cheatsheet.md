# PHPUnit Assertions Cheatsheet

## Table of Contents
- [Basic Assertions](#basic-assertions)
- [Array Assertions](#array-assertions)
- [String Assertions](#string-assertions)
- [Numeric Assertions](#numeric-assertions)
- [Object/Class Assertions](#objectclass-assertions)
- [Type Assertions](#type-assertions)
- [File/FileSystem Assertions](#filesystem-assertions)
- [JSON Assertions](#json-assertions)
- [Exception Assertions](#exception-assertions)
- [Laravel-Specific Assertions](#laravel-specific-assertions)
- [Database Assertions](#database-assertions)
- [HTTP Response Assertions](#http-response-assertions)

---

## Basic Assertions

```php
// Equality
$this->assertEquals($expected, $actual, $message = '');
$this->assertSame($expected, $actual);           // Strict comparison (===)
$this->assertNotEquals($expected, $actual);
$this->assertNotSame($expected, $actual);

// Boolean
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertNotTrue($condition);
$this->assertNotFalse($condition);

// Null
$this->assertNull($actual);
$this->assertNotNull($actual);

// Empty
$this->assertEmpty($actual);
$this->assertNotEmpty($actual);

// Identity
$this->assertInstanceOf($expected, $actual);
$this->assertNotInstanceOf($expected, $actual);

// Conditional
$this->assertThat($actual, $constraint);
```

---

## Array Assertions

```php
// Basic array assertions
$this->assertIsArray($actual);
$this->assertCount($expectedCount, $array);
$this->assertNotCount($expectedCount, $array);

// Array contents
$this->assertArrayHasKey($key, $array);
$this->assertArrayNotHasKey($key, $array);
$this->assertContains($needle, $haystack);
$this->assertNotContains($needle, $haystack);
$this->assertContainsEquals($needle, $haystack);  // Uses == comparison
$this->assertContainsOnly($type, $haystack);      // All elements of type
$this->assertContainsOnlyInstancesOf($className, $haystack);

// Equivalence
$this->assertEqualsCanonicalizing($expected, $actual);  // Sorts arrays
$this->assertEqualsWithDelta($expected, $actual, $delta);

// Examples
$this->assertArrayHasKey('email', $user);
$this->assertContains('admin', $roles);
$this->assertCount(3, $items);
```

---

## String Assertions

```php
// Basic string assertions
$this->assertIsString($actual);

// Content matching
$this->assertStringContainsString($needle, $haystack);
$this->assertStringContainsStringIgnoringCase($needle, $haystack);
$this->assertStringNotContainsString($needle, $haystack);
$this->assertStringNotContainsStringIgnoringCase($needle, $haystack);

// String starts/ends
$this->assertStringStartsWith($prefix, $string);
$this->assertStringStartsNotWith($prefix, $string);
$this->assertStringEndsWith($suffix, $string);
$this->assertStringEndsNotWith($suffix, $string);

// Regular expressions
$this->assertMatchesRegularExpression($pattern, $string);
$this->assertDoesNotMatchRegularExpression($pattern, $string);

// String length
$this->assertSameSize($expected, $actual);
$this->assertEmpty($string);
$this->assertNotEmpty($string);

// String format
$this->assertStringEqualsFile($expectedFile, $actualString);
$this->assertStringNotEqualsFile($expectedFile, $actualString);

// JSON string
$this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
$this->assertJsonStringNotEqualsJsonString($expectedJson, $actualJson);

// Examples
$this->assertStringContainsString('@', $email);
$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
$this->assertStringStartsWith('https://', $url);
```

---

## Numeric Assertions

```php
// Type checking
$this->assertIsInt($actual);
$this->assertIsFloat($actual);
$this->assertIsNumeric($actual);
$this->assertIsScalar($actual);

// Comparison
$this->assertEquals($expected, $actual);
$this->assertGreaterThan($expected, $actual);
$this->assertGreaterThanOrEqual($expected, $actual);
$this->assertLessThan($expected, $actual);
$this->assertLessThanOrEqual($expected, $actual);

// Floating point with delta
$this->assertEqualsWithDelta($expected, $actual, $delta);

// Finite/Infinite/NAN
$this->assertFinite($actual);
$this->assertInfinite($actual);
$this->assertNan($actual);

// Range checking
$this->assertThat($value, $this->logicalAnd(
    $this->greaterThan(0),
    $this->lessThan(100)
));

// Examples
$this->assertGreaterThan(0, $count);
$this->assertEqualsWithDelta(3.14, $pi, 0.01);
$this->assertLessThanOrEqual(100, $percentage);
```

---

## Object/Class Assertions

```php
// Instance and type
$this->assertInstanceOf($expected, $actual);
$this->assertNotInstanceOf($expected, $actual);

// Class existence
$this->assertClassExists($className);
$this->assertClassNotExists($className);
$this->assertInterfaceExists($interfaceName);
$this->assertInterfaceNotExists($interfaceName);
$this->assertTraitExists($traitName);
$this->assertTraitNotExists($traitName);

// Object attribute
$this->assertObjectHasAttribute($attributeName, $object);
$this->assertObjectNotHasAttribute($attributeName, $object);

// Class attribute
$this->assertClassHasAttribute($attributeName, $className);
$this->assertClassNotHasAttribute($attributeName, $className);
$this->assertClassHasStaticAttribute($attributeName, $className);
$this->assertClassNotHasStaticAttribute($attributeName, $className);

// Method existence
$this->assertTrue(method_exists($object, 'methodName'));
$this->assertTrue(method_exists($className, 'methodName'));

// Examples
$this->assertInstanceOf(User::class, $result);
$this->assertObjectHasAttribute('name', $user);
```

---

## Type Assertions

```php
// Scalar types
$this->assertIsBool($actual);
$this->assertIsInt($actual);
$this->assertIsFloat($actual);
$this->assertIsString($actual);
$this->assertIsNumeric($actual);
$this->assertIsScalar($actual);

// Compound types
$this->assertIsArray($actual);
$this->assertIsObject($actual);
$this->assertIsCallable($actual);
$this->assertIsIterable($actual);
$this->assertIsResource($actual);

// Special types
$this->assertNull($actual);
$this->assertNotNull($actual);

// Negative type assertions
$this->assertIsNotBool($actual);
$this->assertIsNotInt($actual);
$this->assertIsNotFloat($actual);
$this->assertIsNotString($actual);
$this->assertIsNotArray($actual);
$this->assertIsNotObject($actual);
$this->assertIsNotNumeric($actual);
$this->assertIsNotScalar($actual);
$this->assertIsNotCallable($actual);
$this->assertIsNotIterable($actual);
$this->assertIsNotResource($actual);

// Examples
$this->assertIsArray($response->json('data'));
$this->assertIsString($user->email);
$this->assertIsBool($result->success);
```

---

## FileSystem Assertions

```php
// File existence
$this->assertFileExists($path);
$this->assertFileDoesNotExist($path);

// Directory existence
$this->assertDirectoryExists($path);
$this->assertDirectoryDoesNotExist($path);
$this->assertDirectoryIsReadable($path);
$this->assertDirectoryIsWritable($path);

// File readability/writability
$this->assertFileIsReadable($path);
$this->assertFileIsNotReadable($path);
$this->assertFileIsWritable($path);
$this->assertFileIsNotWritable($path);

// File contents
$this->assertFileEquals($expected, $actual);
$this->assertFileNotEquals($expected, $actual);
$this->assertStringEqualsFile($expectedFile, $actualString);
$this->assertStringNotEqualsFile($expectedFile, $actualString);

// Examples
$this->assertFileExists(storage_path('app/exports/report.csv'));
$this->assertFileIsWritable(storage_path('logs'));
```

---

## JSON Assertions

```php
// PHPUnit native JSON assertions
$this->assertJson($actualJson);
$this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
$this->assertJsonStringNotEqualsJsonString($expectedJson, $actualJson);
$this->assertJsonStringEqualsJsonFile($expectedFile, $actualJson);
$this->assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson);

// Laravel JSON assertions (on TestResponse)
$response->assertJson($data);
$response->assertJsonPath($path, $expected);
$response->assertJsonStructure($structure);
$response->assertJsonCount($count, $key = null);
$response->assertJsonFragment($data);
$response->assertJsonMissing($data);
$response->assertJsonValidationErrors($keys);
$response->assertJsonMissingValidationErrors($keys);
$response->assertExactJson($data);

// Examples
// PHPUnit
$this->assertJson($response->content());

// Laravel
$response->assertJson([
    'message' => 'Success',
    'status' => 'created'
]);

$response->assertJsonPath('data.user.name', 'John Doe');

$response->assertJsonStructure([
    'data' => [
        'id',
        'name',
        'email',
        'created_at'
    ]
]);

$response->assertJsonCount(5, 'data.items');

$response->assertJsonFragment(['status' => 'active']);

$response->assertJsonValidationErrors(['email', 'password']);
```

---

## Exception Assertions

```php
// Basic exception assertion
$this->expectException($exceptionClass);
$this->expectExceptionMessage($message);
$this->expectExceptionMessageMatches($regularExpression);
$this->expectExceptionCode($code);

// Assert throws (PHP 8+ / PHPUnit 9.5+)
$this->assertThrows($exceptionClass, $callable);

// Error assertions
$this->expectError();
$this->expectWarning();
$this->expectNotice();
$this->expectDeprecation();

// Examples
public function test_throws_exception_for_invalid_input(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid email format');
    $this->expectExceptionCode(100);

    Email::fromString('invalid');
}

// Using assertThrows
public function test_assert_throws(): void
{
    $this->assertThrows(
        fn() => $service->processInvalidData(),
        ProcessingException::class
    );
}

// Laravel exception assertions
Exceptions::fake();
Exceptions::assertReported(CustomException::class);
Exceptions::assertNotReported(AnotherException::class);
```

---

## Laravel-Specific Assertions

```php
// Authentication
$this->assertAuthenticated($guard = null);
$this->assertGuest($guard = null);
$this->assertAuthenticatedAs($user, $guard = null);

// Authorization
$this->assertAuthorized($ability, $arguments = []);
$this->assertNotAuthorized($ability, $arguments = []);

// Session
$this->assertSessionHas($key, $value = null);
$this->assertSessionHasAll(array $bindings);
$this->assertSessionHasErrors($keys = [], $format = null, $bag = 'default');
$this->assertSessionDoesntHaveErrors($keys = [], $format = null, $bag = 'default');
$this->assertSessionHasNoErrors();
$this->assertSessionHasErrorsIn($bag, $keys = [], $format = null);
$this->assertSessionMissing($key);

// Cookies
$this->assertCookie($name, $value = null);
$this->assertCookieExpired($name);
$this->assertCookieNotExpired($name);
$this->assertCookieMissing($name);
$this->assertPlainCookie($name, $value = null);

// Views
$this->assertViewIs($view);
$this->assertViewHas($key, $value = null);
$this->assertViewHasAll(array $bindings);
$this->assertViewMissing($key);

// Redirects
$this->assertRedirect($uri);
$this->assertRedirectToRoute($name, $parameters = []);
$this->assertRedirectToAction($action, $parameters = []);

// Status assertions
$response->assertStatus($status);
$response->assertOk();                    // 200
$response->assertCreated();               // 201
$response->assertAccepted();              // 202
$response->assertNoContent($status = 204);
$response->assertMovedPermanently();      // 301
$response->assertFound();                 // 302
$response->assertBadRequest();            // 400
$response->assertUnauthorized();          // 401
$response->assertPaymentRequired();       // 402
$response->assertForbidden();             // 403
$response->assertNotFound();              // 404
$response->assertMethodNotAllowed();      // 405
$response->assertNotAcceptable();         // 406
$response->assertConflict();              // 409
$response->assertGone();                  // 410
$response->assertUnsupportedMediaType();  // 415
$response->assertUnprocessable();         // 422
$response->assertTooManyRequests();       // 429
$response->assertServerError();           // 500
$response->assertServiceUnavailable();    // 503

// Examples
$response->assertRedirect(route('home'));
$response->assertSessionHas('success', 'Post created!');
$response->assertViewIs('posts.index');
$response->assertViewHas('posts');
$this->assertAuthenticatedAs($user);
```

---

## Database Assertions

```php
// Laravel database assertions
$this->assertDatabaseHas($table, array $data, $connection = null);
$this->assertDatabaseMissing($table, array $data, $connection = null);
$this->assertDatabaseCount($table, int $count, $connection = null);
$this->assertDatabaseEmpty($table, $connection = null);

// Model assertions
$this->assertModelExists($model);
$this->assertModelMissing($model);

// Soft delete assertions
$this->assertSoftDeleted($table, array $data = [], $connection = null);

// Deleted at column
$this->assertDatabaseHas($table, [
    'id' => $id,
    'deleted_at' => null,
]);

// Examples
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
    'active' => true,
]);

$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

$this->assertDatabaseCount('posts', 10);

$this->assertSoftDeleted($post);
// or
$this->assertSoftDeleted('posts', ['id' => $post->id]);
```

---

## HTTP Response Assertions

```php
// Status codes
$response->assertStatus($code);
$response->assertSuccessful();         // 200-299
$response->assertOk();                  // 200
$response->assertCreated();             // 201
$response->assertNoContent();           // 204
$response->assertUnauthorized();        // 401
$response->assertForbidden();           // 403
$response->assertNotFound();            // 404
$response->assertUnprocessable();       // 422

// Content assertions
$response->assertContent($content);
$response->assertSee($value);
$response->assertSeeInOrder($values);
$response->assertSeeText($value);
$response->assertSeeTextInOrder($values);
$response->assertDontSee($value);
$response->assertDontSeeText($value);

// Header assertions
$response->assertHeader($name, $value = null);
$response->assertHeaderMissing($name);

// JSON response assertions
$response->assertJson($data);
$response->assertExactJson($data);
$response->assertJsonPath($path, $expected);
$response->assertJsonStructure($structure);
$response->assertJsonCount($count, $key = null);
$response->assertJsonFragment($data);
$response->assertJsonMissing($data);
$response->assertJsonMissingExact($data);

// Validation errors
$response->assertJsonValidationErrors($keys);
$response->assertJsonMissingValidationErrors($keys);

// Download assertions
$response->assertDownload($filename = null);

// Stream assertions
$response->assertStreamed();
$response->assertStreamedContent($content);

// Examples
$response = $this->get('/api/users');

$response->assertStatus(200)
    ->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'email']
        ],
        'meta' => ['total', 'per_page']
    ])
    ->assertJsonPath('data.0.name', 'John')
    ->assertJsonCount(10, 'data');

// View assertions
$response = $this->get('/posts');

$response->assertStatus(200)
    ->assertViewIs('posts.index')
    ->assertViewHas('posts')
    ->assertSee('Latest Posts');
```

---

## Custom Assertions

```php
// Creating custom assertions
trait CustomAssertions
{
    public function assertIsValidEmail($value, string $message = ''): void
    {
        $this->assertTrue(
            filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            $message ?: "Failed asserting that '{$value}' is a valid email"
        );
    }

    public function assertIsValidUuid($value, string $message = ''): void
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        $this->assertMatchesRegularExpression(
            $pattern,
            $value,
            $message ?: "Failed asserting that '{$value}' is a valid UUID"
        );
    }

    public function assertIsWithinRange($value, $min, $max, string $message = ''): void
    {
        $this->assertThat(
            $value,
            $this->logicalAnd(
                $this->greaterThanOrEqual($min),
                $this->lessThanOrEqual($max)
            ),
            $message
        );
    }
}

// Usage in tests
class UserTest extends TestCase
{
    use CustomAssertions;

    public function test_user_has_valid_email(): void
    {
        $user = User::find(1);

        $this->assertIsValidEmail($user->email);
    }
}
```

---

## PHPUnit 10+ Attribute Assertions

```php
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class ModernAssertionsTest extends TestCase
{
    #[Test]
    #[TestDox('It validates user email format')]
    public function emailValidation(): void
    {
        $this->assertIsValidEmail('test@example.com');
    }

    #[DoesNotPerformAssertions]
    public function test_method_runs_without_errors(): void
    {
        // Useful when you just want to ensure no exceptions
        $service = new PaymentService();
        $service->initialize(); // Just checking it runs
    }
}
```

---

## Quick Reference Card

| Assertion | Description |
|-----------|-------------|
| `assertEquals($expected, $actual)` | Loose equality (==) |
| `assertSame($expected, $actual)` | Strict equality (===) |
| `assertTrue($condition)` | Assert true |
| `assertFalse($condition)` | Assert false |
| `assertNull($value)` | Assert null |
| `assertEmpty($value)` | Assert empty |
| `assertCount($count, $array)` | Array count |
| `assertContains($needle, $haystack)` | Value in array |
| `assertArrayHasKey($key, $array)` | Key exists |
| `assertInstanceOf($class, $object)` | Instance type |
| `expectException($class)` | Exception expected |
| `assertStringContainsString($needle, $haystack)` | String contains |
| `assertMatchesRegularExpression($pattern, $string)` | Regex match |
| `assertGreaterThan($expected, $actual)` | > comparison |
| `assertLessThan($expected, $actual)` | < comparison |
