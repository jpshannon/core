# werx.Core

## Core libraries for the [Werx Project](http://werx.github.io)

[![Build Status](https://travis-ci.org/werx/core.png?branch=master)](https://travis-ci.org/werx/core) [![Total Downloads](https://poser.pugx.org/werx/core/downloads.png)](https://packagist.org/packages/werx/core) [![Latest Stable Version](https://poser.pugx.org/werx/core/v/stable.png)](https://packagist.org/packages/werx/core)

- Routing - [Aura.Router](https://github.com/auraphp/Aura.Router)
- HTTP Abstraction - [Symfony\HttpFoundation](https://github.com/symfony/HttpFoundation)
- Templates - [Plates Native PHP Templates](http://platesphp.com/)
- Database Abstraction - [Illuminate\Database](https://github.com/illuminate/database)
- Configuration Management - [werx\Config](https://github.com/werx/config)
- Unit Tests - [PHPUnit](https://github.com/sebastianbergmann/phpunit) (of course, why use anything else?)

Learn more at [The Werx Project](http://werx.github.io) or see [werx\Skeleton](https://github.com/werx/skeleton) for a reference implementation.

## Installation
Installation of this package is easy with Composer. If you aren't familiar with the Composer Dependency Manager for PHP, [you should read this first](https://getcomposer.org/doc/00-intro.md).


``` json
{
	"require": {
        "werx/core": "dev-master"
    },
	"minimum-stability": "dev"
}
```
## Suggested Directory Structure

- Your application resides in `src/` and has a base namespace of `Example\Project`
    - Create console/web applications by extending `werx\Core\WerxApp` and `werx\Core\WerxWebApp`
- Your controllers are in `src/controllers/` and have a namespace of `Example\Project\Controllers`
	- Your controllers extend `werx\Core\Controller`
    - Your console controllers extend `werx\Core\Console`
    - Your api controllers extend `werx\Core\Api`

```
src/
	config/
		local/
			config.php
		test/
		prod/
		config.php
		routes.php
	controllers/
		Home.php
	models/
    moduals/
	views/
		home/
			index.php
		layouts/
			default.php
    ExampleProjectApp.php
vendor/
	Composer's installation directory
web/
	index.php
	composer.json
```

## Front Controller
Your `web/index.php` serves as the front controller and contains the following content:

``` php
<?php
# File: web/index.php

namespace Example\Project; // Change this to whatever base namespace you are using in your project.

$autoload_dir = dirname(__DIR__);

// Use Composer's autoloader.
require_once $autoload_dir . '/vendor/autoload.php';

$app = new ExampleProjectApp(); // \werx\Core\Dispatcher is an example of a minimal application
// optionaly customize modules: ex $app->addModule(new \werx\Core\Modules\AuraRoutes);
// Run the application.
$app->run();
```

This will run any the application, calling any configured module.

## Routing
Routing is handled by the [Aura Router](https://github.com/auraphp/Aura.Router).

A sensible default routes is provided out of the box.

``` php
$router->add('default, {/controller,action,id});
```

The following will all call the `index()` method of your `Home` controller.

>- http://example.com/path/to/index.php
>- http://example.com/path/to/index.php/home
>- http://example.com/path/to/index.php/home/index

Or you can choose not to expose script names (default)
``` php
<?php
# File: src/config/config.php
return ["expose_script_names" => false];
```

>- http://example.com/path/to/
>- http://example.com/path/to/home
>- http://example.com/path/to/home/index


This one will also call `index`, but will also pass the value "1" to the index method.

>- http://example.com/path/to/index.php/home/index/1

Likewise, you can call the `bar()` method of the `Foo` controller like this:

>- http://example.com/path/to/index.php/foo/bar

If you want to supply different routes, just create a `config` directory under source with a
file named `routes.php` that defines the routes you need.


```
src/
	config/
		routes.php
```

For more information on routing options, see <https://github.com/auraphp/Aura.Router>.

## Configuration
The configuration manger is built on [werx\Config](https://github.com/werx/config)

### Basic Configuration Usage

``` php
// Load our primary config file.
$this->config->load('config'); // preloaded by default, so not really necessary

$something = $this->config->get('something');

// You can load any configuration file...
$this->config->load('database');

// Or you can get it from the main config.php and forget ever calling load again
$this->config->get('database');
```
``` php
<?php
# File: src/config/config.php
return ["database" => "#database"];
```

### Multiple Environment Configuration Support

The configuration manager supports different configs for different environments (local, dev, test, prod, etc).
To activate per-environment configs, create a sub-directory of `src/config` named the same as your environment.
This directory should contain configuration files with any items from the main config you want to override in that environment.

Example:

	src/config/test/database.php

You can tell the app which environment you are running in by modifying the content of `src/config/environment` to contain the name of your active environment.

Then load your config as you normally would. The configuration items will be merged between the default config environment-specific overrides.

See the [werx\Config Docs](https://github.com/werx/config/blob/master/README.md) for more information on configuration management.

### Extra Context Helper Methods

#### getUrl()

Returns the absolute url to the base of your application.

Parameters
    - `$path`: path to get the url for
    - `$qs`: optional array of query string parameters

Avaliables settings:
    - `WerxApp::$settings['expose_script_name']`: Whether or not to expose the executing script name

``` php
var_dump($this->context->getUrl());
// /path/to/index.php ('expose_script_name' = true)
// /path/to/ ('expose_script_name' = false [default])

var_dump($this->config->getUrl('home/index')); // with expose_script_name = true
// /path/to/index.php/home/index ('expose_script_name' = true)
// /path/to/home/index ('expose_script_name' = false [default])
```

#### getUri()

Returns the absolute uri of your application.

Parameters:
    - `$path`: path to get the url for
    - `$qs`: optional array of query string parameters

Avaliables settings:
    - `WerxApp::$settings['expose_script_name']`: Whether or not to expose the executing script name
    - `WerxApp::$settings['base_url']`: Override the schema and host of the app ex: "https://example.com"


``` php
var_dump($this->config->getUri('home/index'));
// https://example.com/index.php/home/index ('expose_script_name' = true)
// https://example.com/home/index ('expose_script_name' = false [default])
```

#### getAsset()

Returns the absolute url to the base of your application.

Parameters
    - `$path`: path to get the url for
    - `$as_uri`: optionally include the full uri to the asset

Avaliables settings:
    - `WerxApp::$settings['expose_script_name']`: Whether or not to expose the executing script name

``` php
var_dump($this->context->getAsset('/assets/css/site.css'));
// /path/to/assets/css/site.css

var_dump($this->context->getAsset('/assets/css/site.css',true));
// http://example.com/path/to/assets/css/site.css
```

## Templates
Templates are powered by the [Plates Project](http://platesphp.com/), which is part of [The League of Extraordinary Packages](http://thephpleague.com/).

Within this project, all themes and layouts live in `src/views`.

### Basic Template Usage

``` php
class Home extends Controller
{
	public function __construct()
	{
		// Set our default template.
		$this->template->layout('layouts/default');
	}

    // you should always return a Response object!!
	public function index()
	{
		// Set some variables for all views.
		$this->template->page_title = 'Werx Skeleton';

		// Render and Display the home/index view, passing a variable named "heading".
		return $this->view('home/index', ['heading' => 'Congratulations, it worked!']); Or
        //return $this->view('index', ['heading' => 'Congratulations, it worked!']); Or
        //return $this->view(['heading' => 'Congratulations, it worked!']);
        // try not to: return $this->template->output('home/index', ['heading' => 'Congratulations, it worked!']);

		// Same as above, but return the rendered content instead of displaying...why who knows
		// $content = $this->template->render('home/index', ['heading' => 'Congratulations, it worked!']);
        // return $this->content($content);
	}
}
```

I've extended the standard Template class from Plates in 2 important ways:

- 1) I've added the `output()` method so you can display the rendered view without an `echo` if you like.
- 2) All variables are escaped before rendering/displaying the content to prevent cross site scripting.

If there are variables you don't want auto-escaped (example: pre-rendered HTML), you can prevent escaping by calling `unguard('param_name')`.

``` php
$this->template->unguard('html');
$this->template->output('home/index', ['html' => '<p>Some <strong>markup</strong>.</p>']);
```

#### Prefill
I've added some functionality to the Template class for easily getting default values in views when the variable doesn't exist.

``` php
class Home extends Controller
{
	public function __construct()
	{
		// Set our default template.
		$this->template->layout('layouts/default');
	}

	public function index()
	{
		// Grab our prefill content from the request and mass assign.
		$this->template->setPrefill($this->input->get());
		
		// Or we can pull the prefill content from session.
		$this->template->setPrefill($this->session->get('prefill'));

		// Render and Display the home/index view.
		return $this->template->output('home/index');
	}
}
```

Then in our view:

```
<!-- Prefill from the 'foo' variable if it exists. If not, it will default to null. -->
<input type="text" name="foo" value="<?=$this->prefill('foo')?>" />

<!-- Prefill from the 'bar' variable if it exists. If not, it will default to 'some default value'. -->
<input type="text" name="bar" value="<?=$this->prefill('bar', 'some default value')?>" />

```

See the [Plates Project documentation](http://platesphp.com/) for more information on template usage.

## HTTP Abstraction
HTTP Abstraction is provided by [Symfony\HttpFoundation](https://github.com/symfony/HttpFoundation). This provides useful things like:

- Object-Oriented access to `$_GET`, `$_POST`, `$_SESSION`, `$_COOKIE`, etc.
- Internal/External Redirects
- JSON/JSONP Responses

``` php
namespace Example\Project\Controllers;

use Illuminate\Database\Capsule\Manager as Model;
use werx\Core\Controller;
use werx\Core\Database as DB;
use werx\Skeleton\Models\Widget;

class Home extends Controller
{
	// Internal Redirect (another resource in our app)
	public function internalRedirect()
	{
		return $this->redirect('home/foo'); # Foo method of the Home Controller.
	}

	// External Redirect
	public function externalRedirect()
	{
		return $this->redirect('http://www.google.com');
	}

	// Output data json-encoded with proper headers.
	public function getJson()
	{
		$data = (object) ['items' => ['foo', 'bar']];
		return $this->json($data);
	}

	// Output data json-encoded with proper headers and callback.
	// Default callback name is 'callback'
	public function getJsonp()
	{
		$data = (object) ['items' => ['foo', 'bar']];
		return $this->jsonp($data, 'callback');
	}
}
```

### $_GET and $_POST

The Core Controller has a reference to the httpFoundation `Request` object, but I find the syntax for accessing `$_GET` and `$_POST` attributes less than ideal. So, I've built a wrapper around the httpFoundation `Request` object to make the syntax friendlier.

Examples:

``` php
# Symfony way to access an attribute from $_POST
$foo = $this->request->request->get('foo');

# Werx way to access an attribute from $_POST
$foo = $this->input->post('foo');

# Symfony way to access an attribute from $_GET
$foo = $this->request->query->get('foo');

# Werx way to access an attribute from $_GET
$foo = $this->input->get('foo');

# Symfony way to access the entire $_POST array
$post = $this->request->request->all();

# Werx way to access the entire $_POST array
$post = $this->input->post();

# Symfony way to access the entire $_GET array
$get = $this->request->query->all();

# Werx way to access the entire $_GET array
$get = $this->input->get();

# Werx way to access body from PUT/POST
$content = $this->input->content();
```

`null` is returned when trying to access a non-existent attribute from get/post. Pass a 2nd parameter to `$this->input->get()` or `$this->input->post()` to serve as the default value if you want something other than `null`.

``` php
// Returns 'foo'
$foo = $this->input->post('nonexistent', 'foo');
```

In the default `Request` object, you have to pass `true` as the 3rd argument to `$this->request->query->get()` in order to access "deep" array keys. This is defaulted to `true` in my override.

``` php
$bar = $this->input->get('foo[bar]'); 
```

See the [Symfony Docs](http://symfony.com/doc/current/components/http_foundation/introduction.html) for more
information on the HttpFoundation component.

## Database
Database abstraction is handled by [Illuminate\Database](https://github.com/illuminate/database)

Your model:

``` php
# File: src/models/Widget.php

namespace Example\Project\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
	public $timestamps = false; # Aren't using the default timestamp columns
}
```

#### Instead of extending Eloquent\Model directly, you can extend werx\Core\Model (which extends Eloquent) to get easier access to the query builder.

``` php
# File: src/models/Widget.php

namespace Example\Project\Models;

use werx\Core\Model;

class Widget extends Model
{
	public $timestamps = false; # Aren't using the default timestamp columns
	
	public static function search($params = null)
	{
		$query = self::queryBuilder();
		
		if (array_key_exists('firstname', $params))
		{
			$query->where('firstname', $params['firstname']); 
		}

		if (array_key_exists('lastname', $params))
		{
			$query->where('lastname', $params['lastname']); 
		}
		
		return $query->get();
	}
	
}
```

> See tests/resources/models/Captain for a working example.

#### Initialize the database before you try to use it.

``` php
use werx\Core\Database as DB;

/*
DSN can be pear-style DSN string: mysql://username:password@host/database OR an array of connection params

$defaults = [
	'driver'	=> 'mysql',
	'host'		=> 'localhost',
	'database'	=> 'mysql',
	'username'	=> 'root',
	'password'	=> null,
	'charset'	=> 'utf8',
	'collation'	=> 'utf8_unicode_ci',
	'prefix'	=> null
];
*/

DB::init($dsn);
```

#### Once your database has been initialized, you can call your ORM models from anywhere.

``` php
// Get all widgets:
$widgets = \Example\Project\Models\Widget::all()->toArray();

foreach ($widgets as $widget) {
	var_dump($widget['type']);
	var_dump($widget['size']);
	var_dump($widget['color']);
}
```
> See the [Laravel Eloquent Documentation](http://laravel.com/docs/eloquent) for more info on the ORM.

#### You also have access to the fluent query builder.

``` php
use Illuminate\Database\Capsule\Manager as QueryBuilder;

$result = QueryBuilder::table('captains')->where('lastname', 'Kirk')->get();
```
> See the [Laravel Database Documentation](http://laravel.com/docs/database) for more info on the fluent query builder.

#### Multiple Database Connection Support
To initialize Eloquent with multiple database connections, you can pass an array of connection params to `DB::init()`.

```php
# DSN Strings
$dsn = ['default' => 'mysql://username:password@hostname/primarydb', 'otherdb' => 'mysql://username:password@hostname/otherdb'];

DB::init($dsn);
```

```php
# Connection setting array
$dsn = [
	'default' => [
		'driver'	=> 'mysql',
		'host'		=> 'localhost',
		'database'	=> 'primarydb',
		'username'	=> 'username',
		'password'	=> 'password'
	],
	'otherdb' => [
		'driver'	=> 'mysql',
		'host'		=> 'localhost',
		'database'	=> 'otherdb',
		'username'	=> 'username',
		'password'	=> 'password'
	]
];

DB::init($dsn);		
```
Then in your model, specify the connection to use.

```php
<?php
use Illuminate\Database\Eloquent\Model;
class Widget extends Model
{
	public $timestamps = false;
	public $connection = 'otherdb';
}
```