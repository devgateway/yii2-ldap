<?php
/**
 * Parser class
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

class ParsingException extends \RuntimeException
{
    public function __construct(string $msg)
    {
        $info = "Error at character ${this->position}: $msg";
        parent::__construct($info);
    }
}

class Parser
{
    protected $tokens;
    protected $properties = [];
    protected $description;
    protected $length;
    protected $position = 0;

    public function __construct(string $description)
    {
        // unwrap long lines
        $this->description = str_replace("\n ", '', $description);
        $this->length = strlen($this->description);

        $this->tokens = $this->getToken();
        if (!is_array($this->tokens)) {
            $msg = 'schema description must be enclosed in paretheses';
            throw new ParsingException($msg);
        }
    }

    protected function getToken()
    {
        // find first non-blank character, move position there
        $matches = [];
        $matched = preg_match(
            '/ *([^ ])/',
            $this->description,
            $matches,
            0,
            $this->position
        );
        if ($matched) {
            // found beginning of next token, continue
            $first_char = $matches[1];
            $this->position += strlen($matches[0]) - 1;
        } else {
            // no more tokens
            return false;
        }

        // return string until $char, and move past it, or throw exception
        $find_until = function ($char, $error) {
            $end = strpos($this->description, $char, $this->position);
            if ($end === false) {
                throw new ParsingException($error);
            }
            $token = substr($this->description, $this->position, $end - 1);
            $this->position = $end + 1;
            return $token;
        };

        switch ($first_char) {
            case ')':
                $token = false;
                break;

            case '(':
                $token = [];
                $this->position++; // skip opening paren
                while (true) {
                    $subtoken = $this->getToken();
                    if ($subtoken === false) {
                        break;
                    } else {
                        $token[] = $subtoken;
                    }
                }
                break;

            case '\'':
                $this->position++; // skip opening quote
                $token = $find_until('\'', 'unbalanced single quote');
                // TODO: unescape single quote and backslash
                break;

            default:
                $token = $find_until(' ', 'unterminated bareword');
        }

        return $token;
    }

    public function __get(string $name)
    {
        //$name = strtolower(str_replace('-', '_', $name));
        return array_key_exists($name, $this->properties) ? $this->properties : null;
    }
}
