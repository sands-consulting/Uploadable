<?php

namespace Sands\Uploadable;

use ReflectionClass;
use Sands\Uploadable\Exceptions\IncorrectFilterInterfaceException;
use Sands\Uploadable\Exceptions\UnknownFilterException;
use Sands\Uploadable\FilterInterface;

class Uploadable
{
    protected $filters = [];

    public function registerFilter($name, $classPath)
    {
        $this->filters[$name] = $classPath;
    }

    public function getFilter($configString = '')
    {
        $configParts = explode(':', $configString);

        // Get filter name
        $filterName = array_shift($configParts);

        // If filterName is false or filter does not exists, throw exception
        if (!$filterName || !isset($this->filters[$filterName])) {
            throw new UnknownFilterException("Filter '{$filterName}' does not exists", 1);
        }

        // Build filter arguments
        $arguments = [];
        $configParameterString = array_shift($configParts);
        if ($configParameterString) {
            $arguments = explode(',', $configParameterString);
        }

        // Return the new instantiated filter
        $filter = new ReflectionClass($this->filters[$filterName]);
        $filter = $filter->newInstanceArgs($arguments);
        if (!$filter instanceof FilterInterface) {
            throw new IncorrectFilterInterfaceException("Filter '{$this->filters[$filterName]}' must implement " . FilterInterface::class);
        }
        return $filter;

    }
}
