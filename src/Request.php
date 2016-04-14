<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 14.04.16
 * Time: 20:14
 */

namespace axisy\router;


class Request
{
    private $route;
    private $params = [];
    private $handler;
    private $pattern;

    public function __construct($pattern, $route, $handler, array $params = [])
    {
        $this->pattern = $pattern;
        $this->route = $route;
        $this->handler = $handler;
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }
}