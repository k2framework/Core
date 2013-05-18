<?php

namespace K2\Datamapper;

use K2\Datamapper\MapperBuilder;

interface MapperInterface
{

    public function map(MapperBuilder $builder, array $options = array());
}
