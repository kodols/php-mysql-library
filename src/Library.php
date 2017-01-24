<?php

	namespace Kodols\MySQL;

	use \Kodols\MySQL\Configuration;
	use \Kodols\MySQL\Server;
	use Exception;

	class Library {

		public function newConfiguration(){
			return new Configuration;
		}

		private static $configurationDetails = [];

		public function attachConfiguration(Configuration $object, $id = 'default', $overwrite = false){
			if($id === ''){
				throw new Exception('Configuration ID must have a value.');
			}

			if(isset(self::$configurationDetails[$id]) && !$overwrite){
				throw new Exception('Trying to attach a configuration that already exists. If this is intended please provide $overwrite as TRUE.');
			}

			self::$configurationDetails[$id] = $object->attach();

			return $this;
		}

		private static $connections = [];

		public function connect($connection_id = 'default'){
			if(!isset(self::$connections[$connection_id])){
				if(!isset(self::$configurationDetails[$connection_id])){
					throw new Exception('Provided database connection id "'.$connection_id.'" has not been attached');
				}
				self::$connections[$connection_id] = new Server(self::$configurationDetails[$connection_id]);
			}

			return self::$connections[$connection_id];
		}

	}