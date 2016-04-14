<?php
use axisy\router as router;

class RegexpTest extends \Codeception\Test\Unit
{
    public $expList = [
        'user.{name:.*?}' => 'user\.(.*?)',
        'user.{name:.*?}.{age:\d+}' => 'user\.(.*?)\.(\d+)',
    ];
    public $testParams = [
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
        foreach ($this->testParams as $pattern => $regexp) {
            $router[$pattern] = function () {
            };
        }
        foreach ($this->testParams as $data) {
            $result = $router->match($data['route']);
            $this->tester->assertTrue($result instanceof router\Request);
            $this->tester->assertEquals($result->getParams(), $data['params']);
        }
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}