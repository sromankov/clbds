<?php

namespace Classes\Collection;

use Classes\Model\Entity;

/**
 * Class EntitiesCollection
 *
 * Extends base collection class to work with data source features
 *
 * @package Classes\Collection
 */
class EntitiesCollection extends Collection
{
    /**
     * Related database table name
     * @var string
     */
    protected $table;

    /**
     * EntitiesCollection constructor.
     *
     * @param array $items
     * @param null $table
     */
    public function __construct($items = [], $table = null)
    {
        $this->table = $table;
        $this->items = [];

        foreach ($items as $key => $item) {

            $entity = null;

            if (is_array($item)) {
                $entity = new Entity($table, $item);
            }
            if ($item instanceof Entity) {
                $entity = $item;
            }

            if ($entity) {
                $this->items[$key] = $entity;
            }

        }
    }

    /**
     * Stores the current collection state into linked database table
     *
     * @return $this
     */
    public function apply()
    {
        foreach ($this->items as $item) {

            /**
             * @var $item Entity
             */
            if ($item->isNew()) {

                $item->store();
            } else {

                // TODO work with the collection of real elements stored in database was not tested absolutely
                $data = $item->toArray();
                $item->update($data);
            }
        }

        return $this;
    }

    /**
     * Deletes linked to collection database table records
     *
     * @return $this
     */
    public function drop()
    {
        foreach ($this->items as $item) {
            /**
             * @var $item Entity
             */
            if (!$item->isNew()) {

                $item->delete();

                // TODO remove item from collection as well
            }
        }

        return $this;
    }

    /**
     * Returns array representation of the collection
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];

        /**
         * @var $item Entity
         */
        foreach ($this->items as $item) {
            $result [] = $item->toArray();
        }

        return $result;
    }

    /**
     * Returns collection element searched by record ID
     *
     * @param $id
     * @return bool|mixed
     */
    public function findById($id)
    {
        foreach ($this->items as $key => $item) {
            if (isset($item['id']) && $id === $item['id']) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Returns collection with specified element excluded
     *
     * @param $id
     * @return EntitiesCollection
     */
    public function excludeById($id)
    {
        $items = [];

        foreach ($this->items as $item) {

            /**
             * @var $item Entity
             */
            if (isset($item->toArray()['id']) && $id != $item->toArray()['id']) {
                $items[] = $item;
            }
        }

        return new EntitiesCollection($items);
    }

    /**
     * Clones collection
     *
     * @return EntitiesCollection static
     */
    public function makeClone()
    {
        $new = array();

        foreach ($this->items as $k => $v) {
            $new[$k] = clone $v;
        }

        return new static($new, $this->table);
    }
}
