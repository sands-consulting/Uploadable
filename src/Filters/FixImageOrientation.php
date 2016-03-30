<?php

namespace Sands\Uploadable\Filters;

use Sands\Uploadable\FilterInterface;

class FixImageOrientation implements FilterInterface
{

    public function process($type, $file, $model)
    {
        $image = app('image')->make($file->getPathname());
        $image->orientate();
        $image->save();
    }

}
