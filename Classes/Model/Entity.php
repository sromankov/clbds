<?php

namespace Classes\Model;

use Classes\Model\Connection\Connection;

/**
 * Class Entity
 * @package Classes\Model
 */
class Entity
{
    /**
     * Database connector
     * @var Connection
     */
    protected $connection;
    /**
     * Database table
     * @var string
     */
    protected $table;
    /**
     * Entity attributes
     * @var array
     */
    protected $attributes = [];

    public function __construct($table, $attributes=[])
    {
        $this->connection = Connection::getInstance()->getConnection();
        $this->table = $table;
        $this->attributes = $attributes;
    }

    /**
     * Updates existing entity with new attributes set
     *
     * @param $data array
     * @return $this|null
     */
    public function update($data)
    {
        if (!isset($this->attributes['id']) || is_null($this->attributes['id'])) {

            return null;
        }

        if (isset($data['id']) && $this->attributes['id'] !=$data['id']) {

            return null;
        }

        $sql = "UPDATE {$this->table} SET {$this->buildUpdateAttributesQueryPart($data)} WHERE id=:id";
        $statement = $this->connection->prepare($sql);
        $statement->execute($data);

        return $this;
    }

    /**
     * Deletes entity from database table
     *
     * @return bool|null
     */
    public function delete()
    {
        if (!isset($this->attributes['id']) || is_null($this->attributes['id'])) {

            return null;
        }

        $sql = "DELETE FROM {$this->table} WHERE id=:id";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $this->attributes['id']);
        $statement->execute();

        return true;
    }

    /**
     * Stores new entity into database
     *
     * @return $this|null
     */
    public function store()
    {
        if (isset($this->attributes['id']) && !is_null($this->attributes['id'])) {
            return null;
        }

        unset($this->attributes['id']);

        $sql = "INSERT INTO {$this->table} {$this->buildInsertAttributesQueryPart($this->attributes)}";
        $statement = $this->connection->prepare($sql);
        $statement->execute($this->attributes);

        $this->attributes['id'] = $this->connection->lastInsertId();

        return $this;
    }

    /**
     * Builds "attribute=:value, ..." part of the UPDATE SQL statement
     *
     * @param $data array
     * @return string
     */
    protected function buildUpdateAttributesQueryPart($data)
    {
        $parts = [];
        foreach ($data as $key => $value) {
            if ($key != 'id') {
                $parts[] = "$key = :$key";
            }
        }

        return implode(", ", $parts);
    }

    /**
     * Builds "(....) VALUES (....)" part of the INSERT SQL statement
     *
     * @param $data array
     * @return string
     */
    protected function buildInsertAttributesQueryPart($data)
    {
        $names = [];
        $values = [];

        if (is_array(reset($data))) {
            // TODO mass insert query
        } else {

            foreach ($data as $key => $value) {
                if ($key != 'id') {
                    $names[] = "$key";
                }
            }

            foreach ($data as $key => $value) {
                if ($key != 'id') {
                    $values[] = ":$key";
                }
            }

            $line = "(" . implode(", ", $names) .") VALUES (" . implode(", ", $values) .")";
        }

        return $line;
    }

    /**
     * Returns whether entity is new (not saved)
     *
     * @return bool
     */
    public function isNew()
    {
        return !isset($this->attributes['id']);
    }

    /**
     * Returns entity attributes as assoc. array
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this->attributes;
    }
}
