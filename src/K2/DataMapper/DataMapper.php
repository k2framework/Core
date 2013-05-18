<?php

namespace K2\Datamapper;

use K2\Datamapper\MapperInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;

class DataMapper
{

    /**
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccesor;

    function __construct(PropertyAccessorInterface $propertyAccesor)
    {
        $this->propertyAccesor = $propertyAccesor;
    }

    public function bind($object, $data)
    {
        if ($object instanceof MapperInterface) {

            $builder = new MapperBuilder(); //creo la instancia del builder
            $object->map($builder); //y la paso al objeto para obtener los mapeos

            foreach ($builder->getItems() as $key => $item) {
                if (isset($data[$key])) {
                    $this->setValue($object, $key, $this->resolve($item, $data[$key]));
                    unset($data[$key]);
                }
            }
        }

        foreach ($data as $key => $value) {
            $this->setValue($object, $key, $value);
        }
    }

    protected function setValue($object, $key, $value, $throw = false)
    {
        try {
            $this->propertyAccesor
                    ->setValue($object, $key, $value);
        } catch (RuntimeException $e) {
            //si no existe el indice, lo ignoramos
        }
    }

    protected function resolve($item, $value)
    {
        return $value;
    }

}

