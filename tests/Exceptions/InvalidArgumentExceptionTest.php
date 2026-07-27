<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\InvalidArgumentException;

#[CoversClass(InvalidArgumentException::class)]
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testOnlyIntOrStringProvidesMessage(): void
    {
        $ex = InvalidArgumentException::onlyIntOrString();

        $this->assertInstanceOf(InvalidArgumentException::class, $ex);
        $this->assertSame('Only int and string types are allowed.', $ex->getMessage());
    }

    public function testCannotInsertTypeProvidesMessage(): void
    {
        $ex = InvalidArgumentException::cannotInsertType('int', 'string');

        $this->assertInstanceOf(InvalidArgumentException::class, $ex);
        $this->assertSame('Cannot insert int into a list of string values.', $ex->getMessage());
    }

    public function testCannotMergeDifferentScalarTypesProvidesMessage(): void
    {
        $ex = InvalidArgumentException::cannotMergeDifferentScalarTypes();

        $this->assertInstanceOf(InvalidArgumentException::class, $ex);
        $this->assertSame('Cannot merge lists with different scalar types.', $ex->getMessage());
    }
}
