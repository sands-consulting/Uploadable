<?php

namespace Sands\Uploadable;

interface FilterInterface
{
    public function process($type, $file, $model);
}
