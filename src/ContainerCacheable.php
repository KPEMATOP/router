<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 15.04.16
 * Time: 0:19
 */

namespace axisy\router;


use Psr\Cache\CacheItemPoolInterface;

class ContainerCacheable extends Container
{
    public $keyPrefix = 'axisy:router';
    public $stateKey;
    public $ttl = 84600;
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    public function __construct(CacheItemPoolInterface $pool, array $patterns)
    {
        parent::__construct($patterns);
        $this->pool = $pool;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getPool()
    {
        return $this->pool;
    }

    protected function prepareRoute($route)
    {
        $key = $this->keyPrefix . ':' . $route . ':' . $this->stateKey;
        $item = $this->pool->getItem($key);
        if (!$item->isHit()) {
            $item->set(parent::prepareRoute($route));
            $item->expiresAfter($this->ttl);
            $this->pool->save($item);
        }
        return $item->get();
    }

    protected function buildGroupRegex()
    {
        $key = $this->keyPrefix . ':groupRegex' . $this->stateKey;
        $item = $this->pool->getItem($key);
        if (!$item->isHit()) {
            $item->set(parent::buildGroupRegex());
            $item->expiresAfter($this->ttl);
            $this->pool->save($item);
        }
        return $item->get();
    }

    protected function extractParamNames($pattern, array $values)
    {
        $key = $this->keyPrefix . ':' . $pattern . ':' . $this->stateKey;
        $item = $this->pool->getItem($key);
        if (!$item->isHit()) {
            $item->set(parent::extractParamNames($pattern, $values));
            $item->expiresAfter($this->ttl);
            $this->pool->save($item);
        }
        return $item->get();
    }

}