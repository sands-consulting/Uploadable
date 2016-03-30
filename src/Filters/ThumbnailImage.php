<?php

namespace Sands\Uploadable\Filters;

use Sands\Uploadable\FilterInterface;

class ThumbnailImage implements FilterInterface
{

    public function process($type, $file, $model)
    {
        $image = app('image')->make($file->getPathname());

        if ($image->height() > $image->width()) {
            $image->resize($this->size, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $image->resize(null, $this->size, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        $image->resizeCanvas($this->size, $this->size, 'center', false, $this->color);

        $image->save();
    }

    public function __construct($size = 140, $color = '#000000')
    {
        $this->size = $size;
        $this->color = $color;
    }

}
