<?php

	namespace Kodols\MySQL;

	trait Helpers {

		private function __call_insert($table, $values, $type){
			$builder = $this->build($type)->into($table);

			foreach($values as $key => $value){
				if(is_numeric($key)){
					$builder->value($value);
				}else{
					$builder->set($key, $value);
				}
			}

			return $builder->execute();
		}

		public function insert($table, array $values = []){
			return $this->__call_insert($table, $values, 'insert');
		}

		public function ignore($table, array $values = []){
			return $this->__call_insert($table, $values, 'ignore');
		}

		public function replace($table, array $values = []){
			return $this->__call_insert($table, $values, 'replace');
		}

		public function delete($table, array $values = []){
			$builder = $this->build('delete')->from($table);

			foreach($values as $key => $value){
				$builder->where($key,'=',$value);
			}

			$builder->execute();
		}

		public function update($table, array $set = [], array $where = []){
			$builder = $this->build('update')->table($table);

			foreach($set as $key => $value){
				$builder->set($key, $value);
			}

			foreach($where as $key => $value){
				$builder->where($key,'=',$value);
			}

			$builder->execute();
		}

	}