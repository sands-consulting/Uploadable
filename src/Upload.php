<?php

namespace Sands\Uploadable;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{

    public static function boot()
    {
        self::deleted(function (Upload $upload) {

            // remove file
            unlink($upload->path);

            // remove directory
            if (count(glob(dirname($upload->path) . '/*')) === 0) {
                rmdir(dirname($upload->path));
            }
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
