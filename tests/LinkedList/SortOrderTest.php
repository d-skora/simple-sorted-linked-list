<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\LinkedList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\RuntimeException;
use SimpleSortedLinkedList\LinkedList\SortOrder;

#[CoversClass(SortOrder::class)]
final class SortOrderTest extends TestCase
{
    public function testAscendingSortOrderComparesIntegers(): void
    {
        $order = SortOrder::ascending();

        $this->assertSame(-1, $order->compare(1, 2));
        $this->assertSame(0, $order->compare(2, 2));
        $this->assertSame(1, $order->compare(3, 2));
    }

    public function testDescendingSortOrderInvertsComparison(): void
    {
        $order = SortOrder::descending();

        $this->assertSame(1, $order->compare(1, 2));
        $this->assertSame(0, $order->compare(2, 2));
        $this->assertSame(-1, $order->compare(3, 2));
    }

    public function testAscendingSortOrderComparesStringsAlphabetically(): void
    {
        $order = SortOrder::ascending();

        $this->assertLessThan(0, $order->compare('abc', 'bcd'));
        $this->assertGreaterThan(0, $order->compare('bcd', 'abc'));
    }

    public function testCustomSortOrderUsesProvidedComparator(): void
    {
        $order = SortOrder::custom(static fn (int|string $a, int|string $b): int => (int) $b - (int) $a);

        $this->assertSame(1, $order->compare(1, 2));
        $this->assertSame(0, $order->compare(2, 2));
        $this->assertSame(-1, $order->compare(3, 2));
    }

    public function testCustomComparatorMustReturnInt(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(RuntimeException::comparatorMustReturnInt()->getMessage());

        $order = SortOrder::custom(static fn (int|string $a, int|string $b): mixed => true);

        $order->compare(1, 2);
    }

    public function testCompareThrowsWhenCustomComparatorIsNull(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(RuntimeException::comparatorNotSet()->getMessage());

        $order = SortOrder::custom(static fn (int|string $a, int|string $b): int => 0);
        $comparatorProperty = new \ReflectionProperty(SortOrder::class, 'comparator');
        $comparatorProperty->setValue($order, null);

        $order->compare(1, 2);
    }
}
