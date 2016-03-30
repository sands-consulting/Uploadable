<?php

namespace Sands\Uploadable\Filters;

use Sands\Uploadable\FilterInterface;

class Save implements FilterInterface
{

    public function process($type, $file, $model)
    {
        $fileName = $this->type . str_slug(basename($file->getClientOriginalName(), $file->getClientOriginalExtension())) . '.' . $file->getClientOriginalExtension();
        $path = $model->getPath($type) . DIRECTORY_SEPARATOR . $fileName;
        $url = $model->getUrl($type) . '/' . $fileName;

        // copy the file
        copy($file->getPathname(), $path);

        // create db entry
        $model->uploads()->create([
            'type' => $type,
            'name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'url' => $url,
            'path' => $path,
        ]);
    }

    public function __construct($type = 'original-')
    {
        $this->type = $type ? $type . '-' : '';
    }
}
