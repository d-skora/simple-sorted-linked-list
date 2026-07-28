<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\LinkedList;

use SimpleSortedLinkedList\Exceptions\InvalidArgumentException;
use SimpleSortedLinkedList\Exceptions\RuntimeException;

/**
 * Sort order and comparator strategy for linked-list ordering.
 */
final class SortOrder
{
    private const ASCENDING = 'ascending';
    private const DESCENDING = 'descending';
    private const CUSTOM = 'custom';

    private string $mode;
    /** @var (callable(int|string, int|string): mixed)|null */
    private $comparator;

    /**
     * Create a sort order.
     *
     * @param string $mode Sorting mode identifier.
        * @param (callable(int|string, int|string): mixed)|null $comparator
        *   Optional custom comparator when mode is custom.
     */
    private function __construct(string $mode, $comparator = null)
    {
        $this->mode = $mode;
        $this->comparator = $comparator;
    }

    /**
     * Ascending sort order.
     *
     * @return self
     */
    public static function ascending(): self
    {
        return new self(self::ASCENDING);
    }

    /**
     * Descending sort order.
     *
     * @return self
     */
    public static function descending(): self
    {
        return new self(self::DESCENDING);
    }

    /**
     * Create a custom sort order with a comparator.
     *
     * The comparator must accept two scalars (int|string) and return an int (<0, 0, >0).
     *
     * @param callable(int|string, int|string): mixed $comparator
     * @return self
     */
    public static function custom(callable $comparator): self
    {
        return new self(self::CUSTOM, $comparator);
    }

    /**
     * Compare two values according to the configured order.
     *
     * @param int|string $a
     * @param int|string $b
     * @return int Comparison result: negative if $a < $b, zero if equal, positive if $a > $b.
     */
    public function compare(int|string $a, int|string $b): int
    {
        if ($this->mode === self::CUSTOM) {
            if ($this->comparator === null) {
                throw RuntimeException::comparatorNotSet();
            }

            $result = ($this->comparator)($a, $b);
            if (!is_int($result)) {
                throw RuntimeException::comparatorMustReturnInt();
            }

            return $result;
        }

        $result = self::compareScalars($a, $b);
        return $this->mode === self::DESCENDING ? -$result : $result;
    }

    /**
     * Internal scalar comparison used for ascending/descending modes.
     *
     * @param int|string $a
     * @param int|string $b
     * @return int
     */
    private static function compareScalars(int|string $a, int|string $b): int
    {
        if (gettype($a) !== gettype($b)) {
            throw InvalidArgumentException::cannotCompareDifferentScalarTypes();
        }

        if ($a === $b) {
            return 0;
        }

        if (is_int($a) && is_int($b)) {
            return $a <=> $b;
        }

        return strcmp((string) $a, (string) $b);
    }
}
