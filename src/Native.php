<?php

	/*
	 * This class is an alias to provide MySQL library with generic PDO functionality
	 * http://php.net/manual/en/class.pdo.php
	 */

	namespace Kodols\MySQL;

	trait Native {

		// http://php.net/manual/en/pdo.begintransaction.php
		public function beginTransaction(){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[beginTransaction]', '');
			}
			return $this->createPDO()->beginTransaction();
		}

		// http://php.net/manual/en/pdo.commit.php
		public function commit(){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[commit]', '');
			}
			return $this->createPDO()->commit();
		}

		// http://php.net/manual/en/pdo.errorcode.php
		public function errorCode(){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[errorCode]', '');
			}
			return $this->createPDO()->errorCode();
		}

		// http://php.net/manual/en/pdo.errorinfo.php
		public function errorInfo(){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[errorInfo]', '');
			}
			return $this->createPDO()->errorInfo();
		}

		// http://php.net/manual/en/pdo.exec.php
		public function exec($statement){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[errorInfo]', $statement);
			}
			return $this->createPDO()->exec($statement);
		}

		// http://php.net/manual/en/pdo.getattribute.php
		public function getAttribute($attribute){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[getAttribute]', $name);
			}
			return $this->createPDO()->getAttribute($name);
		}

		// http://php.net/manual/en/pdo.getavailabledrivers.php
		public function getAvailableDrivers(){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[getAvailableDrivers]', '');
			}
			return $this->createPOD()->getAvailableDrivers();
		}

		// http://php.net/manual/en/pdo.intransaction.php
		public function inTransaction(){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[inTransaction]', '');
			}
			return $this->createPDO()->inTransaction();
		}

		// http://php.net/manual/en/pdo.lastinsertid.php
		public function lastInsertId($name = null){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[lastInsertId]', $name);
			}
			return $this->createPDO()->lastInsertId($name);
		}

		// http://php.net/manual/en/pdo.prepare.php
		public function prepare($statement, $driver_options = []){
			if($this->isLogEnabled()){
				$this->logQuery('native-pdo[prepare]', [$statement, $driver_options]);
			}
			return $this->createPDO()->prepare($statement, $driver_options);
		}

		// http://php.net/manual/en/pdo.query.php
		public function query($statement, $mode = null, $info1 = null, $info2 = null){
			$pdo = $this->createPDO();
			$options = [$statement];

			if($mode !== null){
				$options[] = $mode;
			}

			if($info1 !== null){
				$options[] = $info1;
			}

			if($info2!== null){
				$options[] = $info2;
			}

			$this->logQuery('native-pdo[query]', $options);

			return call_user_func_array([$pdo, 'query'], $options);
		}

		// http://php.net/manual/en/pdo.quote.php
		public function quote($string, $parameter_type = null){
			$pdo = $this->createPDO();
			$options = [$string];

			if($parameter_type !== null){
				$options[] = $parameter_type;
			}

			$this->logQuery('native-pdo[quote]', $options);

			return call_user_func_array([$pdo, 'quote'], $options);
		}

		// http://php.net/manual/en/pdo.rollback.php
		public function rollBack(){
			$this->logQuery('native-pdo[rollBack]', '');
			return $this->createPDO()->rollBack();
		}

		// http://php.net/manual/en/pdo.setattribute.php
		public function setAttribute($attribute, $value){
			$this->logQuery('native-pdo[setAttribute]', [$attribute,$value]);
			return $this->createPDO()->setAttribute($attribute, $value);
		}

	}