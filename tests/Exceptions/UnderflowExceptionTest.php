<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\UnderflowException;

#[CoversClass(UnderflowException::class)]
final class UnderflowExceptionTest extends TestCase
{
    public function testCannotGetFirstProvidesMessage(): void
    {
        $ex = UnderflowException::cannotGetFirst();

        $this->assertInstanceOf(UnderflowException::class, $ex);
        $this->assertSame('Cannot get first element from an empty list.', $ex->getMessage());
    }

    public function testCannotGetLastProvidesMessage(): void
    {
        $ex = UnderflowException::cannotGetLast();

        $this->assertInstanceOf(UnderflowException::class, $ex);
        $this->assertSame('Cannot get last element from an empty list.', $ex->getMessage());
    }

    public function testIteratorNotValidProvidesMessage(): void
    {
        $ex = UnderflowException::iteratorNotValid();

        $this->assertInstanceOf(UnderflowException::class, $ex);
        $this->assertSame('Iterator is not on a valid element.', $ex->getMessage());
    }
}
