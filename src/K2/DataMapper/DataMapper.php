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

    public function bind($object, $data, array $options = array())
    {
        if (is_string($data)) {//si data es un string, buscamos en el objeto Request
            $data = \K2\Kernel\App::getRequest()->request($data, array());
        }

        if ($object instanceof MapperInterface) {

            $builder = new MapperBuilder(); //creo la instancia del builder
            $object->map($builder); //y la paso al objeto para obtener los mapeos

            foreach ($builder->getItems() as $key => $item) {
                if (isset($data[$key])) {
                    $this->setValue($object, $key, $this->resolve($item, $data[$key]), $options);
                    unset($data[$key]);
                }
            }
        }

        //si no se define el indice strict, setea los demas
        //datos del arreglo data
        if (!isset($options['strict'])) {
            foreach ($data as $key => $value) {
                $this->setValue($object, $key, $value, $options);
            }
        }
    }

    public function bindPublic($object, $data, array $options = array())
    {
        $options['create_attributes'] = true;

        $this->bind($object, $data, $options);
    }

    protected function setValue($object, $key, $value, array $options = array(), $throw = false)
    {
        try {
            $this->propertyAccesor
                    ->setValue($object, $key, $value);
        } catch (RuntimeException $e) {
            //si no existe el indice, lo ignoramos
            if (isset($options['create_attributes'])) {
                $object->{$key} = $value;
            }
        }
    }

    protected function resolve(Item $item, $value)
    {
        $filters = $item->getFilters();

        foreach ($filters as $id => $filter) {
            $data = array($value);
            if (!is_int($filter)) {
                $data[] = $id;
            }
            $data[] = $filter;
            $value = call_user_func_array('filter_var', $data);
        }

        return $value;
    }

}

