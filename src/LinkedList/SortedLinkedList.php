<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\LinkedList;

use SimpleSortedLinkedList\Exceptions\InvalidArgumentException;
use SimpleSortedLinkedList\Exceptions\OutOfBoundsException;
use SimpleSortedLinkedList\Exceptions\UnderflowException;
use SimpleSortedLinkedList\Interfaces\SortedLinkedListInterface;

/**
 * Sorted singly linked list with stable scalar typing and custom order support.
 *
 * @implements \SimpleSortedLinkedList\Interfaces\SortedLinkedListInterface<int, int|string>
 */
final class SortedLinkedList implements SortedLinkedListInterface
{
    private ?Node $head = null;
    private ?Node $tail = null;
    private ?string $type = null;
    /** @var int<0, max> */
    private int $count = 0;

    /**
     * Construct a sorted linked list with the given order.
     *
     * @param SortOrder $order Sorting order or comparator to use.
     */
    public function __construct(private readonly SortOrder $order)
    {
    }

    /**
     * Ensure node links are broken when the list is released.
     */
    public function __destruct()
    {
        $this->clear();
    }

    /**
     * Create a new sorted linked list and populate it with items.
     *
     * @param iterable<int|string> $items Items to insert into the list.
     * @param SortOrder $order Sorting order for the list.
     * @return self
     */
    public static function create(iterable $items, SortOrder $order): self
    {
        $list = new self($order);
        foreach ($items as $item) {
            $list->insert($item);
        }

        return $list;
    }

    /**
     * Insert an item while preserving sort order.
     *
     * @param int|string $item The scalar item to insert.
     * @throws InvalidArgumentException When a mixed scalar type is inserted.
     */
    public function insert(int|string $item): void
    {
        $this->assertScalarType($item);

        $node = new Node($item);
        if ($this->head === null) {
            $this->head = $this->tail = $node;
            $this->count++;
            return;
        }

        [$previous, $current] = $this->findInsertionPoint($item);
        $this->linkInsertedNode($previous, $current, $node);
        $this->count++;
    }

    /**
     * Remove the first occurrence of the given item.
     *
     * @param int|string $item The item to remove.
     * @return bool True if an item was removed, false otherwise.
     */
    public function remove(int|string $item): bool
    {
        [$previous, $current] = $this->findNodeWithPrevious($item);
        if ($current === null) {
            return false;
        }

        $this->unlinkNode($previous, $current);
        return true;
    }

    /**
     * Remove all occurrences of the given item.
     *
     * @param int|string $item The item to remove.
     * @return bool True if one or more items were removed, false otherwise.
     */
    public function removeAll(int|string $item): bool
    {
        $removed = false;
        $previous = null;
        $current = $this->head;

        while ($current !== null) {
            if ($current->getValue() === $item) {
                $removed = true;
                $current = $this->unlinkNode($previous, $current);
                continue;
            }

            $previous = $current;
            $current = $current->getNext();
        }

        return $removed;
    }

    /**
     * Return the first (head) value.
     *
     * @return int|string
     * @throws UnderflowException When the list is empty.
     */
    public function first(): int|string
    {
        if ($this->head === null) {
            throw UnderflowException::cannotGetFirst();
        }

        return $this->head->getValue();
    }

    /**
     * Return the last (tail) value.
     *
     * @return int|string
     * @throws UnderflowException When the list is empty.
     */
    public function last(): int|string
    {
        if ($this->tail === null) {
            throw UnderflowException::cannotGetLast();
        }

        return $this->tail->getValue();
    }

    /**
     * Convert the list to an array of values.
     *
     * @return list<int|string>
     */
    public function toArray(): array
    {
        $values = [];
        $current = $this->head;
        while ($current !== null) {
            $values[] = $current->getValue();
            $current = $current->getNext();
        }

        return $values;
    }

    /**
     * Get the value at the specified index.
     *
     * @param int $index Zero-based index of the element.
     * @return int|string
     * @throws OutOfBoundsException When the index is out of bounds.
     */
    public function at(int $index): int|string
    {
        return $this->nodeAtIndex($index)->getValue();
    }

    /**
     * Check whether the list contains the provided item.
     *
     * @param int|string $item
     * @return bool
     */
    public function contains(int|string $item): bool
    {
        if (!$this->isCompatibleLookupType($item)) {
            return false;
        }

        $current = $this->head;
        while ($current !== null) {
            if ($this->valuesEqual($current->getValue(), $item)) {
                return true;
            }

            $current = $current->getNext();
        }

        return false;
    }

    /**
     * Count the occurrences of an item in the list.
     *
     * @param int|string $item
     * @return int
     */
    public function countOccurrences(int|string $item): int
    {
        if (!$this->isCompatibleLookupType($item)) {
            return 0;
        }

        $count = 0;
        $current = $this->head;
        while ($current !== null) {
            if ($this->valuesEqual($current->getValue(), $item)) {
                $count++;
            }

            $current = $current->getNext();
        }

        return $count;
    }

    /**
     * Clear the list, resetting internal state.
     */
    public function clear(): void
    {
        $current = $this->head;
        while ($current !== null) {
            $next = $current->getNext();
            $current->markRemoved();
            $current->setNext(null);
            $current = $next;
        }

        $this->count = 0;
        $this->resetEmptyState();
    }

    /**
     * Create a shallow copy of the list.
     *
     * @return self
     */
    public function copy(): self
    {
        $copy = new self($this->order);
        foreach ($this as $item) {
            $copy->insert($item);
        }

        return $copy;
    }

    /**
     * Filter the list using a callback and return a new list with matching items.
     *
     * @param callable(int|string): bool $callback Predicate called for each item.
     * @return self
     */
    public function filter(callable $callback): self
    {
        $list = new self($this->order);
        foreach ($this as $item) {
            if ($callback($item)) {
                $list->insert($item);
            }
        }

        return $list;
    }

    /**
     * Merge another sorted list into this one and return the merged result.
     *
     * @param SortedLinkedListInterface<int, int|string> $other
     * @return SortedLinkedListInterface<int, int|string>
     * @throws InvalidArgumentException If the lists have different scalar types.
     */
    public function merge(SortedLinkedListInterface $other): SortedLinkedListInterface
    {
        $merged = $this->copy();
        foreach ($other as $item) {
            $merged->insert($item);
        }

        return $merged;
    }

    /**
     * Get an iterator for traversing the list without sharing iterator state
     * with the underlying collection.
     *
     * @return \Iterator<int, int|string>
     */
    public function getIterator(): \Traversable
    {
        return new SortedLinkedListIterator(
            $this,
            function (): array {
                $nodes = [];
                $current = $this->head;
                while ($current !== null) {
                    $nodes[] = $current;
                    $current = $current->getNext();
                }

                return $nodes;
            },
            function (Node $needle): int {
                $current = $this->head;
                $index = 0;
                while ($current !== null) {
                    if (!$current->isRemoved()) {
                        if ($current === $needle) {
                            return $index;
                        }

                        $index++;
                    }

                    $current = $current->getNext();
                }

                return -1;
            }
        );
    }

    /**
     * Return the number of items in the list.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Ensure the scalar type of items in the list is consistent.
     *
     * @param mixed $item
     * @throws InvalidArgumentException When a mixed scalar type is inserted.
     */
    private function assertScalarType(mixed $item): void
    {
        if (!is_int($item) && !is_string($item)) {
            throw InvalidArgumentException::onlyIntOrString();
        }

        $incomingType = is_int($item) ? 'int' : 'string';
        if ($this->type === null) {
            $this->type = $incomingType;
            return;
        }

        if ($this->type !== $incomingType) {
            throw InvalidArgumentException::cannotInsertType($incomingType, $this->type);
        }
    }

    /**
     * Find the insertion point for a new value.
     *
     * @param int|string $item The value being inserted.
     * @return array{0: ?Node, 1: ?Node} Previous and current nodes at the insertion point.
     */
    private function findInsertionPoint(int|string $item): array
    {
        $previous = null;
        $current = $this->head;
        while ($current !== null && $this->order->compare($current->getValue(), $item) <= 0) {
            $previous = $current;
            $current = $current->getNext();
        }

        return [$previous, $current];
    }

    /**
     * Link a newly created node into the list.
     *
     * @param Node|null $previous The node before the insertion point.
     * @param Node|null $next The node after the insertion point.
     * @param Node $node The node to insert.
     */
    private function linkInsertedNode(?Node $previous, ?Node $next, Node $node): void
    {
        $node->setNext($next);
        if ($previous === null) {
            $this->head = $node;
        } else {
            $previous->setNext($node);
        }

        if ($next === null) {
            $this->tail = $node;
        }
    }

    /**
     * Find a node by value together with its previous node.
     *
     * @param int|string $item The value to find.
     * @return array{0: ?Node, 1: ?Node} Previous and current nodes.
     */
    private function findNodeWithPrevious(int|string $item): array
    {
        if (!$this->isCompatibleLookupType($item)) {
            return [null, null];
        }

        $previous = null;
        $current = $this->head;

        while ($current !== null) {
            if ($this->valuesEqual($current->getValue(), $item)) {
                return [$previous, $current];
            }

            $previous = $current;
            $current = $current->getNext();
        }

        return [null, null];
    }

    /**
     * Unlink a node from the list and return the next node.
     *
     * @param Node|null $previous The node immediately before the node to unlink.
     * @param Node $current The node to unlink.
     * @return Node|null The next node after the unlinked node.
     */
    private function unlinkNode(?Node $previous, Node $current): ?Node
    {
        $next = $current->getNext();
        if ($previous === null) {
            $this->head = $next;
        } else {
            $previous->setNext($next);
        }

        if ($current === $this->tail) {
            $this->tail = $previous;
        }

        $current->markRemoved();
        if ($this->count === 0) {
            throw UnderflowException::internalCountUnderflow();
        }

        $this->count--;
        if ($this->count === 0) {
            $this->resetEmptyState();
        }

        return $next;
    }

    /**
     * Resolve the node at the requested index.
     *
     * @param int $index Zero-based index of the element.
     * @return Node
     * @throws OutOfBoundsException When the index is invalid.
     */
    private function nodeAtIndex(int $index): Node
    {
        if ($index < 0 || $index >= $this->count) {
            throw OutOfBoundsException::indexOutOfBounds();
        }

        $current = $this->head;
        for ($position = 0; $position < $index; $position++) {
            $current = $current?->getNext();
        }

        if ($current === null) {
            throw OutOfBoundsException::indexOutOfBounds();
        }

        return $current;
    }

    /**
     * Reset the list fields used for empty-state bookkeeping.
     */
    private function resetEmptyState(): void
    {
        $this->head = null;
        $this->tail = null;
        $this->type = null;
    }

    /**
     * @param int|string $left
     * @param int|string $right
     */
    private function valuesEqual(int|string $left, int|string $right): bool
    {
        return $this->order->compare($left, $right) === 0;
    }

    /**
     * @param int|string $item
     */
    private function isCompatibleLookupType(int|string $item): bool
    {
        if ($this->type === null) {
            return false;
        }

        return (is_int($item) && $this->type === 'int')
            || (is_string($item) && $this->type === 'string');
    }
}
