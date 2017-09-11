<?php
/**
 * FilterBuilder class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

/**
 * Encapsulates an LDAP search filter
 */
class FilterBuilder
{
    const _OR =  '|';
    const _AND = '&';
    const _NOT = '!';
    const _GTE = '>=';
    const _LTE = '<=';

    protected $operator;
    protected $comparison;
    protected $operands = [];

    /**
     * Initialize class members, and validate search scope.
     *
     * @param string $operator filter operator
     * @param array $args filter arguments, can include arrays or other FilterBuilders
     * @throws InvalidArgumentException if array does not include only arrays or FilterBuilders
     */
    public function __construct($operator, $args)
    {
        $this->operator = $operator;
        switch ($operator) {
            case self::_GTE:
                $this->comparison = '>=';
                break;
            case self::_LTE:
                $this->comparison = '<=';
                break;
            default:
                $this->comparison = '=';
        }

        // handle each memeber of $args depending on its type
        foreach ($args as $arg) {
            if ($arg instanceof FilterBuilder) {
                array_push($this->operands, $arg);
            } elseif (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $operand = "(${key}$this->comparison${v})";
                            array_push($this->operands, $operand);
                        }
                    } else {
                        $operand = "(${key}$this->comparison${value})";
                        array_push($this->operands, $operand);
                    }
                }
            } else {
                throw new \InvalidArgumentException('Not an array or a FilterBuilder');
            }
        }
    }

    /**
     * String representation of the FilterBuilder object
     * @return string
     */
    public function __toString()
    {
        if ($this->operator === self::_GTE or $this->operator === self::_LTE) {
            return $this->operands[0];
        } else {
            $result = '(' . $this->operator;
            foreach ($this->operands as $operand) {
                $result .= $operand;
            }
            $result .= ')';
            return $result;
        }
    }

    /**
     * Creates a FilterBuilder object for the EITHER operator
     * @return FilterBuilder
     * @throws InvalidArgumentException if no arguments are provided
     */
    public static function either()
    {
        if (func_num_args() === 2 and
            is_array(func_get_arg(0)) and
            !func_get_arg(1) instanceof FilterBuilder and
            !is_array(func_get_arg(1))) {
            $keys = func_get_arg(0);
            $value = func_get_arg(1);
            $args = [];
            foreach ($keys as $key) {
                $args[$key] = $value;
            }
            return new FilterBuilder(self::_OR, [$args]);
        } elseif (func_num_args() > 0) {
            return new FilterBuilder(self::_OR, func_get_args());
        } else {
            throw new \InvalidArgumentException('No arguments provided');
        }
    }

    /**
     * Creates a FilterBuilder object for the ALL operator
     * @return FilterBuilder
     * @throws InvalidArgumentException if no arguments are provided
     */
    public static function all()
    {
        if (func_num_args() === 2 and
            is_array(func_get_arg(0)) and
            !func_get_arg(1) instanceof FilterBuilder and
            !is_array(func_get_arg(1))) {
            $keys = func_get_arg(0);
            $value = func_get_arg(1);
            $args = [];
            foreach ($keys as $key) {
                $args[$key] = $value;
            }
            return new FilterBuilder(self::_AND, [$args]);
        } elseif (func_num_args() > 0) {
            return new FilterBuilder(self::_AND, func_get_args());
        } else {
            throw new \InvalidArgumentException('No arguments provided');
        }
    }

    /**
     * Creates a FilterBuilder object for the NEITHER operator
     * @return FilterBuilder
     * @throws InvalidArgumentException if no arguments are provided
     */
    public static function neither()
    {
        if (func_num_args() === 1) {
            return new FilterBuilder(self::_NOT, func_get_args());
        } elseif (func_num_args() > 1) {
            $or = new FilterBuilder(self::_OR, func_get_args());
            return new FilterBuilder(self::_NOT, [$or]);
        } else {
            throw new \InvalidArgumentException('No arguments provided');
        }
    }

    /**
     * Creates a FilterBuilder object for the GREATER THAN OR EQUAL TO operator
     * @return FilterBuilder
     * @throws InvalidArgumentException if no arguments are provided
     */
    public static function gte()
    {
        if (func_num_args() > 0) {
            return new FilterBuilder(self::_GTE, func_get_args());
        } else {
            throw new \InvalidArgumentException('No arguments provided');
        }
    }

    /**
     * Creates a FilterBuilder object for the LESS THAN OR EQUAL TO operator
     * @return FilterBuilder
     * @throws InvalidArgumentException if no arguments are provided
     */
    public static function lte()
    {
        if (func_num_args() > 0) {
            return new FilterBuilder(self::_LTE, func_get_args());
        } else {
            throw new \InvalidArgumentException('No arguments provided');
        }
    }

    /**
     * Creates a FilterBuilder object for the ANY operator
     * @return FilterBuilder
     * @throws InvalidArgumentException if no arguments are provided
     */
    public static function any($keys, $values)
    {
        $values_split = preg_split('/[\s]+/', trim($values), -1, PREG_SPLIT_NO_EMPTY);
        $values_array = array_map(function ($value) {
            return("*$value*");
        }, $values_split);
        $args = [];
        foreach ($keys as $key) {
            $args[$key] = $values_array;
        }
        return new FilterBuilder(self::_OR, [$args]);
    }
}
