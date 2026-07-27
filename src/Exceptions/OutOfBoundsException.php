<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Exceptions;

/**
 * Exception for index-out-of-range access on a sorted linked list.
 */
final class OutOfBoundsException extends \OutOfBoundsException
{
    /**
     * Create an out-of-bounds exception for an invalid list index.
     *
     * @return self
     */
    public static function indexOutOfBounds(): self
    {
        return new self('Index out of bounds.');
    }
}
