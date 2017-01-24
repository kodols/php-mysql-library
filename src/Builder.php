<?php

	namespace Kodols\MySQL;

	class Builder {

		protected $compiled_query = '';
		protected $compiled_params = [];
		protected $server;
		protected $buildFormat;
		protected $compiled = false;

		public function execute(){
			if(!$this->compiled) {
				$this->compile();
			}

			if($this->server->isLogEnabled()){
				$this->server->logQuery($this->buildFormat, $this->debug());
			}

			$resource = $this->server->prepare($this->compiled_query);
			$resource->execute($this->compiled_params);

			$this->compiled = false;
			$this->compiled_query = '';
			$this->compiled_params = [];

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
