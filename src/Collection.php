<?php

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection.
     *
     * @param  mixed $items
     *
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = is_array($items) ? $items : $this->getArrayableItems($items);
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed $items
     *
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if ($items instanceof self) {
            return $items->all();
        }

        return (array) $items;
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  mixed $items
     *
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Collapse the collection of items into a single array.
     *
     * @return static
     */
    public function collapse()
    {
        $results = [];

        foreach ($this->items as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            }
            $results = array_merge($results, $values);
        }

        return new static($results);
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function contains($key, $value = null)
    {
        if (func_num_args() == 2) {
            return $this->contains(function ($k, $item) use ($key, $value) {
                return static::dataGet($item, $key) == $value;
            });
        }
        if ($this->useAsCallable($key)) {
            return ! is_null($this->first($key));
        }

        return in_array($key, $this->items);
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @param  mixed        $default
     *
     * @return mixed
     */
    protected function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        $key = is_array($key) ? $key : explode('.', $key);
        foreach ($key as $segment) {
            if (is_array($target)) {
                if ( ! array_key_exists($segment, $target)) {
                    return static::value($default);
                }
                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if ( ! isset($target[$segment])) {
                    return static::value($default);
                }
                $target = $target[$segment];
            } elseif (is_object($target)) {
                if ( ! isset($target->{$segment})) {
                    return static::value($default);
                }
                $target = $target->{$segment};
            } else {
                return static::value($default);
            }
        }

        return $target;
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    protected function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed $value
     *
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Get the first item from the collection.
     *
     * @param  callable|null $callback
     * @param  mixed         $default
     *
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return count($this->items) > 0 ? reset($this->items) : null;
        }

        foreach ($this->items as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return static::value($default);
    }

    /**
     * Diff the collection with the given items.
     *
     * @param  mixed $items
     *
     * @return static
     */
    public function diff($items)
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Execute a callback over each item.
     *
     * @param  callable $callback
     *
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Create a new collection consisting of every n-th element.
     *
     * @param  int $step
     * @param  int $offset
     *
     * @return static
     */
    public function every($step, $offset = 0)
    {
        $new      = [];
        $position = 0;
        foreach ($this->items as $key => $item) {
            if ($position % $step === $offset) {
                $new[] = $item;
            }
            $position++;
        }

        return new static($new);
    }

    /**
     * Fetch a nested element of the collection.
     *
     * @param  string $key
     *
     * @return static
     *
     * @deprecated since version 5.1. Use pluck instead.
     */
    public function fetch($key)
    {
        foreach (explode('.', $key) as $segment) {
            $results = [];

            foreach ($this->items as $value) {
                if (array_key_exists($segment, $value = (array) $value)) {
                    $results[] = $value[$segment];
                }
            }

            $this->items = array_values($results);
        }

        return;

        return new static(array_values($results));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  bool   $strict
     *
     * @return static
     */
    public function whereNot($key, $value, $strict = true)
    {
        return $this->filter(function ($item) use ($key, $value, $strict) {
            return $strict ? static::dataGet($item, $key) !== $value
                : static::dataGet($item, $key) != $value;
        });
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null $callback
     *
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Filter items by the given key value pair using loose comparison.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return static
     */
    public function whereLoose($key, $value)
    {
        return $this->where($key, $value, false);
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  bool   $strict
     *
     * @return static
     */
    public function where($key, $value, $strict = true)
    {
        return $this->filter(function ($item) use ($key, $value, $strict) {
            return $strict ? static::dataGet($item, $key) === $value
                : static::dataGet($item, $key) == $value;
        });
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @return static
     */
    public function flatten()
    {
        $return = [];

        array_walk_recursive($this->items, function ($x) use (&$return) {
            $return[] = $x;
        });

        return new static($this->items);
    }

    /**
     * Flip the items in the collection.
     *
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param  callable|string $groupBy
     * @param  bool            $preserveKeys
     *
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        $groupBy = $this->valueRetriever($groupBy);
        $results = [];
        foreach ($this->items as $key => $value) {
            $groupKey = $groupBy($value, $key);
            if ( ! array_key_exists($groupKey, $results)) {
                $results[$groupKey] = new static;
            }
            $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
        }

        return new static($results);
    }

    /**
     * Get a value retrieving callback.
     *
     * @param  string $value
     *
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return static::dataGet($item, $value);
        };
    }

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  callable|string $keyBy
     *
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy   = $this->valueRetriever($keyBy);
        $results = [];
        foreach ($this->items as $item) {
            $results[$keyBy($item)] = $item;
        }

        return new static($results);
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Concatenate values of a given key as a string.
     *
     * @param  string $value
     * @param  string $glue
     *
     * @return string
     */
    public function implode($value, $glue = null)
    {
        $first = $this->first();
        if (is_array($first) || is_object($first)) {
            return implode($glue, $this->pluck($value)->all());
        }

        return implode($value, $this->items);
    }

    /**
     * Get an array with the values of a given key.
     *
     * @param  string $value
     * @param  string $key
     *
     * @return static
     */
    public function pluck($value, $key = null)
    {
        $results = [];
        list($value, $key) = static::explodePluckParameters($value, $key);
        foreach ($this->items as $item) {
            $itemValue = static::dataGet($item, $value);
            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey           = static::dataGet($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return new static($results);
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param  string|array      $value
     * @param  string|array|null $key
     *
     * @return array
     */
    protected static function explodePluckParameters($value, $key)
    {
        $value = is_array($value) ? $value : explode('.', $value);
        $key   = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param  mixed $items
     *
     * @return static
     */
    public function intersect($items)
    {
        return new static(array_intersect($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * Get the last item from the collection.
     *
     * @param  callable|null $callback
     * @param  mixed         $default
     *
     * @return mixed
     */
    public function last(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return count($this->items) > 0 ? end($this->items) : static::value($default);
        }

        $this->items = array_reverse($this->items);

        return static::first($callback, $default);
    }

    /**
     * Alias for the "pluck" method.
     *
     * @param  string $value
     * @param  string $key
     *
     * @return static
     */
    public function lists($value, $key = null)
    {
        return $this->pluck($value, $key);
    }

    /**
     * Get the max value of a given key.
     *
     * @param  string|null $key
     *
     * @return mixed
     */
    public function max($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = static::dataGet($item, $key);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable $callback
     * @param  mixed    $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  mixed $items
     *
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the min value of a given key.
     *
     * @param  string|null $key
     *
     * @return mixed
     */
    public function min($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = static::dataGet($item, $key);

            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    /**
     * "Paginate" the collection by slicing it into a smaller collection.
     *
     * @param  int $page
     * @param  int $perPage
     *
     * @return static
     */
    public function forPage($page, $perPage)
    {
        return $this->slice(($page - 1) * $perPage, $perPage);
    }

    /**
     * Slice the underlying collection array.
     *
     * @param  int  $offset
     * @param  int  $length
     * @param  bool $preserveKeys
     *
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param  mixed $value
     *
     * @return $this
     */
    public function prepend($value)
    {
        array_unshift($this->items, $value);

        return $this;
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param  mixed $value
     *
     * @return $this
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);

        return $this;
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Pulls an item from the collection.
     *
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        $value = static::get($key, $default);

        static::forget($key);

        return $value;
    }

    /**
     * Get an item from the collection by key.
     *
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return static::value($default);
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param  mixed $key
     *
     * @return $this
     */
    public function forget($key)
    {
        $this->offsetUnset($key);

        return $this;
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Put an item in the collection by key.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return $this
     */
    public function put($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Get one or more items randomly from the collection.
     *
     * @param  int $amount
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($amount = 1)
    {
        if ($amount > ($count = $this->count())) {
            throw new InvalidArgumentException("You requested {$amount} items, but there are only {$count} items in the collection");
        }
        $keys = array_rand($this->items, $amount);
        if ($amount == 1) {
            return $this->items[$keys];
        }

        return new static(array_intersect_key($this->items, array_flip($keys)));
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param  mixed $value
     * @param  bool  $strict
     *
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        if ( ! $this->useAsCallable($value)) {
            return array_search($value, $this->items, $strict);
        }
        foreach ($this->items as $key => $item) {
            if (call_user_func($value, $item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get and remove the first item from the collection.
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @return static
     */
    public function shuffle()
    {
        $items = $this->items;
        shuffle($items);

        return new static($items);
    }

    /**
     * Chunk the underlying collection array.
     *
     * @param  int  $size
     * @param  bool $preserveKeys
     *
     * @return static
     */
    public function chunk($size, $preserveKeys = false)
    {
        $chunks = [];
        foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Sort through each item with a callback.
     *
     * @param  callable|null $callback
     *
     * @return static
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;
        $callback ? uasort($items, $callback) : uasort($items, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return new static($items);
    }

    /**
     * Sort the collection in descending order using the given callback.
     *
     * @param  callable|string $callback
     * @param  int             $options
     *
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param  callable|string $callback
     * @param  int             $options
     * @param  bool            $descending
     *
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results  = [];
        $callback = $this->valueRetriever($callback);
        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }
        $descending ? arsort($results, $options)
            : asort($results, $options);
        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param  int      $offset
     * @param  int|null $length
     * @param  mixed    $replacement
     *
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() == 1) {
            return new static(array_splice($this->items, $offset));
        }

        return new static(array_splice($this->items, $offset, $length, $replacement));
    }

    /**
     * Get the sum of the given values.
     *
     * @param  callable|string|null $callback
     *
     * @return mixed
     */
    public function sum($callback = null)
    {
        if (is_null($callback)) {
            return array_sum($this->items);
        }
        $callback = $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result += $callback($item);
        }, 0);
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param  int $limit
     *
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Transform each item in the collection using a callback.
     *
     * @param  callable $callback
     *
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->items = $this->map($callback)->all();

        return $this;
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable $callback
     *
     * @return static
     */
    public function map(callable $callback)
    {
        $keys  = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Return only unique items from the collection array.
     *
     * @param  string|callable|null $key
     *
     * @return static
     */
    public function unique($key = null)
    {
        if (is_null($key)) {
            return new static(array_unique($this->items, SORT_REGULAR));
        }
        $key    = $this->valueRetriever($key);
        $exists = [];

        return $this->reject(function ($item) use ($key, &$exists) {
            if (in_array($id = $key($item), $exists)) {
                return true;
            }
            $exists[] = $id;
        });
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param  callable|mixed $callback
     *
     * @return static
     */
    public function reject($callback)
    {
        if ($this->useAsCallable($callback)) {
            return $this->filter(function ($item) use ($callback) {
                return ! $callback($item);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return static
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
     *      => [[1, 4], [2, 5], [3, 6]]
     *
     * @param  mixed ...$items
     *
     * @return static
     */
    public function zip($items)
    {
        $arrayableItems = array_map(function ($items) {
            return $this->getArrayableItems($items);
        }, func_get_args());
        $params         = array_merge([
            function () {
                return new static(func_get_args());
            },
            $this->items
        ], $arrayableItems);

        return new static(call_user_func_array('array_map', $params));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->items;
    }

    /**
     * Get a CachingIterator instance.
     *
     * @param  int $flags
     *
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}