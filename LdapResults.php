<?php
namespace devgateway\ldap;

class LdapResults implements \Iterator
{
    protected $current_entry = false;
    protected $link_id;
    protected $result_id;

    public function __construct($link_id, $result_id)
    {
        $this->link_id = $link_id;
        $this->result_id = $result_id;
    }

    public function current()
    {
        return ldap_get_attributes($this->link_id, $this->current_entry);
    }

    public function key()
    {
        return ldap_get_dn($this->link_id, $this->current_entry);
    }

    public function next()
    {
        $this->current_entry = @ldap_next_entry($this->link_id, $this->result_id);
    }

    public function rewind()
    {
        $this->current_entry = @ldap_first_entry($this->link_id, $this->result_id);
    }

    public function valid()
    {
        return $this->current_entry !== false;
    }

    public function count()
    {
        $n = ldap_count_entries($this->link_id, $this->result_id);
        if ($n === false) {
            throw new \RuntimeException('Can\'t count LDAP entries');
        }

        return $n;
    }

    public function __destruct()
    {
        ldap_free_result($this->result_id);
    }
}
