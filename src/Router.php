<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 14.04.16
 * Time: 18:21
 */

namespace axisy\router;


class Router implements \ArrayAccess
{
    const REGEX_FETCH_EXPR = "#(?<!\\\\)\\{[^:]+:(.+?)(?<!\\\\)\\}#";
    const REGEX_EXTRACT_PARAMS = '#(?<!\\\\)\{([^:]+):.+?(?<!\\\\)\}#';
    public $variableTagFormat = "\t%d\t";
    public $regexpSeparator = '~';
    public $identityChar = '#';
    public $shortcuts = [
        'f' => '(?:[0-9]+\.[0-9]+|\d+)',
        'i' => '\d+',
        's' => '\w+'
    ];
    public $patterns = [];
    private $preparedSearchRegex;

    public function __construct(array $patterns = [])
    {
        $this->extend($patterns);
    }

    public function extend(array $data)
    {
        foreach ($data as $pattern => $handler) {
            $this->offsetSet($pattern, $handler);
        }
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @throws \ErrorException
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (!is_callable($value)) {
            throw new \ErrorException("Value must be a callable", 0, 1, __FILE__, __LINE__);
        }
        $this->preparedSearchRegex = '';
        $this->patterns[$offset] = $value;
    }

    public function route($route)
    {
        if (!$match = $this->match($route)) {
            return false;
        }

        return call_user_func($match->getHandler(), $match);
    }

    /**
     * @param $route
     * @return Match|bool
     */
    public function match($route)
    {
        $routePrepared = $this->prepareRoute($route);
        $searchExpression = $this->buildGroupRegex();
        if (!preg_match($searchExpression, $routePrepared, $params)) {
            return false;
        }
        //drop first element because of uselessness
        array_shift($params);
        $index = strlen(array_pop($params)) - 1;
        $patterns = array_keys($this->patterns);
        if (!isset($patterns[$index])) {
            return false;
        }
        $pattern = $patterns[$index];
        unset($patterns, $index, $regex, $searchExpression, $routePrepared);
        $handler = $this->patterns[$pattern];
        $params = $this->extractParamNames($pattern, $params);
        return new Match($pattern, $route, $handler, $params);
    }

    protected function prepareRoute($route)
    {
        return $route . str_pad('', count($this->patterns), $this->identityChar);
    }

    protected function buildGroupRegex()
    {
        if (!$this->preparedSearchRegex) {
            $result = '';
            $patterns = array_keys($this->patterns);
            foreach ($patterns as $key => $pattern) {
                $result .= $result ? '|' : '';
                $key++;
                $result .= '^' . $this->toRegexp($pattern) . "({$this->identityChar}{{$key}}){$this->identityChar}*$";
            }
            $this->preparedSearchRegex = $this->regexpSeparator . '(?|' . $result . ')' . $this->regexpSeparator;
        }
        return $this->preparedSearchRegex;
    }

    public function toRegexp($pattern)
    {
        $exprList = [];
        $replaceHandler = function ($matches) use (&$exprList) {
            list(, $expr) = $matches;
            if (isset($this->shortcuts[$expr])) {
                $expr = $this->shortcuts[$expr];
            }
            $exprList[] = $expr;
            return sprintf($this->variableTagFormat, count($exprList) - 1);
        };

        $regexp = preg_replace_callback(self::REGEX_FETCH_EXPR, $replaceHandler, $pattern);

        if ($regexp) {
            $regexp = preg_quote($regexp, $this->regexpSeparator);
            $search = $replace = [];
            foreach ($exprList as $key => $exp) {
                $search[] = sprintf($this->variableTagFormat, $key);
                $replace[] = '(' . $exp . ')';
            }
            return str_replace($search, $replace, $regexp);
        }
        return $pattern;
    }

    protected function extractParamNames($pattern, array $params)
    {
        if ($params && preg_match_all(self::REGEX_EXTRACT_PARAMS, $pattern, $matches)) {
            $params = array_combine($matches[1], $params);
        }
        return $params;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->patterns[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->patterns[$offset];
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->patterns[$offset]);
    }
}