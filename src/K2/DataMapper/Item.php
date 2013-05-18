<?php

namespace K2\Datamapper;

class Item
{

    protected $key;
    protected $filters = array();
    protected $validations = array();

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function getValidations()
    {
        return $this->validations;
    }

    public function setValidations($validations)
    {
        $this->validations = $validations;
    }

}
