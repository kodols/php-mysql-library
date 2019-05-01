<?php

namespace Kodols\MySQL;

trait Helpers
{

    private function __call_insert($table, $values, $type)
    {
        $builder = $this->build($type)->into($table);

        foreach ($values as $key => $value) {
            if (is_numeric($key)) {
                $builder->value($value);
            } else {
                $builder->set($key, $value);
            }
        }

        return $builder->execute();
    }

    public function insert($table, array $values = [])
    {
        return $this->__call_insert($table, $values, 'insert');
    }

    public function ignore($table, array $values = [])
    {
        return $this->__call_insert($table, $values, 'ignore');
    }

    public function replace($table, array $values = [])
    {
        return $this->__call_insert($table, $values, 'replace');
    }

    public function delete($table, array $values = [])
    {
        $builder = $this->build('delete')->from($table);

        foreach ($values as $key => $value) {
            $builder->where($key, '=', $value);
        }

        $builder->execute();
    }

    public function update($table, array $set = [], array $where = [])
    {
        $builder = $this->build('update')->table($table);

        foreach ($set as $key => $value) {
            $builder->set($key, '=', $value);
        }

        foreach ($where as $key => $value) {
            $builder->where($key, '=', $value);
        }

        $builder->execute();
    }

	public function replaceMany($table, array $input, $get_query = false, $database = null){
		return $this->insertMany($table, $input, $get_query, $database, 'replace');
	}

	public function ignoreMany($table, array $input, $get_query = false, $database = null){
		return $this->insertMany($table, $input, $get_query, $database, 'insert ignore');
	}

	public function insertMany($table, array $input, $get_query = false, $database = null, $method = 'insert')
	{
		if (empty($input)) {
			return false;
		}

		$table = str_replace('.', '`.`', $table);

		$keys = [];
		$values = [];
		$data = [];
		$rows = count($input);
		$columns = count(reset($input));

		$query = $method . ' INTO ' . ($database !== null ? '`' . $database . '`.' : '') . '`' . $table . '` (';

		foreach (reset($input) as $key => $value) {
			$keys[] = '`' . $key . '`';
		}

		$query .= implode(',', $keys) . ') VALUES';

		foreach ($input as $index => $row)
		{
			foreach ($row as $key => $value)
			{
				$keyName = ':v' . $index . $key;
				$values[] = $keyName;
				$data[$keyName] = $value;
			}

			if ($index != 0) {
				$query .= ',';
			}

			$query .= ' (' . implode(',', $values) . ')';

			$values = [];
		}

		if ($get_query) {
			foreach ($data as $key => $value) {
				$query = str_replace($key, "'".str_replace("'","\\\'", $value), $query);
			}
			return $query;
		}

		$db = $this->prepare($query);
		$db->execute($data);
	}
}
