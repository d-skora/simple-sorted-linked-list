<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\LinkedList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\LinkedList\Node;

#[CoversClass(Node::class)]
final class NodeTest extends TestCase
{
    public function testGetValueReturnsStoredValue(): void
    {
        $node = new Node(123);

        self::assertSame(123, $node->getValue());
    }

    public function testGetNextReturnsNullWhenNoNextNodeSet(): void
    {
        $node = new Node('first');

        self::assertNull($node->getNext());
    }

    public function testSetNextLinksToNextNode(): void
    {
        $first = new Node(1);
        $second = new Node(2);

        $first->setNext($second);

        self::assertSame($second, $first->getNext());
    }

    public function testSetNextAllowsNullToClearLink(): void
    {
        $first = new Node(1);
        $second = new Node(2);

        $first->setNext($second);
        $first->setNext(null);

        self::assertNull($first->getNext());
    }

    public function testMarkRemovedSetsRemovedFlag(): void
    {
        $node = new Node(1);

        self::assertFalse($node->isRemoved());

        $node->markRemoved();

        self::assertTrue($node->isRemoved());
    }
}
