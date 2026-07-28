<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\UnderflowException;

#[CoversClass(UnderflowException::class)]
final class UnderflowExceptionTest extends TestCase
{
    public function testCannotGetFirstProvidesMessage(): void
    {
        $ex = UnderflowException::cannotGetFirst();

        self::assertInstanceOf(UnderflowException::class, $ex);
        self::assertSame('Cannot get first element from an empty list.', $ex->getMessage());
    }

    public function testCannotGetLastProvidesMessage(): void
    {
        $ex = UnderflowException::cannotGetLast();

        self::assertInstanceOf(UnderflowException::class, $ex);
        self::assertSame('Cannot get last element from an empty list.', $ex->getMessage());
    }

    public function testIteratorNotValidProvidesMessage(): void
    {
        $ex = UnderflowException::iteratorNotValid();

        self::assertInstanceOf(UnderflowException::class, $ex);
        self::assertSame('Iterator is not on a valid element.', $ex->getMessage());
    }

    public function testInternalCountUnderflowProvidesMessage(): void
    {
        $ex = UnderflowException::internalCountUnderflow();

        self::assertInstanceOf(UnderflowException::class, $ex);
        self::assertSame('Internal count underflow detected.', $ex->getMessage());
    }
}
