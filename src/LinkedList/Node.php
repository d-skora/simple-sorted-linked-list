<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\LinkedList;

/**
 * Node in a singly linked sorted list.
 */
final class Node
{
    private int|string $value;
    private ?self $next;
    private bool $removed = false;

    /**
     * Node constructor.
     *
     * @param int|string $value The scalar value stored in the node.
     * @param self|null $next  Optional reference to the next node.
     */
    public function __construct(int|string $value, ?self $next = null)
    {
        $this->value = $value;
        $this->next = $next;
    }

    /**
     * Get the node value.
     *
     * @return int|string The scalar value stored in the node.
     */
    public function getValue(): int|string
    {
        return $this->value;
    }

    /**
     * Get the next node.
     *
     * @return self|null The next node or null if none.
     */
    public function getNext(): ?self
    {
        return $this->next;
    }

    /**
     * Set the next node reference.
     *
     * @param self|null $next The node to set as next.
     */
    public function setNext(?self $next): void
    {
        $this->next = $next;
    }

    /**
     * Mark the node as removed from the list.
     */
    public function markRemoved(): void
    {
        $this->removed = true;
    }

    /**
     * Check whether the node has been removed from the list.
     *
     * @return bool True when the node has been unlinked from the list.
     */
    public function isRemoved(): bool
    {
        return $this->removed;
    }
}
