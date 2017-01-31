<?php

	namespace Kodols\MySQL;

	class Builder {

		protected $compiled_query = '';
		protected $compiled_params = [];
		protected $server;
		protected $buildFormat;
		protected $compiled = false;

		// http://php.net/manual/en/pdostatement.fetch.php
		public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null){
			$options = [];
			
			if($fetch_style !== null){
				$options[] = $fetch_style;
			}
			
			if($cursor_orientation !== null){
				$options[] = $cursor_orientation;
			}

			if($cursor_offset !== null){
				$options[] = $cursor_offset;
			}

			$resource = $this->execute();

			return call_user_func_array([$resource,'fetch'], $options);
		}

		// http://php.net/manual/en/pdostatement.fetchall.php
		public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null){
			$options = [];
			
			if($fetch_style !== null){
				$options[] = $fetch_style;
			}
			
			if($fetch_argument !== null){
				$options[] = $fetch_argument;
			}

			if($ctor_args !== null){
				$options[] = $ctor_args;
			}

			$resource = $this->execute();

			return call_user_func_array([$resource,'fetchAll'], $options);
		}

		public function execute($keepParameters = false){
			if(!$this->compiled) {
				$this->compile();
			}

			if($this->server->isLogEnabled()){
				$this->server->logQuery($this->buildFormat, $this->debug());
			}

			$native = $this->server->getNativePdo();

			$resource = $native->prepare($this->compiled_query);
			$resource->execute($this->compiled_params);

			$this->compiled = false;
			$this->compiled_query = '';

			if(!$keepParameters) {
				$this->compiled_params = [];
			}

			return $resource;
		}

		public function debug($return_pdo = false){
			if(!$this->compiled){
				$this->compile();
			}

			$query = $this->compiled_query;

			foreach($this->compiled_params as $param_key => $param_value){
				if(!is_numeric($param_key)){
					$query = str_replace($param_key, '"'.$param_value.'"', $query);
				}
			}

			if($return_pdo){
				return [
					'query' => $this->compiled_query,
					'parameters' => $this->compiled_params
				];
			}

			return $query;
		}

		protected function clean($variable){
			$variable = str_replace(['`',' ',"\n","\r","\t"],'',$variable);

			if(strpos($variable,'.') !== false){
				return '`'.str_replace('.', '`.`', $variable).'`';
			}

			return '`'.$variable.'`';
		}

	}
