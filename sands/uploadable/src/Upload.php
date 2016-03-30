<?php

namespace Sands\Uploadable;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{

    public static function boot()
    {
        self::deleted(function (Upload $upload) {
            unlink($upload->path);
        });
    }

    public function uploadable()
    {
        return $this->morphTo();
    }

    protected $fillable = [
        'uploadable_type',
        'uploadable_id',
        'type',
        'name',
        'mime_type',
        'url',
        'path',
    ];
}
