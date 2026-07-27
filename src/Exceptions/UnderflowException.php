<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Exceptions;

/**
 * Exception for empty-collection and invalid-position access.
 */
final class UnderflowException extends \UnderflowException
{
    /**
     * Create a new underflow exception.
     *
     * @param string $message Exception message describing the underflow condition.
     */
    public function __construct(string $message = 'List is empty or position out of bounds')
    {
        parent::__construct($message);
    }

    /**
     * Create an exception for empty-list access to the first element.
     *
     * @return self
     */
    public static function cannotGetFirst(): self
    {
        return new self('Cannot get first element from an empty list.');
    }

    /**
     * Create an exception for empty-list access to the last element.
     *
     * @return self
     */
    public static function cannotGetLast(): self
    {
        return new self('Cannot get last element from an empty list.');
    }

    /**
     * Create an exception for iterator access when the iterator is invalid.
     *
     * @return self
     */
    public static function iteratorNotValid(): self
    {
        return new self('Iterator is not on a valid element.');
    }
}
