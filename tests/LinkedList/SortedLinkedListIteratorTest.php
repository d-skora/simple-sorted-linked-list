<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\LinkedList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Exceptions\UnderflowException;
use SimpleSortedLinkedList\LinkedList\SortOrder;
use SimpleSortedLinkedList\LinkedList\SortedLinkedList;
use SimpleSortedLinkedList\LinkedList\SortedLinkedListIterator;

#[CoversClass(SortedLinkedList::class)]
#[CoversClass(SortedLinkedListIterator::class)]
#[CoversClass(UnderflowException::class)]
final class SortedLinkedListIteratorTest extends TestCase
{
    /**
     * @param list<int|string> $items
     */
    private function createAscending(array $items): SortedLinkedList
    {
        return SortedLinkedList::create($items, SortOrder::ascending());
    }

    public function testIteratorMethodsWorkDirectly(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        self::assertTrue($iterator->valid());
        self::assertSame(0, $iterator->key());
        self::assertSame(1, $iterator->current());

        $iterator->next();
        self::assertTrue($iterator->valid());
        self::assertSame(1, $iterator->key());
        self::assertSame(2, $iterator->current());

        $iterator->next();
        self::assertFalse($iterator->valid());
    }

    public function testCurrentThrowsWhenIteratorIsInvalid(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $iterator->next();
        $iterator->next();

        $this->expectException(UnderflowException::class);
        $iterator->current();
    }

    public function testClearResetsTheIterator(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();
        $iterator->rewind();
        $iterator->next();

        $list->clear();

        self::assertFalse($iterator->valid());
    }

    public function testCurrentThrowsAfterClear(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $list->clear();

        $this->expectException(UnderflowException::class);
        $iterator->current();
    }

    public function testRemoveAllRemovesAllValuesAndResetsTheList(): void
    {
        $list = $this->createAscending([1, 1]);
        $iterator = $list->getIterator();

        self::assertTrue($list->removeAll(1));
        self::assertSame([], $list->toArray());
        self::assertCount(0, $list);
        self::assertFalse($iterator->valid());
    }

    public function testForeachCanSafelyRemoveCurrentElement(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $values = [];

        foreach ($list as $value) {
            $values[] = $value;
            if ($value === 1 || $value === 3) {
                $list->remove($value);
            }
        }

        self::assertSame([1, 2, 3], $values);
        self::assertSame([2], $list->toArray());
    }

    public function testForeachSkipsRemovedNodesAfterRemoveAll(): void
    {
        $list = $this->createAscending([1, 2, 2, 3]);
        $values = [];

        foreach ($list as $value) {
            $values[] = $value;
            if ($value === 2) {
                $list->removeAll(2);
            }
        }

        self::assertSame([1, 2, 3], $values);
        self::assertSame([1, 3], $list->toArray());
    }

    public function testRewindResetsTheIteratorToTheFirstElement(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $iterator->next();
        $iterator->rewind();

        self::assertSame(0, $iterator->key());
        self::assertSame(1, $iterator->current());
        self::assertTrue($iterator->valid());
    }

    public function testKeyReturnsTheCurrentIteratorIndex(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        self::assertSame(0, $iterator->key());

        $iterator->next();
        self::assertSame(1, $iterator->key());
    }

    public function testNextAdvancesToTheNextElement(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $iterator->next();

        self::assertSame(2, $iterator->current());
        self::assertSame(1, $iterator->key());
    }

    public function testValidReflectsIteratorPosition(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        self::assertTrue($iterator->valid());

        $iterator->next();
        self::assertTrue($iterator->valid());

        $iterator->next();
        self::assertFalse($iterator->valid());
    }
}
