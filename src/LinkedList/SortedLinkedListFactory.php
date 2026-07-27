<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\LinkedList;

use SimpleSortedLinkedList\Interfaces\SortedLinkedListInterface;

/**
 * Factory for creating sorted linked lists.
 */
final class SortedLinkedListFactory
{
    /**
     * Create a sorted linked list from an iterable of items.
     *
     * @param iterable<int|string> $items Items to insert into the new list.
     * @param SortOrder $order Sorting order or custom comparator.
     * @return SortedLinkedListInterface<int, int|string> The created sorted linked list.
     */
    public static function create(iterable $items, SortOrder $order): SortedLinkedListInterface
    {
        return SortedLinkedList::create($items, $order);
    }
}
