<?php

namespace Neonus\DbLayer\Repository;

use Neonus\DbLayer\Entity\BaseEntity;
use Neonus\DbLayer\Lib\EntityFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Neonus\DbLayer\Interfaces\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    /** @var Connection */
    protected $db;

    /** @var EntityFactory */
    protected $factory;

    /**
     * BaseRepository constructor.
     * @param Connection $connection
     * @param EntityFactory $factory
     * @throws \Exception
     */
    public function __construct(Connection $connection, EntityFactory $factory)
    {
        $this->db = $connection;
        $this->factory = $factory;

        if (static::TABLE_NAME === '' || !defined('static::TABLE_NAME')) {
            throw new \Exception('Constant TABLE_NAME must be defined.');
        }
        if (static::ENTITY_NAME === '' || !defined('static::ENTITY_NAME')) {
            throw new \Exception('Constant ENTITY_NAME must be defined.');
        }
    }

    /**
     * @param int $id
     * @return BaseEntity|bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function get(int $id)
    {
        $stmt = $this->db->executeQuery('SELECT * FROM ' . $this->getTableName() . ' WHERE id = ?', [$id]);

        if ($data = $stmt->fetchAssociative()) {
            return $this->factory->create($this->getEntityName(), $data);
        } else {
            return false;
        }
    }

    /**
     * @param string $url
     * @return BaseEntity|bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByUrl(string $url)
    {
        $stmt = $this->db->executeQuery('SELECT * FROM ' . $this->getTableName() . ' WHERE url = ?', [$url]);
        if ($data = $stmt->fetchAssociative()) {
            return $this->factory->create($this->getEntityName(), $data);
        } else {
            return false;
        }
    }

    /**
     * Persist entity to database.
     *
     * @param BaseEntity $entity
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function persist(BaseEntity $entity, bool $forceInsert = false)
    {
        $data = $entity->toArray();
        $types = $this->getTypes($data);
        if ($entity->getId() && !$forceInsert) {
            $id = $data['id'];
            unset($data['id']);
            return $this->db->update($this->getTableName(), $data, ['id' => $id], $types);
        } else {
            // Remove ID from data if it's 0 (new entity)
            if (isset($data['id']) && $data['id'] == 0) {
                unset($data['id']);
                unset($types['id']);
            }

            $res = $this->db->insert($this->getTableName(), $data, $types);

            // Fill in entity ID, if exists
            if (!$entity->getId() && $id = $this->db->lastInsertId()) {
                $entity->setId($id);
            }
            return $res;
        }
    }

    /**
     * Delete all records from the table.
     *
     * @return int Number of deleted records
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteAll(): int
    {
        return $this->db->executeStatement('DELETE FROM ' . $this->getTableName());
    }

    public function getTypes(array $data)
    {
        $res = [];
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $res[$key] = ParameterType::BOOLEAN;
            }
        }
        return $res;
    }

    private function getEntityName(): string
    {
        return $this::ENTITY_NAME;
    }

    private function getTableName(): string
    {
        return $this::TABLE_NAME;
    }

    /**
     * Get the database connection
     */
    public function getDb(): Connection
    {
        return $this->db;
    }
}
