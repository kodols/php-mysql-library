<?php

	/*
	 * This class is an alias to provide MySQL library with generic PDO functionality
	 * http://php.net/manual/en/class.pdo.php
	 */

	namespace Kodols\MySQL;

	trait Native {

		// http://php.net/manual/en/pdo.begintransaction.php
		public function beginTransaction(){
			return $this->createPDO()->beginTransaction();
		}

		// http://php.net/manual/en/pdo.commit.php
		public function commit(){
			return $this->createPDO()->commit();
		}

		// http://php.net/manual/en/pdo.errorcode.php
		public function errorCode(){
			return $this->createPDO()->errorCode();
		}

		// http://php.net/manual/en/pdo.errorinfo.php
		public function errorInfo(){
			return $this->createPDO()->errorInfo();
		}

		// http://php.net/manual/en/pdo.exec.php
		public function exec($statement){
			return $this->createPDO()->exec($statement);
		}

		// http://php.net/manual/en/pdo.getattribute.php
		public function getAttribute($attribute){
			return $this->createPDO()->getAttribute($name);
		}

		// http://php.net/manual/en/pdo.getavailabledrivers.php
		public function getAvailableDrivers(){
			return $this->createPOD()->getAvailableDrivers();
		}

		// http://php.net/manual/en/pdo.intransaction.php
		public function inTransaction(){
			return $this->createPDO()->inTransaction();
		}

		// http://php.net/manual/en/pdo.lastinsertid.php
		public function lastInsertId($name = null){
			return $this->createPDO()->lastInsertId($name);
		}

		// http://php.net/manual/en/pdo.prepare.php
		public function prepare($statement, $driver_options = []){
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

			return call_user_func_array([$pdo, 'query'], $options);
		}

		// http://php.net/manual/en/pdo.quote.php
		public function quote($string, $parameter_type = null){
			$pdo = $this->createPDO();
			$options = [$string];

			if($parameter_type !== null){
				$options[] = $parameter_type;
			}

			return call_user_func_array([$pdo, 'quote'], $options);
		}

		// http://php.net/manual/en/pdo.rollback.php
		public function rollBack(){
			return $this->createPDO()->rollBack();
		}

		// http://php.net/manual/en/pdo.setattribute.php
		public function setAttribute($attribute, $value){
			return $this->createPDO()->setAttribute($attribute, $value);
		}

	}