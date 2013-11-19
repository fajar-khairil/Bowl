Bowl, Yet Another Dependency Injection Container (PHP5.4+)
==========================================================

- Manage multiple environment (production/development/test ...)
- Manage dependencies between objects
- Perform a Lazy instantiation
- Perform a Factory pattern
- No external files to configure dependencies
- You can avoid Singleton/Factory pattern from your classes

Requirements
------------

* PHP5.4+

Installation
------------

Create or update your composer.json and run `composer update`

``` json
{
    "require": {
        "kzykhys/bowl": "dev-master"
    }
}
```

Usage
-----

### Define parameters

``` php
$bowl = new \Bowl\Bowl();
$bowl['lang'] = 'en';
$bowl['debug'] = true;
```

### Define a shared service

``` php
$bowl = new \Bowl\Bowl();

$bowl->share('service_name', function () {
    return new stdClass();
});

var_dump($bowl->get('service_name') === $bowl->get('service_name')); // bool(true)
```

### Define a factory service

``` php
$bowl = new \Bowl\Bowl();

$bowl->factory('service_name', function () {
    return new stdClass();
});

var_dump($bowl->get('service_name') === $bowl->get('service_name')); // bool(false)
```

### Define a service depending on other services

``` php
$bowl = new \Bowl\Bowl();

$bowl->share('driver.mysql', function () {
    return new MysqlDriver();
});

$bowl->share('connection', function () {
    $c = new Connection();
    $c->setDriver($this->get('driver.mysql'));

    return $c;
});
```

### Using tags to manage collection of services

``` php
$bowl = new \Bowl\Bowl();

$bowl->share('form.type.text', function () {
    return new TextType();
}, ['form.type']);

$bowl->share('form.type.email', function () {
    return new EmailType();
}, ['form.type']);

$bowl->share('form', function () {
    $form = new Form();

    foreach ($this->getTaggedServices('form.type') as $service) {
        $form->addType($service);
    }

    return $form;
});
```

### Working with environment flag

``` php
use Bowl\Bowl;

$bowl = new Bowl();

// Common parameters
$bowl['lang'] = 'en';

// Production configuration
$bowl->configure('production', function (Bowl $bowl) {
    $bowl['debug'] = false;

    $bowl->share('orm.repository', function () {
        return new EntityRepository();
    });
});

// Development configuration
$bowl->configure('development', function (Bowl $bowl) {
    $bowl['debug'] = true;

    $bowl->share('orm.repository', function () {
        return new MockRepository();
    });
});

// Common services
$bowl->share('orm.manager', function () {
    return new OrmManager($this->get('orm.repository'));
});
$bowl->share('fixture.loader', function () {
    return new Loader($this->get('orm.manager'), $this['debug']);
});

// Set enviroment manually
$bowl->env('production');

// Or using system's environment variable
$bowl->env(getenv('APP_ENV') ? getenv('APP_ENV') : 'production');
```

### Real life example

``` php
<?php

require __DIR__ . '/../vendor/autoload.php';

$bowl = new \Bowl\Bowl();

// Set a parameter
$bowl['debug'] = false;

// Shared service
$bowl->share('ciconia.renderer', function () {
    return new \Ciconia\Renderer\HtmlRenderer();
});

// Tagged service
$bowl->share('ciconia.extension.table', function () {
    return new \Ciconia\Extension\Gfm\TableExtension();
}, ['ciconia.extension']);

// This example shows how to manage services using tags
$bowl->share('ciconia', function () {
    $ciconia = new \Ciconia\Ciconia();

    // $bowl is bind to this closure, so you can access $this as Bowl.
    if ($this['debug']) {
        $ciconia = new \Ciconia\Diagnose\Ciconia();
    }

    // Resolve dependencies
    $ciconia->setRenderer($this->get('ciconia.renderer'));

    // All services tagged as "ciconia.extension"
    foreach ($this->getTaggedServices('ciconia.extension') as $extension) {
        $ciconia->addExtension($extension);
    }

    return $ciconia;
});

// Get the object
$ciconia = $bowl->get('ciconia');
echo $ciconia->render('Markdown is *awesome*');

// Create a new instance even if this is a shared object
$ciconia = $bowl->reset('ciconia')->get('ciconia');
echo $ciconia->render('Markdown is *awesome*');
```

API
---

### Manage environment

#### configure(_string_ **$environment**, _\Closure_ **$closure**)

You can configure Bowl based on environment flags such as production and development.

``` php
// sample goes here
```

#### env(_string_ **$environment**)

``` php
// sample goes here
```

### Service container

#### share(_string_ **$name**, _\Closure_ **$closure**)

``` php
// sample goes here
```

#### factory(_string_ **$name**, _\Closure_ **$closure**)

``` php
// sample goes here
```

#### extend(_string_ **$name**, _\Closure_ **$closure**)

``` php
// sample goes here
```

#### get(_string_ **$name**)

``` php
// sample goes here
```

#### getTaggedServices(_string_ **$name**)

``` php
// sample goes here
```

Contributing
------------

Feel free to fork and send a pull request.

License
-------

The MIT License

Author
------

Kazuyuki Hayashi (@kzykhys)