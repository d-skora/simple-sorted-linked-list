<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\InvalidArgumentException;

#[CoversClass(InvalidArgumentException::class)]
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testOnlyIntOrStringProvidesMessage(): void
    {
        $ex = InvalidArgumentException::onlyIntOrString();

        self::assertInstanceOf(InvalidArgumentException::class, $ex);
        self::assertSame('Only int and string types are allowed.', $ex->getMessage());
    }

    public function testCannotInsertTypeProvidesMessage(): void
    {
        $ex = InvalidArgumentException::cannotInsertType('int', 'string');

        self::assertInstanceOf(InvalidArgumentException::class, $ex);
        self::assertSame('Cannot insert int into a list of string values.', $ex->getMessage());
    }

    public function testCannotMergeDifferentScalarTypesProvidesMessage(): void
    {
        $ex = InvalidArgumentException::cannotMergeDifferentScalarTypes();

        self::assertInstanceOf(InvalidArgumentException::class, $ex);
        self::assertSame('Cannot merge lists with different scalar types.', $ex->getMessage());
    }

    public function testCannotCompareDifferentScalarTypesProvidesMessage(): void
    {
        $ex = InvalidArgumentException::cannotCompareDifferentScalarTypes();

        self::assertInstanceOf(InvalidArgumentException::class, $ex);
        self::assertSame('Cannot compare values with different scalar types.', $ex->getMessage());
    }
}
