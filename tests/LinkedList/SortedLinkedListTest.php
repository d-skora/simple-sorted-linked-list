<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\LinkedList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\InvalidArgumentException;
use SimpleSortedLinkedList\Exceptions\OutOfBoundsException;
use SimpleSortedLinkedList\Exceptions\RuntimeException;
use SimpleSortedLinkedList\Exceptions\UnderflowException;
use SimpleSortedLinkedList\Interfaces\SortedLinkedListInterface;
use SimpleSortedLinkedList\LinkedList\SortOrder;
use SimpleSortedLinkedList\LinkedList\SortedLinkedList;
use SimpleSortedLinkedList\LinkedList\SortedLinkedListFactory;

#[CoversClass(SortedLinkedList::class)]
#[CoversClass(SortedLinkedListFactory::class)]
#[CoversClass(OutOfBoundsException::class)]
final class SortedLinkedListTest extends TestCase
{
    private function createAscending(array $items): SortedLinkedList
    {
        return SortedLinkedListFactory::create($items, SortOrder::ascending());
    }

    // Creation and inspection
    public function testCreateCreatesSortedList(): void
    {
        $intList = $this->createAscending([3, 1, 2]);

        $this->assertSame([1, 2, 3], $intList->toArray());
    }

    public function testCreateCanBeCalledDirectly(): void
    {
        $list = SortedLinkedList::create([], SortOrder::ascending());

        $this->assertSame([], $list->toArray());
    }

    public function testInsertPreservesSortOrder(): void
    {
        $intList = $this->createAscending([1, 3]);
        $intList->insert(2);

        $this->assertSame([1, 2, 3], $intList->toArray());
    }

    // Head, tail, count, and membership checks
    public function testFirstReturnsHeadValue(): void
    {
        $intList = $this->createAscending([2, 1]);

        $this->assertSame(1, $intList->first());
    }

    public function testLastReturnsTailValue(): void
    {
        $intList = $this->createAscending([2, 1]);

        $this->assertSame(2, $intList->last());
    }

    public function testCountReportsNumberOfItems(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        $this->assertCount(3, $intList);
        $intList->insert(4);
        $this->assertCount(4, $intList);
    }

    public function testContainsAndCountOccurrences(): void
    {
        $intList = $this->createAscending([1, 2, 1]);

        $this->assertTrue($intList->contains(2));
        $this->assertFalse($intList->contains(3));
        $this->assertSame(2, $intList->countOccurrences(1));
        $this->assertSame(1, $intList->countOccurrences(2));
    }

    public function testAtReturnsValueByIndex(): void
    {
        $intList = $this->createAscending([10, 20, 30]);

        $this->assertSame(10, $intList->at(0));
        $this->assertSame(30, $intList->at(2));
    }

    public function testAtThrowsOutOfBoundsExceptionForNegativeIndex(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $intList = $this->createAscending([10, 20]);
        $intList->at(-1);
    }

    public function testAtThrowsOutOfBoundsExceptionForIndexEqualToCount(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $intList = $this->createAscending([10, 20]);
        $intList->at(2);
    }

    public function testAtThrowsOutOfBoundsExceptionWhenCurrentBecomesNullDueToCountMismatch(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Index out of bounds.');

        $intList = $this->createAscending([10]);
        $countProperty = new \ReflectionProperty(\SimpleSortedLinkedList\LinkedList\SortedLinkedList::class, 'count');
        $countProperty->setValue($intList, 2);

        $intList->at(1);
    }

    // Removal behavior
    public function testRemoveRemovesFirstOccurrenceOnly(): void
    {
        $intList = $this->createAscending([1, 2, 1, 1, 3]);

        $this->assertTrue($intList->remove(1));
        $this->assertSame([1, 1, 2, 3], $intList->toArray());
        $this->assertSame(2, $intList->countOccurrences(1));
    }

    public function testRemoveAllRemovesAllOccurrences(): void
    {
        $intList = $this->createAscending([1, 2, 1, 1, 3]);

        $this->assertTrue($intList->removeAll(1));
        $this->assertSame([2, 3], $intList->toArray());
        $this->assertSame(0, $intList->countOccurrences(1));
    }

    // Copy, merge, and filtering
    public function testCopyCreatesIndependentList(): void
    {
        $intList = $this->createAscending([1, 2, 3]);
        $copy = $intList->copy();

        $this->assertSame([1, 2, 3], $copy->toArray());

        $intList->insert(0);
        $this->assertSame([1, 2, 3], $copy->toArray());
    }

    public function testMergeCombinesTwoLists(): void
    {
        $left = $this->createAscending([1, 2]);
        $right = $this->createAscending([3]);

        $merged = $left->merge($right);

        $this->assertSame([1, 2, 3], $merged->toArray());
    }

    public function testFilterReturnsMatchingValues(): void
    {
        $intList = $this->createAscending([1, 2, 3]);
        $filtered = $intList->filter(static fn (int|string $value): bool => is_int($value) && $value > 1);

        $this->assertSame([2, 3], $filtered->toArray());
    }

    public function testClearEmptiesTheList(): void
    {
        $intList = $this->createAscending([1, 2]);
        $intList->clear();

        $this->assertCount(0, $intList);
        $this->assertSame([], $intList->toArray());
    }

    public function testRemoveReturnsFalseWhenItemIsMissing(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        $this->assertFalse($intList->remove(4));
    }

    public function testRemoveReturnsFalseWhenListIsEmpty(): void
    {
        $emptyList = $this->createAscending([]);

        $this->assertFalse($emptyList->remove(1));
    }

    public function testRemoveRemovesTailItem(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        $this->assertTrue($intList->remove(3));
        $this->assertSame([1, 2], $intList->toArray());
    }

    public function testRemoveHeadUpdatesHeadAndLeavesTailIntact(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        $this->assertTrue($intList->remove(1));
        $this->assertSame([2, 3], $intList->toArray());
    }

    public function testRemoveSingleItemListResetsScalarType(): void
    {
        $intList = $this->createAscending([1]);
        $intList->remove(1);

        $intList->insert('a');
        $this->assertSame(['a'], $intList->toArray());
    }

    public function testRemoveAllReturnsFalseWhenListIsEmpty(): void
    {
        $emptyList = $this->createAscending([]);

        $this->assertFalse($emptyList->removeAll(1));
    }

    public function testRemoveAllReturnsFalseWhenItemIsMissing(): void
    {
        $intList = $this->createAscending([1, 2]);

        $this->assertFalse($intList->removeAll(3));
    }

    public function testRemoveAllRemovesMiddleElementsUsingPreviousPointer(): void
    {
        $intList = $this->createAscending([1, 2, 2, 3]);

        $this->assertTrue($intList->removeAll(2));
        $this->assertSame([1, 3], $intList->toArray());
    }

    public function testRemoveAllRemovesAllValuesAndResetsTheList(): void
    {
        $intList = $this->createAscending([1, 1]);

        $this->assertTrue($intList->removeAll(1));
        $this->assertSame([], $intList->toArray());
        $this->assertCount(0, $intList);
    }

    // Exception handling
    public function testFirstThrowsUnderflowExceptionWhenEmpty(): void
    {
        $this->expectException(UnderflowException::class);

        $emptyList = $this->createAscending([]);

        $emptyList->first();
    }

    public function testLastThrowsUnderflowExceptionWhenEmpty(): void
    {
        $this->expectException(UnderflowException::class);

        $emptyList = $this->createAscending([]);

        $emptyList->last();
    }

    public function testMergeThrowsWhenScalarTypesDiffer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $left = $this->createAscending([1, 2]);
        $right = $this->createAscending(['a']);

        $left->merge($right);
    }

    public function testMergeThrowsWhenOtherIsNotConcreteSortedLinkedList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(InvalidArgumentException::cannotMergeDifferentScalarTypes()->getMessage());

        $left = $this->createAscending([1, 2]);
        $other = new class implements SortedLinkedListInterface {
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([]);
            }

            public static function create(iterable $items, SortOrder $order): self
            {
                return new self();
            }

            public function insert(int|string $item): void
            {
            }

            public function remove(int|string $item): bool
            {
                return false;
            }

            public function removeAll(int|string $item): bool
            {
                return false;
            }

            public function first(): int|string
            {
                throw new \UnderflowException();
            }

            public function last(): int|string
            {
                throw new \UnderflowException();
            }

            public function toArray(): array
            {
                return [];
            }

            public function at(int $index): int|string
            {
                throw new \OutOfBoundsException();
            }

            public function contains(int|string $item): bool
            {
                return false;
            }

            public function countOccurrences(int|string $item): int
            {
                return 0;
            }

            public function clear(): void
            {
            }

            public function count(): int
            {
                return 0;
            }

            public function copy(): self
            {
                return new self();
            }

            public function filter(callable $callback): self
            {
                return new self();
            }

            public function merge(SortedLinkedListInterface $other): SortedLinkedListInterface
            {
                return new self();
            }
        };

        $left->merge($other);
    }

    public function testAtThrowsOutOfBoundsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $emptyList = $this->createAscending([]);
        $emptyList->at(10);
    }

    public function testMixedTypeInsertionThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createAscending(['a', 1]);
    }

    public function testAssertScalarTypeThrowsForNonScalar(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(InvalidArgumentException::onlyIntOrString()->getMessage());

        $list = $this->createAscending([]);
        $method = new \ReflectionMethod(\SimpleSortedLinkedList\LinkedList\SortedLinkedList::class, 'assertScalarType');

        $method->invoke($list, new \stdClass());
    }

    public function testCustomComparatorMustReturnInt(): void
    {
        $this->expectException(RuntimeException::class);
        $custom = SortOrder::custom(static fn (int|string $a, int|string $b): mixed => true);

        SortedLinkedList::create(['a', 'b'], $custom);
    }
}
