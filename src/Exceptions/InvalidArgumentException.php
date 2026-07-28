<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Exceptions;

/**
 * Exception for invalid arguments passed to the package APIs.
 */
final class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Create an exception for a value that is not int or string.
     *
     * @return self
     */
    public static function onlyIntOrString(): self
    {
        return new self('Only int and string types are allowed.');
    }

    /**
     * Create an exception for inserting a mismatched scalar type.
     *
     * @param string $incoming The scalar type being inserted.
     * @param string $existing The scalar type already stored in the list.
     * @return self
     */
    public static function cannotInsertType(string $incoming, string $existing): self
    {
        return new self(sprintf('Cannot insert %s into a list of %s values.', $incoming, $existing));
    }

    /**
     * Create an exception for merging lists with different scalar types.
     *
     * @return self
     */
    public static function cannotMergeDifferentScalarTypes(): self
    {
        return new self('Cannot merge lists with different scalar types.');
    }

    /**
     * Create an exception for comparing values with different scalar types.
     *
     * @return self
     */
    public static function cannotCompareDifferentScalarTypes(): self
    {
        return new self('Cannot compare values with different scalar types.');
    }
}
