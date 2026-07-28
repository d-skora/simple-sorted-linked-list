<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\RuntimeException;

#[CoversClass(RuntimeException::class)]
final class RuntimeExceptionTest extends TestCase
{
    public function testComparatorMustReturnIntProvidesMessage(): void
    {
        $ex = RuntimeException::comparatorMustReturnInt();

        self::assertInstanceOf(RuntimeException::class, $ex);
        self::assertSame('Custom comparator must return an int.', $ex->getMessage());
    }

    public function testComparatorNotSetProvidesMessage(): void
    {
        $ex = RuntimeException::comparatorNotSet();

        self::assertInstanceOf(RuntimeException::class, $ex);
        self::assertSame('Custom comparator is not set.', $ex->getMessage());
    }
}
