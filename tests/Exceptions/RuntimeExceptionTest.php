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

        $this->assertInstanceOf(RuntimeException::class, $ex);
        $this->assertSame('Custom comparator must return an int.', $ex->getMessage());
    }

    public function testComparatorNotSetProvidesMessage(): void
    {
        $ex = RuntimeException::comparatorNotSet();

        $this->assertInstanceOf(RuntimeException::class, $ex);
        $this->assertSame('Custom comparator is not set.', $ex->getMessage());
    }
}
