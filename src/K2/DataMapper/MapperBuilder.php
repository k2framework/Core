<?php

namespace K2\DataMapper;

use K2\DataMapper\Exception\InvalidArgumentException;

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

    public function sanitizeString($item)
    {
        return $this->add($item, array(FILTER_SANITIZE_STRING));
    }

    public function sanitizeInt($item, array $options = array(), $flags = null)
    {
        $options = array(
            'options' => $options,
            'flags' => $flags,
        );
        return $this->add($item, array(FILTER_SANITIZE_NUMBER_INT => $options));
    }

    public function sanitizeFloat($item, array $options = array(), $flags = FILTER_FLAG_ALLOW_FRACTION)
    {
        $options = array(
            'options' => $options,
            'flags' => $flags,
        );
        return $this->add($item, array(FILTER_SANITIZE_NUMBER_FLOAT => $options));
    }

    public function sanitizeEmail($item)
    {
        return $this->add($item, array(FILTER_SANITIZE_EMAIL));
    }

    public function sanitizeUrl($item)
    {
        return $this->add($item, array(FILTER_SANITIZE_URL));
    }

    public function callback($item, $callback)
    {
        return $this->add($item, array(FILTER_CALLBACK => array('options' => $callback)));
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

