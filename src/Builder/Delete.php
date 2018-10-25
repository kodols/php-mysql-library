<?php

namespace Kodols\MySQL\Builder;

use \Exception;
use \Kodols\MySQL\Server;
use \Kodols\MySQL\Builder;

class Delete extends Builder
{

    protected $buildFormat = 'delete';

    private static $compileIndex = 0;
    private $compileIndexActive;

    private $column_indexes = [];
    private $where_indexes = [];

    protected $compiled_query = '';
    protected $compiled_params = [];
    protected $server;

    public function __construct(Server $server)
    {
        self::$compileIndex++;
        $this->compileIndexActive = hash('crc32', self::$compileIndex);
        $this->server = $server;
    }

    public function reset()
    {
        return new self($this->server);
    }

    protected function compile()
    {
        $this->compiled_query = '';
        $this->compiled_query .= 'DELETE';

        $raw_column = 0;
        $column = 0;
        $holder = [];

        foreach ($this->column_indexes as $index) {
            list($column_name, $column_alias) = $this->{$index}[$$index++];

            if ($index == 'column') {
                $column_name = $this->clean($column_name);
            }

            if ($column_alias !== null) {
                $column_alias = $this->clean($column_alias);
                $holder[] = $column_name . ' AS ' . $column_alias;
            } else {
                $holder[] = $column_name;
            }
        }

        if (count($holder)) {
            $this->compiled_query .= ' ' . implode(', ', $holder);
        }

        $this->compiled_query .= ' FROM';

        $holder = [];
        foreach ($this->from as $data) {
            $data[0] = $this->clean($data[0]);

            if ($data[1] !== null) {
                $data[1] = $this->clean($data[1]);
                $holder[] = $data[0] . ' AS ' . $data[1] . '';
            } else {
                $holder[] = $data[0];
            }
        }

        $this->compiled_query .= ' ' . implode(', ', $holder);

        foreach ($this->joins as $joinIndex => $joinData) {
            list($table, $alias, $format) = $joinData;

            $format = strtoupper($format);
            $table = $this->clean($table);

            if ($alias !== null) {
                $alias = $this->clean($alias);
                $this->compiled_query .= ' ' . $format . ($format ? ' ' : '') . 'JOIN ' . $table . ' AS ' . $alias;
            } else {
                $this->compiled_query .= ' ' . $format . ($format ? ' ' : '') . 'JOIN ' . $table;
            }

            if (isset($this->on[$joinIndex])) {
                $holder = '';
                foreach ($this->on[$joinIndex] as $on) {
                    list($field1, $operator, $field2, $statement) = $on;
                    $holder .= ($holder ? ' ' . $statement : 'ON');

                    $field1 = $this->clean($field1);
                    $field2 = $this->clean($field2);

                    $holder .= ' ' . $field1 . ' ' . $operator . ' ' . $field2;
                }

                $this->compiled_query .= ' ' . $holder;
            }
        }

        $raw_where = 0;
        $where = 0;
        $where_in_values = 0;
        $where_in_subquery = 0;

        $holder = '';
        $was_change = true;

        foreach ($this->where_indexes as $windex) {
            if ($windex == 'and_open' || $windex == 'or_open') {
                $holder .= ($holder ? ($windex == 'and_open' ? ' AND ' : ' OR ') : '') . '(';
                $was_change = true;
                continue;
            } elseif ($windex == 'close') {
                $holder .= ')';
                continue;
            } elseif ($windex == 'where') {
                list($field, $operator, $value, $format, $values2, $splitter) = $this->{$windex}[$$windex++];
                $holder .= ($was_change ? '' : ' ' . $splitter . ' ');
                $was_change = false;

                $field = $this->clean($field);
                $key = ':c' . $this->compileIndexActive . 'v' . hash('crc32', count($this->compiled_params));
                $this->compiled_params[$key] = $value;
                $holder .= $field . ' ' . $operator . ' ' . $key;

                if ($format !== null) {
                    $holder .= ' ' . $format;
                }

                if ($values2 !== null) {
                    $key = ':c' . $this->compileIndexActive . 'v' . hash('crc32', count($this->compiled_params));
                    $this->compiled_params[$key] = $values2;
                    $holder .= ' ' . $key;
                }
            } elseif ($windex == 'raw_where') {
                list($field, $operator, $value, $format, $values2, $splitter) = $this->{$windex}[$$windex++];
                $holder .= ($was_change ? '' : ' ' . $splitter . ' ');
                $was_change = false;

                $field = $this->clean($field);
                $holder .= $field . ' ' . $operator . ' ' . $value;

                if ($format !== null) {
                    $holder .= ' ' . $format;
                }

                if ($values2 !== null) {
                    $holder .= ' ' . $values2;
                }
            } elseif ($windex == 'where_in_values') {
                list($field, $values, $splitter, $prefix) = $this->{$windex}[$$windex++];
                $field = $this->clean($field);

                $holder .= ($was_change ? '' : ' ' . $splitter . ' ');
                $was_change = false;

                $holder .= $field . ($prefix ? ' ' . $prefix : '') . ' IN(';

                foreach ($values as $index => $value) {
                    if ($index) {
                        $holder .= ', ';
                    }
                    $key = ':c' . $this->compileIndexActive . 'v' . hash('crc32', count($this->compiled_params));
                    $this->compiled_params[$key] = $value;
                    $holder .= $key;
                }
                $holder .= ')';
            } elseif ($windex == 'where_in_subquery') {
                list($field, $value, $splitter, $prefix) = $this->{$windex}[$$windex++];
                $field = $this->clean($field);

                $holder .= ($was_change ? '' : ' ' . $splitter . ' ');
                $was_change = false;

                $holder .= $field . ($prefix ? ' ' . $prefix : '') . ' IN(' . $value . ')';
            }
        }

        if ($holder) {
            $this->compiled_query .= ' WHERE ' . $holder;
        }

        $this->compiled = true;
    }

    private $raw_column = [];

    public function raw_column($name, $alias = null)
    {
        $this->compiled = false;
        $this->raw_column[] = [$name, $alias];
        $this->column_indexes[] = 'raw_column';
        return $this;
    }

    private $column = [];

    public function column($name, $alias = null)
    {
        $this->compiled = false;
        $this->column[] = [$name, $alias];
        $this->column_indexes[] = 'column';
        return $this;
    }

    public function subquery($query, $alias = null)
    {
        $this->compiled = false;
        if (!is_string($query)) {
            if ($query instanceof Select) {
                $query = $query->debug(true);
                $this->compiled_params = array_merge($this->compiled_params, $query['parameters']);
                $query = $query['query'];
            } else {
                throw new Exception('WHERE IN SUBQUERY requires either a raw sql query string or a SELECT builder that has not been executed.');
            }
        }

        $this->raw_column[] = ['(' . $query . ')', $alias];
        $this->column_indexes[] = 'raw_column';

        return $this;
    }


    private $from = [];

    public function from($name, $alias = null)
    {
        $this->compiled = false;
        $this->from[] = [$name, $alias];
        return $this;
    }

    private $joins = [];

    public function join($table, $alias = null, $format = 'LEFT')
    {
        $this->compiled = false;
        if (!$format) {
            $format = '';
        }

        $this->joins[] = [$table, $alias, $format];

        return $this;
    }

    private $on = [];

    public function on($field1, $operator, $field2, $format = 'AND')
    {
        $this->compiled = false;
        if (!count($this->joins)) {
            throw new Exception('Cannot call query builders ON if there was no join initiated.');
        }

        $joinIndex = count($this->joins) - 1;

        if (!isset($this->on[$joinIndex])) {
            $this->on[$joinIndex] = [];
        }

        $this->on[$joinIndex][] = [$field1, $operator, $field2, strtoupper($format)];

        return $this;
    }

    private $where = [];

    public function where($field, $operator, $value, $format = null, $values2 = null, $splitter = 'AND')
    {
        $this->compiled = false;
        $this->where_indexes[] = 'where';
        $this->where[] = [$field, $operator, $value, $format, $values2, $splitter];
        return $this;
    }

    public function or_where($field, $operator, $value, $format = null, $values2 = null)
    {
        return $this->where($field, $operator, $value, $format, $values2, 'OR');
    }

    public function and_where($field, $operator, $value, $format = null, $values2 = null)
    {
        return $this->where($field, $operator, $value, $format, $values2, 'AND');
    }

    private $raw_where = [];

    public function raw_where($field, $operator, $value, $format = null, $values2 = null, $splitter = 'AND')
    {
        $this->compiled = false;
        $this->where_indexes[] = 'raw_where';
        $this->raw_where[] = [$field, $operator, $value, $format, $values2, $splitter];
        return $this;
    }

    public function raw_or_where($field, $operator, $value, $format = null, $values2 = null)
    {
        return $this->raw_where($field, $operator, $value, $format, $values2, 'OR');
    }

    public function raw_and_where($field, $operator, $value, $format = null, $values2 = null)
    {
        return $this->raw_where($field, $operator, $value, $format, $values2, 'AND');
    }

    public function open()
    {
        $this->compiled = false;
        $this->where_indexes[] = 'and_open';
        return $this;
    }

    public function or_open()
    {
        $this->compiled = false;
        $this->where_indexes[] = 'or_open';
        return $this;
    }

    public function close()
    {
        $this->compiled = false;
        $this->where_indexes[] = 'close';
        return $this;
    }

    private $where_in_values = [];

    public function where_in_values($field, array $values, $splitter = 'AND', $prefix = '')
    {
        $this->compiled = false;

        if (!count($values)) {
            throw new Exception('The WHERE_IN_VALUES requires $values array to have values.');
        }

        $this->where_indexes[] = 'where_in_values';
        $this->where_in_values[] = [$field, $values, $splitter, $prefix];
        return $this;
    }

    public function or_where_in_values($field, array $values)
    {
        return $this->where_in_values($field, $values, 'OR');
    }

    public function and_where_in_values($field, array $values)
    {
        return $this->where_in_values($field, $values, 'AND');
    }

    public function where_not_in_values($field, array $values, $splitter = 'AND')
    {
        return $this->where_in_values($field, $values, $splitter, 'NOT');
    }

    public function and_where_not_in_values($field, array $values)
    {
        return $this->where_in_values($field, $values, 'AND', 'NOT');
    }

    public function or_where_not_in_values($field, array $values)
    {
        return $this->where_in_values($field, $values, 'OR', 'NOT');
    }

    private $where_in_subquery = [];

    public function where_in_subquery($field, $value, $splitter = 'AND', $prefix = '')
    {
        $this->compiled = false;

        if (!is_string($value)) {
            if ($value instanceof Select) {
                $value = $value->debug(true);
                $this->compiled_params = array_merge($this->compiled_params, $value['parameters']);
                $value = $value['query'];
            } else {
                throw new Exception('WHERE IN SUBQUERY requires either a raw sql query string or a SELECT builder that has not been executed.');
            }
        }

        $this->where_indexes[] = 'where_in_subquery';
        $this->where_in_subquery[] = [$field, $value, $splitter, $prefix];
        return $this;
    }

    public function and_where_in_subquery($field, $value)
    {
        return $this->where_in_subquery($field, $value);
    }

    public function or_where_in_subquery($field, $value)
    {
        return $this->where_in_subquery($field, $value, 'OR');
    }

    public function where_not_in_subquery($field, $value)
    {
        return $this->where_in_subquery($field, $value, 'AND', 'NOT');
    }

    public function and_where_not_in_subquery($field, $value)
    {
        return $this->where_in_subquery($field, $value, 'AND', 'NOT');
    }

    public function or_where_not_in_subquery($field, $value)
    {
        return $this->where_in_subquery($field, $value, 'OR', 'NOT');
    }

}
