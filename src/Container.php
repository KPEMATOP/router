<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 14.04.16
 * Time: 18:21
 */

namespace axisy\router;

    /**
     * Class Container
     * @package axisy\router
     */
/**
 * Class Container
 * @package axisy\router
 */
class Container implements \ArrayAccess
{
    /**
     * Regex for convert pattern to regular expression
     */
    const REGEX_FETCH_EXPR = "#(?<!\\\\)\\{[^:]+:(.+?)(?<!\\\\)\\}#";
    /**
     * Regex for extract parameters name
     */
    const REGEX_EXTRACT_PARAMS = '#(?<!\\\\)\{([^:]+):.+?(?<!\\\\)\}#';
    /**
     * @var string
     */
    public $variableTagFormat = "\t%d\t";
    /**
     * @var string
     */
    public $regexpSeparator = '~';
    /**
     * @var string
     */
    public $identityChar = '#';
    /**
     * @var array
     */
    public $shortcuts = [
        'n' => '[0-9]+\.[0-9]+|\d+',
        'd' => '\d+',
        's' => '\w+'
    ];
    /**
     * @var array
     */
    public $patterns = [];

    /**
     * Container constructor.
     * @param array $patterns
     */
    public function __construct(array $patterns = [])
    {
        $this->extend($patterns);
    }

    /**
     * @param array $data
     * @throws \ErrorException
     */
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
        $this->patterns[$offset] = $value;
    }

    /**
     * @param $route
     * @return mixed
     * @throws NotFound
     */
    public function route($route)
    {
        if (!$match = $this->match($route)) {
            throw new NotFound("Unknown route $route", 0, 1, __FILE__, __LINE__);;
        }

        return call_user_func($match->getHandler(), $match);
    }

    /**
     * @param $route
     * @return Request|bool
     * @throws \ErrorException
     */
    public function match($route)
    {
        $routePrepared = $this->prepareRoute($route);
        $searchExpression = $this->buildGroupRegex();
        if (!$searchExpression) {
            throw new \ErrorException("Empty list of pattern", 0, 1, __FILE__, __LINE__);
        }
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
        return new Request($pattern, $route, $handler, $params);
    }

    /**
     * Adds a route to additional suffix, in which is possible to find the matching pattern
     * @param $route string
     * @return string Result
     */
    protected function prepareRoute($route)
    {
        return $route . str_pad('', count($this->patterns), $this->identityChar);
    }

    /**
     * It collects regular expressions into one expression
     * @return string
     */
    protected function buildGroupRegex()
    {
        $result = '';
        $patterns = array_keys($this->patterns);
        foreach ($patterns as $key => $pattern) {
            $result .= $result ? '|' : '';
            $key++;
            $result .= '^' . $this->toRegexp($pattern) . "({$this->identityChar}{{$key}}){$this->identityChar}*$";
        }
        return $this->regexpSeparator . '(?|' . $result . ')' . $this->regexpSeparator;
    }

    /**
     * Convert pattern to regular expression
     * @param $pattern string The template may contain special
     *                        tags to retrieve parameters from the route.
     *                        Tags should be in the format {name: [expresson | shortcut]}
     * @return mixed
     */
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

    /**
     * Union values and parameter names
     * @param $pattern string Source pattern
     * @param array $values The known value, the number must match the
     *                      number of tags in the request
     * @return array
     */
    protected function extractParamNames($pattern, array $values)
    {
        if ($values && preg_match_all(self::REGEX_EXTRACT_PARAMS, $pattern, $matches)) {
            $values = array_combine($matches[1], $values);
        }
        return $values;
    }

    /** @inheritdoc */
    public function offsetExists($offset)
    {
        return isset($this->patterns[$offset]);
    }

    /** @inheritdoc */
    public function offsetGet($offset)
    {
        return $this->patterns[$offset];
    }

    /** @inheritdoc */
    public function offsetUnset($offset)
    {
        unset($this->patterns[$offset]);
    }
}