<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Exceptions;

/**
 * Base runtime exception for the package.
 */
class RuntimeException extends \RuntimeException
{
    /**
     * Create an exception for a custom comparator returning a non-int value.
     *
     * @return self
     */
    public static function comparatorMustReturnInt(): self
    {
        return new self('Custom comparator must return an int.');
    }

    /**
     * Create an exception for a missing custom comparator.
     *
     * @return self
     */
    public static function comparatorNotSet(): self
    {
        return new self('Custom comparator is not set.');
    }
}
