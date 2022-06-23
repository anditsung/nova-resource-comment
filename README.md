[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KUFF55R5MNSMA)

# A commenting field for laravel nova

## Screenshot
![screenshot 1](https://github.com/anditsung/nova-resource-comment/raw/main/screenshots/Screen%20Shot%202022-06-23%20at%2009.27.56.png)
![screenshot 2](https://github.com/anditsung/nova-resource-comment/raw/main/screenshots/Screen%20Shot%202022-06-23%20at%2010.43.48.png)

## Installation

You can install this package in a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require anditsung/nova-resource-comment
```

Next, we need to run migrations. Auto-discovery of this package's service provider helps with that!

```bash
php artisan migrate
```

And lastly, any model that you want to have comments needs the `Commentable` trait added to it.

```php
use Anditsung\NovaResourceComment\Traits\Commentable;

class Post extends Model
{
    use Commentable;
    
    // ...
}
```

If you would like to publish the config for this package, run:

```bash
php artisan vendor:publish --tag nova-comment
```

### Usage

Simply add the `Anditsung\NovaResourceComment\HasManyComment` field in your Nova resource:

```php
namespace App\Nova;

use KAnditsung\NovaResourceComment\HasManyComment;

class Post extends Resource
{
    // ...
    
    public function fields(Request $request)
    {
        return [
            // ...
            
            HasManyComment::make(),

            // ...
        ];
    }
}
```

Nova's default value for per page via relationship is 5. If you like yo set this to a different value, such as 25, use this method

```php
HasManyComment::make()
    ->maxComment(25),
```

Now you can comment from the detail view of any resource

### Sidebar Navigation

Occasionally you may want to hide comments from the sidebar. You can easily do this by setting the respective config value to false. Make sure to first publish the configs.

```php
'available-for-navigation' => false
```

## Credits

* [Nova Comments](https://github.com/kirschbaum-development/nova-comments), original package using resource tool
