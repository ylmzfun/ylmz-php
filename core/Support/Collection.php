<?php

namespace Ylmz\Support;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Countable;
use JsonSerializable;

class Collection implements IteratorAggregate, Countable, JsonSerializable
{
    public function __construct(private array $items = []) {}

    public function all(): array { return $this->items; }

    public function count(): int { return count($this->items); }

    /** Map over each item. */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /** Filter items by callback. */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /** Reduce items to a single value. */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /** Pluck a single column. */
    public function pluck(string $value, ?string $key = null): self
    {
        $result = [];
        foreach ($this->items as $item) {
            $v = is_array($item) ? ($item[$value] ?? null) : ($item->$value ?? null);
            if ($key !== null) {
                $k = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
                $result[$k] = $v;
            } else {
                $result[] = $v;
            }
        }
        return new self($result);
    }

    /** Get first item or default. */
    public function first(mixed $default = null): mixed
    {
        foreach ($this->items as $item) return $item;
        return $default;
    }

    /** Get last item or default. */
    public function last(mixed $default = null): mixed
    {
        return !empty($this->items) ? end($this->items) : $default;
    }

    /** Get value by key, dot notation supported. */
    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (is_array($data) && array_key_exists($segment, $data)) {
                $data = $data[$segment];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /** Check if key exists. */
    public function has(string $key): bool
    {
        return $this->get($key, new \stdClass()) !== new \stdClass();
    }

    /** Merge another array/collection. */
    public function merge(array|self $items): self
    {
        return new self(array_merge($this->items, is_array($items) ? $items : $items->all()));
    }

    /** Sort items by callback. */
    public function sort(callable $callback): self
    {
        $items = $this->items;
        uasort($items, $callback);
        return new self($items);
    }

    /** Get unique values by key. */
    public function unique(?string $key = null): self
    {
        if ($key === null) {
            return new self(array_unique($this->items));
        }
        $seen = [];
        $result = [];
        foreach ($this->items as $item) {
            $v = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            if (!in_array($v, $seen, true)) {
                $seen[] = $v;
                $result[] = $item;
            }
        }
        return new self($result);
    }

    /** Group items by key. */
    public function groupBy(string $key): self
    {
        $result = [];
        foreach ($this->items as $item) {
            $k = is_array($item) ? ($item[$key] ?? null) : ($item->$key ?? null);
            $result[$k][] = $item;
        }
        return new self($result);
    }

    /** Take first N items. */
    public function take(int $limit): self
    {
        return new self(array_slice($this->items, 0, $limit));
    }

    /** Skip first N items. */
    public function skip(int $offset): self
    {
        return new self(array_slice($this->items, $offset));
    }

    /** Get array of values. */
    public function values(): self
    {
        return new self(array_values($this->items));
    }

    /** Get array of keys. */
    public function keys(): self
    {
        return new self(array_keys($this->items));
    }

    /** Check if empty. */
    public function isEmpty(): bool { return empty($this->items); }

    /** Check if not empty. */
    public function isNotEmpty(): bool { return !$this->isEmpty(); }

    /** Sum values by key. */
    public function sum(?string $key = null): float|int
    {
        if ($key === null) return array_sum($this->items);
        return $this->pluck($key)->sum();
    }

    /** Push item onto collection. */
    public function push(mixed $value): self
    {
        $this->items[] = $value;
        return $this;
    }

    /** Convert to array. */
    public function toArray(): array { return $this->items; }

    /** Convert to JSON. */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->items, $options);
    }

    /** Execute callback on each item. */
    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) break;
        }
        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }

    /** Static constructor. */
    public static function make(array $items = []): self
    {
        return new self($items);
    }
}
