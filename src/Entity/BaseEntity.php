<?php

namespace Neonus\DbLayer\Entity;

use Neonus\DbLayer\Interfaces\HydratationInterface;
use DateTime;
use Exception;
use ReflectionClass;
use ReflectionProperty;
use StdClass;

abstract class BaseEntity extends StdClass implements HydratationInterface
{
    /** @var integer */
    protected int $id = 0;

    /** @var DateTime */
    protected DateTime $created_at;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }


    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     * @throws Exception
     */
    public function setCreatedAt(mixed $created_at): void
    {
        $this->created_at = $this->convertToDateTime($created_at);
    }

    /**
     * @param array $data
     * @return static
     */
    public function hydrate(array $data): static
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                // set property via setter
                $setterName = 'set' . str_replace('_', '', ucwords($key, '_'));
                if (method_exists($this, $setterName) && $value !== null) {
                    $this->{$setterName}($value);
                }
            } else {
                // set it as simple property without setter
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * Serialize Entity to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result     = array();
        $reflection = new ReflectionClass(get_class($this));

        // Process parent properties
        foreach ($reflection->getParentClass()->getProperties() as $property) {
            $this->serializeProperty($property, $result);
        }

        foreach ($reflection->getProperties() as $property) {
            $this->serializeProperty($property, $result);
        }
        return $result;
    }

    protected function serializeProperty(ReflectionProperty $property, array &$result): void
    {
        // ignore internals
        if (str_contains($property->getDocComment(), '@internal')) {
            return;
        }

        // set private properties accessible
        $property->setAccessible(true);

        // Check if property is initialized for typed properties
        if (! $property->isInitialized($this)) {
            return;
        }

        // If value is null, skip it
        $value = $property->getValue($this);
        if (null === $value) {
            return;
        }

        if (is_object($value) && 'DateTime' === get_class($value)) {
            $result[ $property->getName() ] = $value->format('Y-m-d H:i:s');
        } elseif ($property->getName() === 'roles') {
            $result[ $property->getName() ] = implode(',', $value);
        } elseif (is_object($value) && 'Symfony\Component\HttpFoundation\File\File' === get_class($value)) {
            $result[ $property->getName() ] = $value->getBasename();
        } else {
            $result[ $property->getName() ] = $value;
        }
    }


    /**
     * @param $time
     * @return DateTime
     * @throws Exception
     */
    protected function convertToDateTime($time): DateTime
    {
        if ($time instanceof DateTime) {
            return $time;
        } elseif (is_numeric($time)) {
            $datetime = new DateTime();
            return $datetime->setTimestamp($time);
        } elseif (is_object($time) && get_class($time) === 'DateTime') {
            return $time;
        } elseif (is_string($time)) {
            return new DateTime($time);
        } else {
            throw new Exception('Type not recognized. You must set CreatedAt either by string or DateTime.');
        }
    }
}
