<?php

namespace Classes\Collection;

/**
 * Class Collection
 *
 * Collection implementation (common class)
 *
 * @package Classes\Collection
 */
class Collection implements \IteratorAggregate
{
    /**
     * Collection elements
     * @var array
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * Insert new element into collection
     *
     * @param $element
     */
    public function add($element)
    {
        $this->items[] = $element;
    }

    /**
     * Returns collection item by its key
     *
     * @param $id
     * @return bool|mixed
     */
    public function find($id)
    {
        foreach ($this->items as $key => $item) {
            if ($id === $key) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Returns all collection elements
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Applies function to each collection element
     *
     * @param callable $callback
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
     * Returns whether collection has no elements
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Returns elements amount
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Adds the ability to iterate among collection items
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Returns array representation of the collection
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this->items;
    }
}
