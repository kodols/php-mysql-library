<?php

	namespace Kodols\MySQL\Builder;

	use \Kodols\MySQL\Server;
	use \Kodols\MySQL\Builder;

	class Insert extends Builder {

		protected $method = 'INSERT INTO';
		protected $buildFormat = 'insert';

		private $table = '';
		private $values = [];
		protected $compiled_query = '';
		protected $compiled_params = [];
		protected $server;

		public function __construct(Server $server){
			$this->server = $server;
		}

		public function into($name){
			return $this->table($name);
		}

		public function table($name){
			$this->compiled = false;
			$this->table = $name;
			return $this;
		}

		public function set($column, $value){
			$this->compiled = false;
			$this->values[$column] = $value;
			return $this;
		}

		public function value($value){
			$this->compiled = false;
			$this->values[] = $value;
			return $this;
		}

		protected function compile(){
			$this->compiled_query = $this->method.' ';
			$this->compiled_params = [];

			$this->compiled_query.= $this->clean($this->table);

			$useColumnKeys = true;
			$columnKeys = '';
			$queryValues = '';

			foreach($this->values as $key => $value){
				if($useColumnKeys){
					if(is_numeric($key)) {
						$useColumnKeys = false;
					}else{
						$columnKeys .= ($columnKeys?',':'').$this->clean($key);
					}
				}

				$value_key = ':v'.count($this->compiled_params);
				$queryValues .= ($queryValues?',':'').$value_key;
				$this->compiled_params[$value_key] = $value;
			}

			if($useColumnKeys){
				$this->compiled_query .= ' ('.$columnKeys.')';
			}

			$this->compiled_query .= ' VALUES('.$queryValues.')';
			$this->compiled = true;
		}

		public function execute($keepParameters = false){
			parent::execute($keepParameters);
			return $this->server->lastInsertId();
		}

	}