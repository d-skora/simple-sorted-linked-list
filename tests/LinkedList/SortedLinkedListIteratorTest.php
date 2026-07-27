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
    private function createAscending(array $items): SortedLinkedList
    {
        return SortedLinkedList::create($items, SortOrder::ascending());
    }

    public function testIteratorMethodsWorkDirectly(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertSame(0, $iterator->key());
        $this->assertSame(1, $iterator->current());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertSame(1, $iterator->key());
        $this->assertSame(2, $iterator->current());

        $iterator->next();
        $this->assertFalse($iterator->valid());
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

        $this->assertFalse($iterator->valid());
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

        $this->assertTrue($list->removeAll(1));
        $this->assertSame([], $list->toArray());
        $this->assertCount(0, $list);
        $this->assertFalse($iterator->valid());
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

        $this->assertSame([1, 2, 3], $values);
        $this->assertSame([2], $list->toArray());
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

        $this->assertSame([1, 2, 3], $values);
        $this->assertSame([1, 3], $list->toArray());
    }

    public function testRewindResetsTheIteratorToTheFirstElement(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $iterator->next();
        $iterator->rewind();

        $this->assertSame(0, $iterator->key());
        $this->assertSame(1, $iterator->current());
        $this->assertTrue($iterator->valid());
    }

    public function testKeyReturnsTheCurrentIteratorIndex(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $this->assertSame(0, $iterator->key());

        $iterator->next();
        $this->assertSame(1, $iterator->key());
    }

    public function testNextAdvancesToTheNextElement(): void
    {
        $list = $this->createAscending([1, 2, 3]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $iterator->next();

        $this->assertSame(2, $iterator->current());
        $this->assertSame(1, $iterator->key());
    }

    public function testValidReflectsIteratorPosition(): void
    {
        $list = $this->createAscending([1, 2]);
        $iterator = $list->getIterator();

        $iterator->rewind();
        $this->assertTrue($iterator->valid());

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }
}
