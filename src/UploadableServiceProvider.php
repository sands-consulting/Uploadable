<?php

namespace Sands\Uploadable;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageServiceProvider;

class UploadableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->image->boot();
        $this->publishes([
            __DIR__ . '/../publishes/migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->image->register();
        $this->app->singleton('uploadable', function () {
            $uploadable = new Uploadable();
            $uploadable->registerFilter('fix-image-orientation', Filters\FixImageOrientation::class);
            $uploadable->registerFilter('resize-image', Filters\ResizeImage::class);
            $uploadable->registerFilter('thumbnail-image', Filters\ThumbnailImage::class);
            $uploadable->registerFilter('save', Filters\Save::class);
            return $uploadable;
        });
    }

    public function __construct($app)
    {
        parent::__construct($app);
        $this->image = new ImageServiceProvider($app);
    }
}
