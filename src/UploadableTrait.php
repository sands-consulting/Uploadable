<?php

namespace Sands\Uploadable;

trait UploadableTrait
{
    public function files($type = null, $prefix = null)
    {
        $query = $this->uploads();
        if ($type !== null) {
            $query->where('type', $type);
        }
        if ($prefix !== null) {
            $query->where('name', 'like', "{$prefix}%");
        }
        return $query;
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    protected function getHashedPath($separator = '/')
    {
        return strtr(base64_encode(date('Ym')), '+/=', '-_,') . $separator . strtr(base64_encode(str_slug(get_class($this)) . $this->getKey()), '+/=', '-_,');
    }

    public function getPath($type)
    {
        $base = public_path('uploads') . DIRECTORY_SEPARATOR . $this->getHashedPath(DIRECTORY_SEPARATOR);
        if (!file_exists($base)) {
            mkdir($base, 0777, true);
        }
        return $base;
    }

    public function getUrl($type)
    {
        return '/uploads/' . $this->getHashedPath();
    }

    protected function processFile($type, $file, $filters)
    {
        foreach ($filters as $config) {
            $filter = app('uploadable')->getFilter($config);
            $filter->process($type, $file, $this);
        }
    }

    protected function attachFiles()
    {
        foreach ($this->uploadableConfig as $type => $filters) {
            $request = app('request');
            if ($request->hasFile($type)) {
                $files = $request->file($type);
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $this->processFile($type, $file, $filters);
                    }
                }
            }
        }
    }

    protected function detachFiles()
    {
        $this->uploads->each(function ($model) {
            $model->delete();
        });
    }
}
