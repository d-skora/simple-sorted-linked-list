<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Interfaces;

use SimpleSortedLinkedList\LinkedList\SortOrder;

/**
 * @template TKey of array-key
 * @template TValue of int|string
 *
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface SortedLinkedListInterface extends \Countable, \IteratorAggregate
{
    /**
     * Retrieve an iterator for the list.
     *
     * @return \Iterator<int, int|string>
     */
    public function getIterator(): \Traversable;

    /**
     * Create a sorted linked list from items using the provided order.
     *
     * @param iterable<int|string> $items
     * @param SortOrder $order
     * @return self<int, int|string>
     */
    public static function create(iterable $items, SortOrder $order): self;

    /**
     * Insert an item into the list preserving sort order.
     *
     * @param int|string $item
     */
    public function insert(int|string $item): void;

    /**
     * Remove the first occurrence of an item from the list.
     *
     * @param int|string $item
     * @return bool True if an item was removed, false otherwise.
     */
    public function remove(int|string $item): bool;

    /**
     * Remove all occurrences of an item from the list.
     *
     * @param int|string $item
     * @return bool True if at least one item was removed, false otherwise.
     */
    public function removeAll(int|string $item): bool;

    /**
     * Get the first (head) value of the list.
     *
     * @return int|string
     */
    public function first(): int|string;

    /**
     * Get the last (tail) value of the list.
     *
     * @return int|string
     */
    public function last(): int|string;

    /**
     * Convert the list to an array of values.
     *
    * @return list<int|string>
     */
    public function toArray(): array;

    /**
     * Get the value at the given index.
     *
     * @param int $index
     * @return int|string
     * @throws \SimpleSortedLinkedList\Exceptions\OutOfBoundsException When the index is out of bounds.
     */
    public function at(int $index): int|string;

    /**
     * Check whether the list contains a given item.
     *
     * @param int|string $item
     * @return bool
     */
    public function contains(int|string $item): bool;

    /**
     * Count how many times an item occurs in the list.
     *
     * @param int|string $item
     * @return int
     */
    public function countOccurrences(int|string $item): int;

    /**
     * Clear the list.
     */
    public function clear(): void;

    /**
     * Create a copy of the list.
     *
        * @return self<int, int|string>
        */
    public function copy(): self;

    /**
     * Filter the list using a callback and return a new list.
     *
        * @param callable(int|string): bool $callback
     * @return self<int, int|string>
     */
    public function filter(callable $callback): self;

    /**
     * Merge another sorted list into this one and return the merged list.
     *
     * @param SortedLinkedListInterface<int, int|string> $other
     * @return SortedLinkedListInterface<int, int|string>
     */
    public function merge(SortedLinkedListInterface $other): SortedLinkedListInterface;
}
