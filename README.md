# Fast PHP router


Small package allowing to organize quickly routing.
If you are going to use for routing of HTTP requests, it will be necessary to expand the package and add the ability to process the HTTP request types. Routing arranged on a similar principle as described in https://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html, many thanks to the author of the blog =)

On the production server, use the `axisy\router\ContainerCacheable`, compatible __psr-6__ cache interface
## Installing
Via composer `composer require axisy/router`
## Basic usage
```
use \axisy\router as router;

$router = new router\Container([
    'migrate.{command:\w+}' => function(router\Match $match){
        //matches pattern
        $pattern = $match->getPattern();
        //processed route
        $route = $match->getRoute();
        //The parameters obtained from the route
        $params = $match->getParams();
    }
])
//register new route handler
$router['user/{id:\d}'] = function(route\Match $match){}

//Initializing routing
$router->route('route')
```
## Usage shortcuts
_shortcuts_ - this contraction for regular expressions.
```
$router['{page:s}/{identity:d}'] = function(){}
```
Initially, the following shortcut available.

1. __n__ => Number, equal to (?:[0-9]+\.[0-9]+|\d+) 
2. __d__ - Decimal, equal to `\d+`  
3. __s__ - String, equal to `\w+`   

```
//ad shortcuts
$router->shortcuts['username'] = '[A-z][A-z0-9_]+';
//usage defined shrtcut
$router['/user/{name:username}'] = function(){};
...
$router->route('/user/foobarbaz')
```
