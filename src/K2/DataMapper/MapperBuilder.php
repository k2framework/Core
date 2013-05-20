<?php

namespace K2\Datamapper;

use K2\Datamapper\Exception\InvalidArgumentException;

/**
 * Esta clase creará los mapeos
 *
 * @author manuel
 */
class MapperBuilder
{

    protected $items = array();

    public function add($item, array $filters = null)
    {
        $item = $this->resolveItem($item);

        $item->setFilters((array) $filters);

        $this->items[$item->getKey()] = $item;

        return $this;
    }

    /**
     * 
     * @param Item $item
     */
    protected function resolveItem($item)
    {
        if ($item instanceof Item) {
            return $item;
        }

        if (!is_string($item)) {
            throw new InvalidArgumentException('El valor para el atributo $item debe ser una instancia de Item ó un string');
        }

        return new Item($item);
    }

    public function getItems()
    {
        return $this->items;
    }

}

