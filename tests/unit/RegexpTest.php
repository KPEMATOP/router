<?php
use axisy\router as router;

class RegexpTest extends \Codeception\Test\Unit
{
    public $expList = [
        'user.{name:.*?}' => 'user\.(.*?)',
        'user.{name:.*?}.{age:\d+}' => 'user\.(.*?)\.(\d+)',
        '{name:.*?}.{age:\d+}.{foo:s}' => '(.*?)\.(\d+)\.(\w+)',
        '{foo:s}{bar:d}{baz:n}' => '(\w+)(\d+)([0-9]+\.[0-9]+|\d+)',
    ];
    public $testParams = [
        'number/{number:n}' => [
            'route' => 'number/12.98',
            'params' => [
                'number' => 12.98
            ]
        ],
        'decimal/{decimal:d}' => [
            'route' => 'decimal/12',
            'params' => [
                'decimal' => 12
            ]
        ],
        'string/{string:s}' => [
            'route' => 'string/foo',
            'params' => [
                'string' => 'foo'
            ]
        ],
        'user' => [
            'route' => 'user',
            'params' => []
        ],
        'user.{name:\w+}' => [
            'route' => 'user.bert',
            'params' => ['name' => 'bert']
        ],
        'user.{name:.\w+}.{age:\d+}' => [
            'route' => 'user.bert.23',
            'params' => [
                'name' => 'bert',
                'age' => 23
            ]
        ],
        '{route:.*}' => [
            'route' => 'undefinedExistRoute',
            'params' => [
                'route' => 'undefinedExistRoute'
            ]
        ]
    ];
    /**
     * @var \UnitTester
     */
    protected $tester;


    public function testToRegexp()
    {
        $router = new router\Container();
        foreach ($this->expList as $pattern => $regexp) {
            $this->tester->assertEquals($router->toRegexp($pattern), $regexp);
        }
    }

    public function testMatch()
    {
        $router = new router\Container();
        foreach ($this->testParams as $pattern => $data) {
            $router[$pattern] = function () {
            };
            $result = $router->match($data['route']);
            $this->tester->assertTrue($result instanceof router\Request);
            $this->tester->assertEquals($result->getParams(), $data['params']);
        }
        foreach ($this->testParams as $data) {
            $result = $router->match($data['route']);
            $this->tester->assertTrue($result instanceof router\Request);
            $this->tester->assertEquals($result->getParams(), $data['params']);
        }
    }

    public function testRun()
    {
        $result = 'Hello world';
        $route = new router\Container([
            'test/route/{name:\d}' => function ($request) use ($result) {
                $this->tester->assertNotEmpty($request);
                $this->tester->assertTrue($request instanceof router\Request);
                return $result;
            }
        ]);
        $out = $route->route('test/route/1');
        $this->tester->assertEquals($out, $result);
    }

    public function testUndefinedRoute()
    {
        $route = new router\Container();
        $exception = null;
        try {
            $route->route('undefined/route');
        } catch (ErrorException $e) {
            $exception = $e;
        } finally {
            $this->tester->assertTrue($exception instanceof router\NotFound);
        }
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}