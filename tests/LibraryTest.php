<?php

	if(!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
		class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
	}

	class LibraryTest extends \PHPUnit_Framework_TestCase {

		private $KML;

		private function generateLibrary(){
			if(!$this->KML instanceof \Kodols\MySQL\Library){
				$this->KML = new \Kodols\MySQL\Library;
			}
		}

		private function installBasicConfig($id){
			$this->generateLibrary();
			$this->KML->attachConfiguration($this->KML->newConfiguration(), $id);
		}

		public function testConfigurationObject(){
			$this->generateLibrary();

			$config = $this->KML->newConfiguration();

			$this->assertInstanceOf(\Kodols\MySQL\Configuration::class, $config);
			
			$config->setDatabase('unittest');
			$this->assertEquals('unittest', $config->getDatabase());

			$this->assertInstanceOf(\Kodols\MySQL\Library::class, $this->KML->attachConfiguration($config, 'testConfigurationObject'));
		}

		public function testAbilityToExecuteNativeMethodsFromMainObject(){
			$this->installBasicConfig('testAbilityToExecuteNativeMethodsFromMainObject');
			$server = $this->KML->connect('testAbilityToExecuteNativeMethodsFromMainObject');

			$this->assertTrue(method_exists($server, 'beginTransaction'));
			$this->assertTrue(method_exists($server, 'commit'));
			$this->assertTrue(method_exists($server, 'errorCode'));
			$this->assertTrue(method_exists($server, 'errorInfo'));
			$this->assertTrue(method_exists($server, 'exec'));
			$this->assertTrue(method_exists($server, 'getAttribute'));
			$this->assertTrue(method_exists($server, 'getAvailableDrivers'));
			$this->assertTrue(method_exists($server, 'inTransaction'));
			$this->assertTrue(method_exists($server, 'lastInsertId'));
			$this->assertTrue(method_exists($server, 'prepare'));
			$this->assertTrue(method_exists($server, 'query'));
			$this->assertTrue(method_exists($server, 'quote'));
			$this->assertTrue(method_exists($server, 'rollBack'));
			$this->assertTrue(method_exists($server, 'setAttribute'));
		}

		public function testAbilityToExecuteHelperMethodsFromMainObject(){
			$this->installBasicConfig('testAbilityToExecuteHelperMethodsFromMainObject');
			$server = $this->KML->connect('testAbilityToExecuteHelperMethodsFromMainObject');

			$this->assertTrue(method_exists($server, 'insert'));
			$this->assertTrue(method_exists($server, 'ignore'));
			$this->assertTrue(method_exists($server, 'replace'));
			$this->assertTrue(method_exists($server, 'update'));
			$this->assertTrue(method_exists($server, 'delete'));
		}

		public function testBuilderInitialisation(){
			$this->installBasicConfig('testBuilderInitialisation');
			$server = $this->KML->connect('testBuilderInitialisation');

			$this->assertInstanceOf(\Kodols\MySQL\Builder\Delete::class, $server->build('delete'));
			$this->assertInstanceOf(\Kodols\MySQL\Builder\Update::class, $server->build('update'));
			$this->assertInstanceOf(\Kodols\MySQL\Builder\Select::class, $server->build('select'));
			$this->assertInstanceOf(\Kodols\MySQL\Builder\Insert::class, $server->build('insert'));
			$this->assertInstanceOf(\Kodols\MySQL\Builder\Ignore::class, $server->build('ignore'));
			$this->assertInstanceOf(\Kodols\MySQL\Builder\Replace::class, $server->build('replace'));
		}
		
	}