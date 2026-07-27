# Simple Sorted Linked List

![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)

A type-safe sorted singly linked list for PHP 8.2+. Items are kept in sorted
order at all times, with stable scalar typing (a list of `int` stays a list
of `int`), a pluggable sort order, and an iterator that remains safe even when
the list is mutated during `foreach`.

---

## Features

- **Always sorted** — items are inserted in the correct position, not sorted
  on read
- **Stable scalar typing** — once the first item is inserted, the list accepts
  only the same scalar type (`int` or `string`); mixing types throws immediately
- **Ascending, descending, or custom order** — pass any comparator callable
- **Mutation-safe iteration** — you can `remove()`, `removeAll()`, or `clear()`
  inside a `foreach` without corrupting the iterator
- **Rich query API** — `first()`, `last()`, `at(int $index)`, `contains()`,
  `countOccurrences()`, `toArray()`
- **Functional helpers** — `copy()`, `filter()`, `merge()`
- **Zero runtime dependencies** — only `php: ^8.2`

---

## Installation

```bash
composer require d-skora/simple-sorted-linked-list
```

---

## Quick start

### Ascending list of integers

```php
use SimpleSortedLinkedList\LinkedList\SortedLinkedList;
use SimpleSortedLinkedList\LinkedList\SortOrder;

$list = SortedLinkedList::create([3, 1, 4, 1, 5, 9, 2], SortOrder::ascending());

$list->toArray(); // [1, 1, 2, 3, 4, 5, 9]
$list->first();   // 1
$list->last();    // 9
$list->at(2);     // 2
$list->count();   // 7
```

### Descending list of strings

```php
$list = SortedLinkedList::create(['banana', 'apple', 'cherry'], SortOrder::descending());

$list->toArray(); // ['cherry', 'banana', 'apple']
```

### Custom comparator

```php
// Sort integers by absolute value, ascending
$order = SortOrder::custom(static fn (int|string $a, int|string $b): int => abs((int)$a) <=> abs((int)$b));

$list = SortedLinkedList::create([-3, 1, -2], $order);
$list->toArray(); // [1, -2, -3]
```

### Factory

```php
use SimpleSortedLinkedList\LinkedList\SortedLinkedListFactory;

$list = SortedLinkedListFactory::create([5, 3, 1], SortOrder::ascending());
```

### Inserting and removing

```php
$list = SortedLinkedList::create([2, 1, 1, 3], SortOrder::ascending());

$list->insert(2);           // [1, 1, 2, 2, 3]
$list->remove(2);           // [1, 1, 2, 3]       — first occurrence only
$list->removeAll(1);        // [2, 3]

$list->insert(1);           // [1, 2, 3]
$list->insert(1);           // [1, 1, 2, 3]
$list->remove(1);           // [1, 2, 3]          — first occurrence only
```

### Filter and merge

```php
$list = SortedLinkedList::create([1, 2, 3, 4, 5], SortOrder::ascending());

$evens = $list->filter(static fn (int|string $v): bool => (int)$v % 2 === 0);
$evens->toArray(); // [2, 4]

$other = SortedLinkedList::create([10, 20], SortOrder::ascending());
$merged = $list->merge($other);
$merged->toArray(); // [1, 2, 3, 4, 5, 10, 20]
```

### Safe mutation during foreach

```php
$list = SortedLinkedList::create([1, 2, 3, 4, 5], SortOrder::ascending());

foreach ($list as $value) {
    if ($value % 2 === 0) {
        $list->remove($value); // safe — iterator skips removed nodes
    }
}

$list->toArray(); // [1, 3, 5]
```

---

## API reference

| Method | Description |
|---|---|
| `SortedLinkedList::create(iterable, SortOrder)` | Static factory |
| `insert(int\|string)` | Insert preserving order |
| `remove(int\|string): bool` | Remove first occurrence |
| `removeAll(int\|string): bool` | Remove all occurrences |
| `first(): int\|string` | Head value (throws on empty) |
| `last(): int\|string` | Tail value (throws on empty) |
| `at(int): int\|string` | Value at zero-based index |
| `contains(int\|string): bool` | Membership check |
| `countOccurrences(int\|string): int` | Count of a specific value |
| `count(): int` | Total item count (`Countable`) |
| `toArray(): array` | Snapshot as array |
| `clear()` | Empty the list |
| `copy()` | Independent copy |
| `filter(callable): self` | New filtered list |
| `merge(SortedLinkedListInterface): self` | New merged list |
| `getIterator()` | `foreach`-compatible iterator |

### Sort orders

| Factory | Behaviour |
|---|---|
| `SortOrder::ascending()` | Natural ascending (integers by value, strings alphabetically) |
| `SortOrder::descending()` | Natural descending |
| `SortOrder::custom(callable)` | Comparator `fn(a, b): int` — same contract as `usort` |

### Exceptions

| Exception | Thrown when |
|---|---|
| `InvalidArgumentException` | Wrong scalar type inserted, or merging incompatible lists |
| `UnderflowException` | `first()` / `last()` on empty list, or `current()` on exhausted iterator |
| `OutOfBoundsException` | `at()` with out-of-range index |
| `RuntimeException` | Custom comparator returns a non-int, or comparator is not set |

---

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage (requires Xdebug)
XDEBUG_MODE=coverage composer test

# Static analysis (PHPStan level max + strict rules)
composer phpstan

# Check PSR-12 compliance
composer phpcs

# Auto-fix PSR-12 violations
composer phpcbf
```

---

## License

MIT © Daniel Skora
