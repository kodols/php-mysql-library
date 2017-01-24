<?php

	namespace Kodols\MySQL\Builder;

	use \Exception;
	use \Kodols\MySQL\Server;
	use \Kodols\MySQL\Builder;

	class Update extends Builder {

		protected $buildFormat = 'update';
		private static $compileIndex = 0;
		private $compileIndexActive;

		private $where_indexes = [];

		protected $compiled_query = '';
		protected $compiled_params = [];
		protected $server;

		public function __construct(Server $server){
			self::$compileIndex++;
			$this->compileIndexActive = hash('crc32', self::$compileIndex);
			$this->server = $server;
		}

		public function reset(){
			return new self($this->server);
		}

		protected function compile(){
			$this->compiled_query .= 'UPDATE';

			$holder = [];
			foreach($this->table as $data){
				$data[0] = $this->clean($data[0]);

				if($data[1] !== null){
					$data[1] = $this->clean($data[1]);
					$holder[] = $data[0].' AS '.$data[1].'';
				}else{
					$holder[] = $data[0];
				}
			}

			$this->compiled_query .= ' '.implode(', ',$holder);

			foreach($this->joins as $joinIndex => $joinData){
				list($table, $alias, $format) = $joinData;

				$format = strtoupper($format);
				$table = $this->clean($table);

				if($alias !== null){
					$alias = $this->clean($alias);
					$this->compiled_query .= ' '.$format.($format?' ':'').'JOIN '.$table.' AS '.$alias;
				}else{
					$this->compiled_query .= ' '.$format.($format?' ':'').'JOIN '.$table;
				}

				if(isset($this->on[$joinIndex])){
					$holder = '';
					foreach($this->on[$joinIndex] as $on){
						list($field1, $operator, $field2, $statement) = $on;
						$holder .= ($holder ? ' '.$statement : 'ON');

						$field1 = $this->clean($field1);
						$field2 = $this->clean($field2);

						$holder .= ' '.$field1.' '.$operator.' '.$field2;
					}

					$this->compiled_query .= ' '.$holder;
				}
			}

			$holder = [];

			foreach($this->set as $set){
				list($column, $operator, $value, $rawValue) = $set;

				if($rawValue){
					$key = $value;
				}else{
					$key = ':c'.$this->compileIndexActive.'v'.hash('crc32', count($this->compiled_params));
					$this->compiled_params[$key] = $value;
				}

				$column = $this->clean($column);
				$holder[] = $column. ' '.$operator.' '.$key;
			}

			if(!count($holder)){
				throw new Exception('UPDATE build failed, no SET parameters given');
			}

			$this->compiled_query .= ' SET '.implode(', ', $holder);

			$where = 0;
			$where_in_values = 0;
			$where_in_subquery = 0;

			$holder = '';
			$was_change = true;

			foreach($this->where_indexes as $windex){
				if($windex == 'and_open' || $windex == 'or_open'){
					$holder .= ($holder?($windex == 'and_open'?' AND ':' OR '):'').'(';
					$was_change = true;
					continue;
				}elseif($windex == 'close'){
					$holder .= ')';
					continue;
				}elseif($windex == 'where'){
					list($field, $operator, $value, $format, $values2, $splitter) = $this->$windex[$$windex++];
					$holder .= ($was_change?'':' '.$splitter.' ');
					$was_change = false;

					$field = $this->clean($field);
					$key = ':c'.$this->compileIndexActive.'v'.hash('crc32', count($this->compiled_params));
					$this->compiled_params[$key] = $value;
					$holder .= $field.' '.$operator.' '.$key;

					if($format !== null){
						$holder .= ' '.$format;
					}

					if($values2 !== null){
						$key = ':c'.$this->compileIndexActive.'v'.hash('crc32', count($this->compiled_params));
						$this->compiled_params[$key] = $values2;
						$holder .= ' '.$key;
					}
				}elseif($windex == 'where_in_values'){
					list($field, $values, $splitter, $prefix) = $this->$windex[$$windex++];
					$field = $this->clean($field);

					$holder .= ($was_change?'':' '.$splitter.' ');
					$was_change = false;

					$holder .= $field.($prefix?' '.$prefix:'').' IN(';

					foreach($values as $index => $value){
						if($index){
							$holder .= ', ';
						}
						$key = ':c'.$this->compileIndexActive.'v'.hash('crc32', count($this->compiled_params));
						$this->compiled_params[$key] = $value;
						$holder .= $key;
					}
					$holder .= ')';
				}elseif($windex == 'where_in_subquery'){
					list($field, $value, $splitter, $prefix) = $this->$windex[$$windex++];
					$field = $this->clean($field);

					$holder .= ($was_change?'':' '.$splitter.' ');
					$was_change = false;

					$holder .= $field.($prefix?' '.$prefix:'').' IN('.$value.')';
				}
			}

			if($holder){
				$this->compiled_query .= ' WHERE '.$holder;
			}

			if(count($this->group_by)){
				$this->compiled_query .= ' GROUP BY';
				$holder = '';
				foreach($this->group_by as $column){
					$column = $this->clean($column);
					$holder .= ($holder?', ':'').$column;
				}
				$this->compiled_query .= ' '.$holder;
			}

			if(count($this->order_by)){
				$this->compiled_query .= ' ORDER BY';
				$holder = '';
				foreach($this->order_by as $column){
					list($name, $direction) = $column;

					$name = $this->clean($name);
					$holder .= ($holder?', ':'').$name.' '.strtoupper($direction);
				}
				$this->compiled_query .= ' '.$holder;
			}

			if($this->offset !== null && $this->limit !== null){
				$this->compiled_query .= ' LIMIT '.strval($this->offset).','.strval($this->limit);
			}elseif($this->offset !== null){
				$this->compiled_query .= ' OFFSET '.strval($this->offset);
			}elseif($this->limit !== null){
				$this->compiled_query .= ' LIMIT '.strval($this->limit);
			}
		}

		private $table = [];

		public function table($name, $alias = null){
			$this->table[] = [$name, $alias];
			return $this;
		}

		private $joins = [];

		public function join($table, $alias = null, $format = 'LEFT'){
			if(!$format){
				$format = '';
			}

			$this->joins[] = [$table, $alias, $format];

			return $this;
		}

		private $on = [];

		public function on($field1, $operator, $field2, $format = 'AND'){
			if(!count($this->joins)){
				throw new Exception('Cannot call query builders ON if there was no join initiated.');
			}

			$joinIndex = count($this->joins)-1;

			if(!isset($this->on[$joinIndex])){
				$this->on[$joinIndex] = [];
			}

			$this->on[$joinIndex][] = [$field1, $operator, $field2, strtoupper($format)];

			return $this;
		}

		private $where = [];

		public function where($field, $operator, $value, $format = null, $values2 = null, $splitter = 'AND'){
			$this->where_indexes[] = 'where';
			$this->where[] = [$field, $operator, $value, $format, $values2, $splitter];
			return $this;
		}

		public function or_where($field, $operator, $value, $format = null, $values2 = null){
			return $this->where($field, $operator, $value, $format, $values2, 'OR');
		}

		public function and_where($field, $operator, $value, $format = null, $values2 = null){
			return $this->where($field, $operator, $value, $format, $values2, 'AND');
		}

		public function open(){
			$this->where_indexes[] = 'and_open';
			return $this;
		}

		public function or_open(){
			$this->where_indexes[] = 'or_open';
			return $this;
		}

		public function close(){
			$this->where_indexes[] = 'close';
			return $this;
		}

		private $where_in_values = [];

		public function where_in_values($field, array $values, $splitter = 'AND', $prefix = ''){
			if(!count($values)){
				throw new Exception('The WHERE_IN_VALUES requires $values array to have values.');
			}

			$this->where_indexes[] = 'where_in_values';
			$this->where_in_values[] = [$field,$values,$splitter,$prefix];
			return $this;
		}

		public function or_where_in_values($field, array $values){
			return $this->where_in_values($field, $values, 'OR');
		}

		public function and_where_in_values($field, array $values){
			return $this->where_in_values($field, $values, 'AND');
		}

		public function where_not_in_values($field, array $values, $splitter = 'AND'){
			return $this->where_in_values($field, $values, $splitter, 'NOT');
		}

		public function and_where_not_in_values($field, array $values){
			return $this->where_in_values($field, $values, 'AND', 'NOT');
		}

		public function or_where_not_in_values($field, array $values){
			return $this->where_in_values($field, $values, 'OR', 'NOT');
		}

		private $where_in_subquery = [];

		public function where_in_subquery($field, $value, $splitter = 'AND', $prefix = ''){
			if(!is_string($value)){
				if($value instanceof Select){
					$value = $value->debug(true);
					$this->compiled_params = array_merge($this->compiled_params, $value['parameters']);
					$value = $value['query'];
				}else{
					throw new Exception('WHERE IN SUBQUERY requires either a raw sql query string or a SELECT builder that has not been executed.');
				}
			}

			$this->where_indexes[] = 'where_in_subquery';
			$this->where_in_subquery[] = [$field, $value, $splitter, $prefix];
			return $this;
		}

		public function and_where_in_subquery($field, $value){
			return $this->where_in_subquery($field, $value);
		}

		public function or_where_in_subquery($field, $value){
			return $this->where_in_subquery($field, $value, 'OR');
		}

		public function where_not_in_subquery($field, $value){
			return $this->where_in_subquery($field, $value, 'AND', 'NOT');
		}

		public function and_where_not_in_subquery($field, $value){
			return $this->where_in_subquery($field, $value, 'AND', 'NOT');
		}

		public function or_where_not_in_subquery($field, $value){
			return $this->where_in_subquery($field, $value, 'OR', 'NOT');
		}

		private $set = [];

		public function set($column, $operator, $value, $rawValue = false){
			$this->set[] = [$column, $operator, $value, $rawValue];
		}

	}
