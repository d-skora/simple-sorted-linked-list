<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\LinkedList;

use SimpleSortedLinkedList\Exceptions\UnderflowException;

/**
 * Iterator over a sorted linked list.
 *
 * @internal
 * @implements \Iterator<int, int|string>
 */
final class SortedLinkedListIterator implements \Iterator
{
    private SortedLinkedList $list;
    private ?Node $currentNode;
    private int $currentIndex = 0;

    /**
     * Create an iterator for the given list.
     *
     * @param SortedLinkedList $list The list to iterate.
     */
    public function __construct(SortedLinkedList $list)
    {
        $this->list = $list;
        $this->currentNode = $this->nextLiveNode($list->getHead());
    }

    /**
     * Rewind the iterator to the first live node.
     */
    public function rewind(): void
    {
        $this->currentNode = $this->nextLiveNode($this->list->getHead());
        $this->currentIndex = 0;
    }

    /**
     * Return the current node value.
     *
     * @return int|string
     * @throws UnderflowException When the iterator is not on a valid node.
     */
    public function current(): int|string
    {
        if ($this->currentNode === null || $this->currentNode->isRemoved()) {
            throw UnderflowException::iteratorNotValid();
        }

        return $this->currentNode->getValue();
    }

    /**
     * Return the current iterator index.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->currentIndex;
    }

    /**
     * Advance to the next live node.
     */
    public function next(): void
    {
        if ($this->currentNode !== null) {
            $this->currentNode = $this->nextLiveNode($this->currentNode->getNext());
            $this->currentIndex++;
        }
    }

    /**
     * Check whether the iterator is positioned on a live node.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->currentNode !== null && !$this->currentNode->isRemoved() && $this->list->count() > 0;
    }

    /**
     * Skip any removed nodes and return the next live node.
     *
     * @param Node|null $node The node to start from.
     * @return Node|null
     */
    private function nextLiveNode(?Node $node): ?Node
    {
        while ($node !== null && $node->isRemoved()) {
            $node = $node->getNext();
        }

        return $node;
    }
}
