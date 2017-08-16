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
            '/\s*(\S)/',
            $this->description,
            $matches,
            0,
            $this->position
        );
        if ($matched) {
            $first_char = $matches[1];
            $this->position += strlen($matches[0]) - 1;
        } else {
            return false;
        }

        switch ($first_char) {
            case ')':
                return false;
            case '\'':
                $end = strpos($this->description, '\'', $this->position + 1);
                if ($end === false) {
                    throw new ParsingException('unbalanced single quote');
                }
                $this->tokens[] = substr($this->description, $this->position + 1, $end - 1);
                $this->position = $end + 1;
                break;
            case '(':
        }
    }

    protected function stripOid(string $description)
    {

        // match OID
        $matches = [];
        $description_regex = '/^\s* \( \s+ ( [0-2](\.\d+)* ) \s+ (.+) \s+ \) \s*/x';
        $found = preg_match($description_regex, $description, $matches);
        if ($found) {
            $this->properties['oid'] = $matches[1];
            return $matches[3];
        } else {
            throw new \RuntimeException('Unable to parse description');
        }
    }

    public function __get(string $name)
    {
        //$name = strtolower(str_replace('-', '_', $name));
        return array_key_exists($name, $this->properties) ? $this->properties : null;
    }
}
