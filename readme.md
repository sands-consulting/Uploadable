# Uploader Trait for Laravel Models

This package is to simplify upload handling in a Laravel application. Should work for both Laravel 5 and Lumen.

Key features:
1. Simple to use
2. Extendable
3. Handles both single and multiple uploads

## Core Concepts

1. All uploads belongs to a record in the database
2. File upload handling, manipulation and saving are done by passing it through a set of filters in series (à la gulp).
3. You can create your own filters.

## Installation

1. Install it in your application: `composer require sands/uploadable`
2. Add `Sands\Uploadable\UploadableServiceProvider::class` into your `app/config/app.php` file, inside the `providers` array.
3. Publish the package's migration: `php artisan vendor:publish`.
4. Run `php artisan migrate` to create the `uploads` table.

## Usage

Use the `UploadableTrait` trait inside your `model`

```php
use Sands\Uploadable\UploadableTrait;
class Task extends Model
{
    use UploadableTrait;   
    ...
}
```

Add a `$uploadableConfig` array property inside your `model` file: 

```php 
protected $uploadableConfig = [
	// handle <input type="file" name="images"/> or <input type="file" name="images[]" multiple/>
    'images' => [
        'fix-image-orientation', // fixes the image orientation
        'save',                  // saves the image prefixed wth "original-"
        'resize-image:800',      // resize the image to 800px width, maintain aspect ratio
        'save:medium',           // saves the image prefixed with "medium-"
        'resize-image:400',      // resize the image to 400px width, maintain aspect ratio
        'save:small',            // saves the image prefixed with "small-"
        'thumbnail-image:140',   // creates a 140px x 140px thumbnail of the image, resized then center cropped
        'save:thumbnail',        // saves the image prefixed with "thumbnail-"
    ],
    // handle <input type="file" name="images[main]"/> use 'dot' notation
    'images.main' => [
        ...
    ],
];
``` 

In the model's boot method, listen for `saved` and `deleted` events to `attach` and `detach` files to the `model`:

```php
public static function boot()
{
    self::saved(function (Task $task) {
        $task->attachFiles();
    });
    self::deleted(function (Task $task) {
        $task->detachFiles();
    });
}
```

Whenever the model is saved, this package will look for any matching `uploadableConfig` with the request. If there are any, the uploaded file will be passed through the configured filter. Each t 

## Built-in Filters

### Fix Image Orientation Filter

Fixes the image orientation.

Filter name: `fix-image-orientation`

### Resize Image Filter

Manipulates the image size.

Filter name: `resize-image`

Filter arguments:

1. `number` `default: 800` Target width in pixels 
2. `number` `default: 800` Target height in pixels
3. `boolean` `default: true` Constrain the aspect ratio while resizing
4. `boolean` `default: false` Constrain upsize. Do not upsize the image while resizing

### Thumbnail Image Filter

Manipulates the image to be resized and center cropped.

Filter name: `thumbnail-image`

Filter arguments:

1. `number` `default: 140` Target width and height in pixels 
2. `string` `default: #000000` Fill with this color if there is empty area after cropping

### Save Filter

Saves the modified image into an obfuscated directory inside the `public/uploads` directory. Each time the `save` filter is triggered, a new entry will be inserted into the `uploads` table.

Filter name: `save`

Filter arguments:

1. `string` `default: original-` Prefixes the file name with this string.


## Getting Uploads Related to a Model

Relationship between a `model` and `uploads` are implemented with the simple Laravel's `morphMany` method. You can access the files simply by `$task->uploads`. This will return all files and their mutations related to a `model`.

To filter through the files related to a `model` you can simply do `$task->uploads()->where() ... `.

There is also a shorthand `files($type = null, $prefix = null)` method that takes `$type` argument where it filters the type of file uploaded (`images` in the above example) and `$prefix` argument where it will search for uploads with the prefix. This method will return an `Eloquent` query so you can further refine the search.

The `upload` object properties are as per below:

```json
{
     "id": 5,
     "uploadable_type": "App\\Task",
     "uploadable_id": "2",
     "type": "images",
     "name": "original-2016-03-12-121732-am.png",
     "mime_type": "image/png",
     "url": "/uploads/MjAxNjAz/YXBwdGFzazI,/original-2016-03-12-121732-am.png",
     "path": "/var/www/project/public/uploads/MjAxNjAz/YXBwdGFzazI,/original-2016-03-12-121732-am.png",
     "created_at": "2016-03-30 21:52:20",
     "updated_at": "2016-03-30 21:52:20",
}
```

Example for getting an uploaded image's URL:

```php
<ul>
@foreach($tasks as $task)
    <li>
    	<img src="{{$task->files(null, 'thumbnail-')->first()->url}}" alt="">
        {{$task->name}}
    </li>
@endforeach
</ul>
```

## Creating Your Own Filter

It is very easy to create your own filter:

1. Create a `class` that implements `Sands\Uploadable\FilterInterface`.
2. Register the filter: `app('uploadable')->registerFilter('filterName', 'filterClass')`.
3. There is no step 3.

For example, lets say that we want to rotate all uploaded images by a certain configurable degrees:

Create a `RotateImage` class:

```php
namespace App\Filters\RotateImage;
use Sands\Uploadable\FilterInterface;

class RotateImage implements FilterInterface
{
    public function process($type, $file, $model)
    {
        $image = app('image')->make($file->getPathname());
        $image->rotate($this->degrees);
        $image->save();
    }

    public function __construct($degrees = 0)
    {
        $this->degrees = $degrees;
    }
}
```

Register the filter:

```php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Filters\RotateImage;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        app('uploadable')->registerFilter('rotate-image', RotateImage::class);
    }
    ....
}
```

Use it in your model:

```php
use Sands\Uploadable\UploadableTrait;
class Task extends Model
{
    use UploadableTrait;
    protected $uploadableConfig = [
        'images' => [
            'rotate-image:63', // rotate the image by 63 degrees
            'save',            // save the image
        ],
    ];
    ...   
}
```

Thats it!

# Contributing

You are more than welcome to submit issues on bugs, feature requests or questions related to this project. Fork and send a pull request if you would want to contribute code. This project follows PSR-2 coding style.

# License

The MIT License (MIT)
Copyright (c) 2016 Sands Consulting Sdn. Bhd.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

 

