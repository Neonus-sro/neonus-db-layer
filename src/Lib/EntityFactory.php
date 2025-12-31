<?php

namespace Neonus\DbLayer\Lib;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntityFactory
{
    /** @var ParameterBagInterface */
    private $params;

    /**
     * EntityFactory constructor.
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param string $entityName
     * @param $data
     * @param mixed ...$args
     */
    public function create(string $entityName, $data, ...$args)
    {
        $entity = new $entityName(...$args);

        // add params
        if (property_exists($entity, 'params')) {
            $entity->setParams($this->params);
        }

        return $entity->hydrate($data);
    }
}
