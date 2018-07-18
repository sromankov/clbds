<?php

namespace Classes\Model;

use Classes\Collection\EntitiesCollection;
use Classes\Model\Connection\Connection;

/**
 * Class Model
 *
 * The base ORM implementation,
 * Unfortunately - very "base"
 *
 * @package Classes\Model
 */
abstract class Model
{
    /**
     * Database connector
     * @var Connection
     */
    protected $connection;
    /**
     * Database table name
     * @var string
     */
    protected $table;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance()->getConnection();
    }

    /**
     * Returns set of al entities
     *
     * @return EntitiesCollection
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY date_start ASC"; // TODO ORDER as method
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $recordsArray = [];
        foreach ($rows as $row) {

            $recordsArray[] = new Entity($this->table, $row);
        }

        return new EntitiesCollection($recordsArray, $this->table);
    }

    /**
     * Returns entity by id
     *
     * @param $id integer
     * @return Entity
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `id` = :id";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':id', $id);

        $statement->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            return new Entity($this->table, $row);
        }

        return $row;
    }

    /**
     * Creates new entity and saves it into database
     *
     * @param $data
     * @return Entity
     */
    public function create($data)
    {
        $row = new Entity($this->table, $data);
        $row->store();

        return $row;
    }

    /**
     * Table name getter
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

}
