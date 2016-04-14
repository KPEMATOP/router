<?php
use Codeception\Util\Stub;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testMe()
    {
        /** @var CacheItemPoolInterface $pool */
        $pool = Stub::makeEmpty(CacheItemPoolInterface::class, [
            'getItem' => Stub::atLeastOnce(function () {
                return Stub::makeEmpty(CacheItemInterface::class, [
                    'isHit' => Stub::once(function () {
                        return false;
                    }),
                    'set' => Stub::once(function () {
                    }),
                    'get' => Stub::once(function () {
                    }),
                ]);
            }),
            'save' => Stub::exactly(2, function () {
            }),
        ]);
        $router = new \axisy\router\ContainerCacheable($pool, [
            'foo' => function () {
            },
            'bar' => function () {
            }
        ]);
        try {
            $router->route('foo');
        } catch (ErrorException $e) {

        }
    }

    protected function _before()
    {
    }

    // tests

    protected function _after()
    {
        \AspectMock\Test::clean();
    }
}