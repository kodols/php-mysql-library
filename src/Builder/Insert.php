<?php

namespace Kodols\MySQL\Builder;

use Kodols\MySQL\Server;
use Kodols\MySQL\Builder;

class Insert extends Builder
{

    protected $method = 'INSERT INTO';
    protected $buildFormat = 'insert';

    private $table = '';
    private $values = [];
    private $options = [];
    protected $compiled_query = '';
    protected $compiled_params = [];
    protected $server;

    public function __construct(Server $server, array $options = [])
    {
        $this->options['on_duplicate_key_update'] = !empty($options['on_duplicate_key_update']);
        $this->server = $server;
    }

    public function into($name)
    {
        return $this->table($name);
    }

    public function table($name)
    {
        $this->compiled = false;
        $this->table = $name;
        return $this;
    }

    public function set($column, $value)
    {
        $this->compiled = false;
        $this->values[$column] = $value;
        return $this;
    }

    public function value($value)
    {
        $this->compiled = false;
        $this->values[] = $value;
        return $this;
    }

    protected function compile()
    {
        $this->compiled_query = $this->method . ' ';
        $this->compiled_params = [];

        $this->compiled_query .= $this->clean($this->table);

        $useColumnKeys = true;
        $columnKeys = '';
        $queryValues = '';
        $updates = [];

        foreach ($this->values as $key => $value) {
            $value_key = ':v' . count($this->compiled_params);

            if ($useColumnKeys) {
                if (is_numeric($key)) {
                    $useColumnKeys = false;
                } else {
                    $columnKeys .= ($columnKeys ? ',' : '') . $this->clean($key);

                    if (!empty($this->options['on_duplicate_key_update'])) {
                        $updates[] = $this->clean($key).'='.$value_key;
                    }
                }
            }

            $queryValues .= ($queryValues ? ',' : '') . $value_key;
            $this->compiled_params[$value_key] = $value;
        }

        if ($useColumnKeys) {
            $this->compiled_query .= ' (' . $columnKeys . ')';
        }

        $this->compiled_query .= ' VALUES(' . $queryValues . ')';

        if($useColumnKeys && !empty($this->options['on_duplicate_key_update']) && count($updates)){
            $this->compiled_query .= ' ON DUPLICATE KEY UPDATE '.implode(', ', $updates);
        }

        $this->compiled = true;
    }

    public function execute($keepParameters = false)
    {
        parent::execute($keepParameters);
        return $this->server->lastInsertId();
    }

}