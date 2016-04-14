# fast PHP router
## Installing
Via composer `composer require axisy/router`

Package page in packagist.org https://packagist.org/packages/axisy/router

## Usage
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
$router['{route:.*}'] = function(route\Match $match){}
```
