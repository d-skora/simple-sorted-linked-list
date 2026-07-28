<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Feature\LinkedList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\InvalidArgumentException;
use SimpleSortedLinkedList\Exceptions\OutOfBoundsException;
use SimpleSortedLinkedList\Exceptions\RuntimeException;
use SimpleSortedLinkedList\Exceptions\UnderflowException;
use SimpleSortedLinkedList\Interfaces\SortedLinkedListInterface;
use SimpleSortedLinkedList\LinkedList\SortOrder;
use SimpleSortedLinkedList\LinkedList\SortedLinkedList;
use SimpleSortedLinkedList\LinkedList\SortedLinkedListIterator;

#[CoversClass(SortedLinkedList::class)]
#[CoversClass(OutOfBoundsException::class)]
final class SortedLinkedListTest extends TestCase
{
    /**
     * @param list<int|string> $items
     */
    private function createAscending(array $items): SortedLinkedList
    {
        return SortedLinkedList::create($items, SortOrder::ascending());
    }

    // Creation and inspection
    public function testCreateCreatesSortedList(): void
    {
        $intList = $this->createAscending([3, 1, 2]);

        self::assertSame([1, 2, 3], $intList->toArray());
    }

    public function testCreateCanBeCalledDirectly(): void
    {
        $list = SortedLinkedList::create([], SortOrder::ascending());

        self::assertSame([], $list->toArray());
    }

    public function testInsertPreservesSortOrder(): void
    {
        $intList = $this->createAscending([1, 3]);
        $intList->insert(2);

        self::assertSame([1, 2, 3], $intList->toArray());
    }

    // Head, tail, count, and membership checks
    public function testFirstReturnsHeadValue(): void
    {
        $intList = $this->createAscending([2, 1]);

        self::assertSame(1, $intList->first());
    }

    public function testLastReturnsTailValue(): void
    {
        $intList = $this->createAscending([2, 1]);

        self::assertSame(2, $intList->last());
    }

    public function testCountReportsNumberOfItems(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        self::assertCount(3, $intList);
        $intList->insert(4);
        self::assertCount(4, $intList);
    }

    public function testContainsAndCountOccurrences(): void
    {
        $intList = $this->createAscending([1, 2, 1]);

        self::assertTrue($intList->contains(2));
        self::assertFalse($intList->contains(3));
        self::assertSame(2, $intList->countOccurrences(1));
        self::assertSame(1, $intList->countOccurrences(2));
    }

    public function testContainsReturnsFalseForLookupWithDifferentScalarType(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        self::assertFalse($intList->contains('1'));
    }

    public function testCountOccurrencesReturnsZeroForLookupWithDifferentScalarType(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        self::assertSame(0, $intList->countOccurrences('1'));
    }

    public function testGetIteratorReturnsIndependentIterators(): void
    {
        $list = $this->createAscending([1, 2, 3]);

        $first = $list->getIterator();
        $second = $list->getIterator();

        self::assertInstanceOf(SortedLinkedListIterator::class, $first);
        self::assertInstanceOf(SortedLinkedListIterator::class, $second);
        self::assertNotSame($first, $second);

        $first->next();

        self::assertSame(2, $first->current());
        self::assertSame(1, $second->current());
    }

    public function testGetIteratorKeyReturnsMinusOneWhenSnapshotNodeIsNoLongerReachable(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $headProperty = new \ReflectionProperty(SortedLinkedList::class, 'head');
        $head = $headProperty->getValue($list);
        self::assertInstanceOf(\SimpleSortedLinkedList\LinkedList\Node::class, $head);

        $headProperty->setValue($list, $head->getNext());

        self::assertTrue($iterator->valid());
        self::assertSame(-1, $iterator->key());
    }

    public function testAtReturnsValueByIndex(): void
    {
        $intList = $this->createAscending([10, 20, 30]);

        self::assertSame(10, $intList->at(0));
        self::assertSame(30, $intList->at(2));
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

        self::assertTrue($intList->remove(1));
        self::assertSame([1, 1, 2, 3], $intList->toArray());
        self::assertSame(2, $intList->countOccurrences(1));
    }

    public function testRemoveAllRemovesAllOccurrences(): void
    {
        $intList = $this->createAscending([1, 2, 1, 1, 3]);

        self::assertTrue($intList->removeAll(1));
        self::assertSame([2, 3], $intList->toArray());
        self::assertSame(0, $intList->countOccurrences(1));
    }

    // Copy, merge, and filtering
    public function testCopyCreatesIndependentList(): void
    {
        $intList = $this->createAscending([1, 2, 3]);
        $copy = $intList->copy();

        self::assertSame([1, 2, 3], $copy->toArray());

        $intList->insert(0);
        self::assertSame([1, 2, 3], $copy->toArray());
    }

    public function testMergeCombinesTwoLists(): void
    {
        $left = $this->createAscending([1, 2]);
        $right = $this->createAscending([3]);

        $merged = $left->merge($right);

        self::assertSame([1, 2, 3], $merged->toArray());
    }

    public function testFilterReturnsMatchingValues(): void
    {
        $intList = $this->createAscending([1, 2, 3]);
        $filtered = $intList->filter(static fn (int|string $value): bool => is_int($value) && $value > 1);

        self::assertSame([2, 3], $filtered->toArray());
    }

    public function testClearEmptiesTheList(): void
    {
        $intList = $this->createAscending([1, 2]);
        $intList->clear();

        self::assertCount(0, $intList);
        self::assertSame([], $intList->toArray());
    }

    public function testRemoveReturnsFalseWhenItemIsMissing(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        self::assertFalse($intList->remove(4));
    }

    public function testRemoveReturnsFalseWhenListIsEmpty(): void
    {
        $emptyList = $this->createAscending([]);

        self::assertFalse($emptyList->remove(1));
    }

    public function testRemoveRemovesTailItem(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        self::assertTrue($intList->remove(3));
        self::assertSame([1, 2], $intList->toArray());
    }

    public function testRemoveHeadUpdatesHeadAndLeavesTailIntact(): void
    {
        $intList = $this->createAscending([1, 2, 3]);

        self::assertTrue($intList->remove(1));
        self::assertSame([2, 3], $intList->toArray());
    }

    public function testRemoveSingleItemListResetsScalarType(): void
    {
        $intList = $this->createAscending([1]);
        $intList->remove(1);

        $intList->insert('a');
        self::assertSame(['a'], $intList->toArray());
    }

    public function testRemoveAllReturnsFalseWhenListIsEmpty(): void
    {
        $emptyList = $this->createAscending([]);

        self::assertFalse($emptyList->removeAll(1));
    }

    public function testRemoveAllReturnsFalseWhenItemIsMissing(): void
    {
        $intList = $this->createAscending([1, 2]);

        self::assertFalse($intList->removeAll(3));
    }

    public function testRemoveAllRemovesMiddleElementsUsingPreviousPointer(): void
    {
        $intList = $this->createAscending([1, 2, 2, 3]);

        self::assertTrue($intList->removeAll(2));
        self::assertSame([1, 3], $intList->toArray());
    }

    public function testRemoveAllRemovesAllValuesAndResetsTheList(): void
    {
        $intList = $this->createAscending([1, 1]);

        self::assertTrue($intList->removeAll(1));
        self::assertSame([], $intList->toArray());
        self::assertCount(0, $intList);
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

    public function testUnlinkNodeThrowsUnderflowExceptionWhenInternalCountIsAlreadyZero(): void
    {
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage(UnderflowException::internalCountUnderflow()->getMessage());

        $list = $this->createAscending([1]);

        $countProperty = new \ReflectionProperty(SortedLinkedList::class, 'count');
        $countProperty->setValue($list, 0);

        $headProperty = new \ReflectionProperty(SortedLinkedList::class, 'head');
        $head = $headProperty->getValue($list);
        self::assertNotNull($head);

        $unlinkNode = new \ReflectionMethod(SortedLinkedList::class, 'unlinkNode');
        $unlinkNode->invoke($list, null, $head);
    }

    public function testMergeThrowsWhenScalarTypesDiffer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $left = $this->createAscending([1, 2]);
        $right = $this->createAscending(['a']);

        $left->merge($right);
    }

    public function testMergeAcceptsOtherSortedLinkedListInterfaceImplementation(): void
    {
        $left = $this->createAscending([1, 2]);
        $other = new class implements SortedLinkedListInterface {
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([3, 4]);
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
                return 2;
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

        $merged = $left->merge($other);

        self::assertSame([1, 2, 3, 4], $merged->toArray());
        self::assertSame([1, 2], $left->toArray());
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
        $custom = SortOrder::custom(static fn (int|string $a, int|string $b): mixed => $a === $b);

        SortedLinkedList::create(['a', 'b'], $custom);
    }
}
