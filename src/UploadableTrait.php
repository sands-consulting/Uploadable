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

    public function getPath($type)
    {
        $base = public_path('uploads') . DIRECTORY_SEPARATOR . str_slug(get_class($this)) . DIRECTORY_SEPARATOR . $this->getKey();
        if (!file_exists($base)) {
            mkdir($base, 0777, true);
        }
        return $base;
    }

    public function getUrl($type)
    {
        return 'uploads/' . str_slug(get_class($this)) . '/' . $this->getKey();
    }

    protected function processFile($type, $file, $filters)
    {
        foreach ($filters as $config) {
            $filter = app('uploadable')->getFilter($config);
            try {
                $filter->process($type, $file, $this);
            } catch (Exception $e) {
                dd($filter);
            }
        }
    }

    public function attachFiles()
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

    public function detachFiles()
    {
        $this->uploads->each(function ($model) {
            $model->delete();
        });
    }
}
