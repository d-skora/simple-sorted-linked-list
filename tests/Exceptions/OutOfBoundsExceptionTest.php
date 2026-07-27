<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\OutOfBoundsException;

#[CoversClass(OutOfBoundsException::class)]
final class OutOfBoundsExceptionTest extends TestCase
{
    public function testIndexOutOfBoundsProvidesMessage(): void
    {
        $exception = OutOfBoundsException::indexOutOfBounds();

        $this->assertSame('Index out of bounds.', $exception->getMessage());
    }

    public function testExtendsSpOutOfBoundsException(): void
    {
        $this->assertInstanceOf(\OutOfBoundsException::class, OutOfBoundsException::indexOutOfBounds());
    }
}
