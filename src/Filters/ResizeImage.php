<?php

namespace Sands\Uploadable\Filters;

use Sands\Uploadable\FilterInterface;

class ResizeImage implements FilterInterface
{

    public function process($type, $file, $model)
    {
        $config = $this->config;

        $image = app('image')->make($file->getPathname());

        $image->resize($config['width'], $config['height'], function ($constraint) use ($config) {
            if ($config['maintainAspectRatio'] != 'false') {
                $constraint->aspectRatio();
            }
            if ($config['doNotUpsize'] == 'true') {
                $constraint->upsize();
            }
        });

        $image->save();
    }

    public function __construct($width = 800, $height = 800, $maintainAspectRatio = true, $doNotUpsize = false)
    {
        $this->config = [
            'width' => $width,
            'height' => $height,
            'maintainAspectRatio' => $maintainAspectRatio,
            'doNotUpsize' => $doNotUpsize,
        ];
    }

}
