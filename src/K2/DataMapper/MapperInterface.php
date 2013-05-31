<?php

namespace K2\DataMapper;

use K2\DataMapper\MapperBuilder;

interface MapperInterface
{

    public function map(MapperBuilder $builder, array $options = array());
}
