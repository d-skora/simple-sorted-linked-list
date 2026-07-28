<?php

declare(strict_types=1);

namespace SimpleSortedLinkedList\Tests\Feature\LinkedList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSortedLinkedList\Interfaces\SortedLinkedListInterface;
use SimpleSortedLinkedList\LinkedList\Node;
use SimpleSortedLinkedList\LinkedList\SortOrder;
use SimpleSortedLinkedList\LinkedList\SortedLinkedList;
use SimpleSortedLinkedList\LinkedList\SortedLinkedListIterator;

/**
 * Regression tests for cross-cutting SortedLinkedList defects.
 */
#[CoversClass(SortedLinkedList::class)]
#[CoversClass(SortedLinkedListIterator::class)]
#[CoversClass(Node::class)]
final class SortedLinkedListRegressionTest extends TestCase
{
    private const LARGE_LIST_SIZE = 200000;

    #[Group('slow')]
    public function testLargeListIsDestroyedWithoutCrashingThePhpProcess(): void
    {
        [$exitCode, $output] = $this->runInSubprocess(
            '$list = SortedLinkedList::create(range(' . self::LARGE_LIST_SIZE . ', 1, -1), SortOrder::ascending());'
            . PHP_EOL . 'unset($list);'
        );

        self::assertSame(
            0,
            $exitCode,
            sprintf(
                'Destroying a %d-node list must not crash PHP. Got exit code %d (139 = SIGSEGV). Output: %s',
                self::LARGE_LIST_SIZE,
                $exitCode,
                $output
            )
        );
        self::assertSame('survived', $output);
    }

    #[Group('slow')]
    public function testClearedLargeListIsDestroyedWithoutCrashingThePhpProcess(): void
    {
        [$exitCode, $output] = $this->runInSubprocess(
            '$list = SortedLinkedList::create(range(' . self::LARGE_LIST_SIZE . ', 1, -1), SortOrder::ascending());'
            . PHP_EOL . '$list->clear();'
            . PHP_EOL . 'unset($list);'
        );

        self::assertSame(
            0,
            $exitCode,
            'clear() must unlink nodes so the chain can be freed iteratively. Output: ' . $output
        );
        self::assertSame('survived', $output);
    }

    public function testPublicApiDoesNotExposeInternalNodes(): void
    {
        $leaking = [];
        $reflection = new \ReflectionClass(SortedLinkedList::class);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $type = $method->getReturnType();
            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            if (is_a($type->getName(), Node::class, true)) {
                $leaking[] = $method->getName();
            }
        }

        self::assertSame([], $leaking, 'Internal nodes must not escape the list.');
    }

    public function testInsertDuringForeachTerminates(): void
    {
        $list = SortedLinkedList::create([1], SortOrder::ascending());
        $cap = 100;
        $visits = 0;

        foreach ($list as $value) {
            $visits++;
            if ($visits > $cap) {
                break;
            }

            $list->insert($value);
        }

        self::assertLessThanOrEqual(
            $cap,
            $visits,
            'foreach over a one-element list was extended indefinitely by insert(); '
            . 'either snapshot the iteration or reject structural inserts mid-iteration.'
        );
    }

    public function testMergeAcceptsAnyTypeCompatibleImplementation(): void
    {
        $left = SortedLinkedList::create([1, 3], SortOrder::ascending());

        $merged = $left->merge($this->foreignIntList());

        self::assertSame([1, 2, 3, 4], $merged->toArray());
        self::assertSame([1, 3], $left->toArray(), 'merge() must not mutate the receiver.');
    }

    public function testLookupsHonourTheConfiguredComparator(): void
    {
        $order = SortOrder::custom(
            static fn (int|string $a, int|string $b): int => strcasecmp((string) $a, (string) $b)
        );
        $list = SortedLinkedList::create(['a', 'b'], $order);

        self::assertTrue($list->contains('A'), 'the comparator ranks "A" and "a" as equal');
        self::assertSame(1, $list->countOccurrences('A'));
        self::assertTrue($list->remove('A'));
        self::assertSame(['b'], $list->toArray());
    }

    public function testIteratorKeyMatchesListIndex(): void
    {
        $list = SortedLinkedList::create([1, 2, 3], SortOrder::ascending());
        $observed = [];

        foreach ($list as $key => $value) {
            if ($value === 1) {
                $list->remove(1);
                continue;
            }

            $observed[$key] = $value;
        }

        self::assertSame([0 => 2, 1 => 3], $observed);
    }

    /**
     * @param string $body
     * @return array{0: int, 1: string}
     */
    private function runInSubprocess(string $body): array
    {
        $script = <<<PHP
        <?php

        declare(strict_types=1);

        require %s;

        use SimpleSortedLinkedList\LinkedList\SortOrder;
        use SimpleSortedLinkedList\LinkedList\SortedLinkedList;

        %s

        echo 'survived';
        PHP;

        $file = tempnam(sys_get_temp_dir(), 'ssll_') . '.php';
        file_put_contents(
            $file,
            sprintf($script, var_export(dirname(__DIR__, 3) . '/vendor/autoload.php', true), $body)
        );

        try {
            $output = [];
            $exitCode = 0;
            exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);

            return [$exitCode, trim(implode(PHP_EOL, $output))];
        } finally {
            unlink($file);
        }
    }

    /**
     * @return SortedLinkedListInterface<int, int|string>
     */
    private function foreignIntList(): SortedLinkedListInterface
    {
        return new class implements SortedLinkedListInterface {
            /** @var list<int|string> */
            private array $items;

            public function __construct()
            {
                $this->items = [2, 4];
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->items);
            }

            public static function create(iterable $items, SortOrder $order): self
            {
                return new self();
            }

            public function insert(int|string $item): void
            {
            }

            public function remove(int|string $item): bool
            {
                return false;
            }

            public function removeAll(int|string $item): bool
            {
                return false;
            }

            public function first(): int|string
            {
                $first = $this->items[0];
                return $first;
            }

            public function last(): int|string
            {
                $last = $this->items[count($this->items) - 1];
                return $last;
            }

            /** @return list<int|string> */
            public function toArray(): array
            {
                return $this->items;
            }

            public function at(int $index): int|string
            {
                $value = $this->items[$index] ?? throw new \OutOfBoundsException();
                return $value;
            }

            public function contains(int|string $item): bool
            {
                return in_array($item, $this->items, true);
            }

            public function countOccurrences(int|string $item): int
            {
                return count(array_keys($this->items, $item, true));
            }

            public function clear(): void
            {
                $this->items = [];
            }

            public function count(): int
            {
                return count($this->items);
            }

            public function copy(): self
            {
                return new self();
            }

            public function filter(callable $callback): self
            {
                return new self();
            }

            public function merge(SortedLinkedListInterface $other): SortedLinkedListInterface
            {
                return new self();
            }
        };
    }
}
