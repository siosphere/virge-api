# Virge::Api
Virge::Api allows the simple creation of API endpoints. 

Virge::Api allows defining get/post/put/delete methods, as well as chaining API authentication methods by defining custom verifiers.

## Getting Started
To use Virge::Api you should start with a default Virge::Project
```
composer create-project virge/project ./my-project
```

This will create an empty Virge::Reactor, and create the skeleton required to setup the Api.

The entire directory needs to exist on your webserver, but the entry directory should be the ./public directory, this can be symlinked:
```
# assuming the project exists in /var/www/my-project 
ln -s /var/www/my-project/public /var/www/html
```
This can also be done using docker:
```
docker run -d -v $PWD:/var/www/my-project -p 3000:80 siosphere/php7
```
Once the container is up, you'll need to run the symlink command like above

Virge::Project also requires rewrites, for apache you can set that up by creating myproject.conf in /etc/httpd/conf.d/
```
<Directory "/var/www/html">
    Options FollowSymLinks
    AllowOverride All
</Directory>
```
Restarting the docker container or apache will then allow you to visit localhost:3000 in your browser.
You will get a 500 error, a blank white page, or a 404 page depending on configuration.
This is because there is no routing or routes setup.

### Hello Api
We need to include Virge::Api as a capsule, and add the package to our project:
```
composer require virge/api
```

To create our first API route, we need to create our First Capsule. A Capsule is registered with the Reactor and will automatically load configuration files for us, loading services, controllers, and api definition files.

Under the src directory we can create a MyProject directory
We will need to create MyProjectCapsule.php under this directory:
```php
<?php
namespace MyProject;

class MyProjectCapsule extends \Virge\Core\Capsule
{
    public function registerCapsule()
    {
        
    }
}
```
Now we need to add our Capsule to app/Reactor.php, add both the MyProject\MyProjectCapsule() as well as the Virge\Api\Capsule()
```php
...
parent::registerCapsules(array(
    new MyProject\MyProjectCapsule(),

    new Virge\Api\Capsule(),
    new Virge\Cli\Capsule(),
    new Virge\Cron\Capsule(),
    new Virge\Database\Capsule(),
    new Virge\ORM\Capsule(),
    new Virge\Router\Capsule(),
));
...
```
While in this file, we can also define what API version(s) are active. after our parent::registerCapsules call, let's enable version 1 of our Api:
```php
Virge\Api::versions([1]);
```

We also need to tell composer to automatically load our capsule, update bootstrap.php to add our namespace:
```php
/**
 * Add namespaces one by one, this would be the start of your namespace, for
 * example, if I had src/Siosphere, my namespace would be Siosphere
 */
$namespaces = array(
    'MyProject',
);
```

Now we can register our API Method, creating the file src/MyProject/resources/config/api.php
```php
<?php

use Virge\Api;

Api::get('hello')
    ->version('all', function() {
        return 'world';
    })
;
```

Visiting http://localhost:3000/api/v/1/hello should now return a json response with "world"

Api's can also call controller functions, and the methods are passed in the Virge::Router Request object, allowing you to get the JSON request body, GET and POST Fields, as well as URL parameters.

### Api Verification
You can define custom API verifiers, which are simply callback functions that return true or false to allow access
For example, to update our hello API endpoint to require an api key, we can add a verifier:
In config/api.php
```php
Api::verifier('api_key', function($request) {
    return $request->get('apiKey') === '123';
});

Api::get('hello')
    ->verify('api_key')
    ->version('all', function() {
        return 'world';
    })
;
```
Visting http://localhost:3000/api/v/1/hello in your browser will now return a non successful response, but adding in the apiKey:
http://localhost:3000/api/v/1/hello?apiKey=123
Will allow you to visit the page.

A method can have as many verifiers as you want, and ALL verifiers must return true for the function to be called and the method to be successful.

### Api Parameters
Api method names can also contain URL Parameters, these parameters are accessed via the request object:
```php
Api::get('hello/{name}')
    ->version('all', function($request) {
        return sprintf("Hello: %s", $request->getUrlParam('name'));
    })
;
```

Visiting http://localhost:3000/api/v/1/hello/bob will now return a JSON body with "Hello: bob"
