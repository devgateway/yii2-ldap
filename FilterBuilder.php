<?php
/**
 * FilterBuilder class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;


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

    public function __construct($operator, $args)
    {
        $this->operator = $operator;
        switch ($operator) {
            case self::_GTE:
                $this->comparison = '>=';
            case self::_LTE:
                $this->comparison = '<=';
            default:
                $this->comparison = '=';
        }

        foreach ($args as $arg) { 
            if ($arg instanceof FilterBuilder) {
                array_push($this->operands, $arg);
            }
            elseif (is_array($arg)) {
                foreach ($arg as $key=>$value) {
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
                throw new \RuntimeException("not an array or a FilterBuilder");
            }
        }
    }

    public function __toString()
    {
        if ($this->operator === self::_GTE or $this->operator === self::_LTE) {
           return $this->operands[0];
        } else {
            $result = "(".$this->operator;
            foreach ($this->operands as $operand) {
                $result .= $operand;
            }
            $result .= ")";
            return $result;
        }
    }

    public static function _or()
    {
        if (func_num_args() > 0) { 
            return new FilterBuilder(self::_OR, func_get_args());
        } else {
            throw new \RuntimeException("No arguments provided");
        }
    }

    public static function _and()
    {
        if (func_num_args() > 0) { 
            return new FilterBuilder(self::_AND, func_get_args());
        } else {
            throw new \RuntimeException("No arguments provided");
        }
    }

    public static function _not()
    {
        if (func_num_args() > 0) { 
            return new FilterBuilder(self::_NOT, func_get_args());
        } else {
            throw new \RuntimeException("No arguments provided");
        }
    }

    public static function _gte()
    {
        throw new \RuntimeException("Not Implemented");
    }

    public static function _lte()
    {
        throw new \RuntimeException("Not Implemented");
    }

    public static function _each()
    {
        throw new \RuntimeException("Not Implemented");
    }

    public static function _either()
    {
        throw new \RuntimeException("Not Implemented");
    }

    public static function _any()
    {
        throw new \RuntimeException("Not Implemented");
    }
}

