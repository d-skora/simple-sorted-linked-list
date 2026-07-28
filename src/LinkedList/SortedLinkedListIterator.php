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
    /** @var \Closure(): array<int, Node> */
    private \Closure $snapshotProvider;
    /** @var \Closure(Node): int */
    private \Closure $liveIndexResolver;
    /** @var array<int, Node> */
    private array $snapshotNodes = [];
    private int $position = 0;

    /**
     * Create an iterator for the given list.
     *
     * @param SortedLinkedList $list The list to iterate.
     * @param \Closure(): array<int, Node> $snapshotProvider Returns the current node chain snapshot.
     * @param \Closure(Node): int $liveIndexResolver Resolves the node's live index in the list.
     */
    public function __construct(SortedLinkedList $list, \Closure $snapshotProvider, \Closure $liveIndexResolver)
    {
        $this->list = $list;
        $this->snapshotProvider = $snapshotProvider;
        $this->liveIndexResolver = $liveIndexResolver;
        $this->rewind();
    }

    /**
     * Rewind the iterator to the first live node.
     */
    public function rewind(): void
    {
        $this->snapshotNodes = ($this->snapshotProvider)();
        $this->position = 0;
        $this->advanceToLivePosition();
    }

    /**
     * Return the current node value.
     *
     * @return int|string
     * @throws UnderflowException When the iterator is not on a valid node.
     */
    public function current(): int|string
    {
        if (!$this->valid()) {
            throw UnderflowException::iteratorNotValid();
        }

        return $this->snapshotNodes[$this->position]->getValue();
    }

    /**
     * Return the current iterator index.
     *
     * @return int
     */
    public function key(): int
    {
        if (!$this->valid()) {
            return $this->position;
        }

        return ($this->liveIndexResolver)($this->snapshotNodes[$this->position]);
    }

    /**
     * Advance to the next live node.
     */
    public function next(): void
    {
        $this->position++;
        $this->advanceToLivePosition();
    }

    /**
     * Check whether the iterator is positioned on a live node.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->snapshotNodes[$this->position])
            && !$this->snapshotNodes[$this->position]->isRemoved()
            && $this->list->count() > 0;
    }

    /**
     * Advance internal cursor to the next non-removed snapshot node.
     */
    private function advanceToLivePosition(): void
    {
        while (isset($this->snapshotNodes[$this->position]) && $this->snapshotNodes[$this->position]->isRemoved()) {
            $this->position++;
        }
    }
}
