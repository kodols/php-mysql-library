<?php

	/*
	 * Step 1:
	 * Once you have successfully installed the composer package
	 * you may call the library as follows:
	 */

	use \Kodols\MySQL\Library;
	$KML = new Library;

	/*
	 * Step 2:
	 * Create a connection
	 *
	 * setPort(integer) - default value "3306"
	 * setHostname(string) - default value "localhost"
	 * setUsername(string) - default value "root"
	 * setPassword(string) - default value ""
	 * setDatabase(string) - default value ""
	 * setTimeout(integer) - default value "30"
	 * setPersistent(boolean) - default value "false"
	 * setEncoding(string) - default value "utf-8"
	 * setLog(boolean) - default value "true"
	 */

	$config = $KML->newConfiguration();
	$config->setPassword('secret');
	$config->setDatabase('project');

	/*
	 * Step 3:
	 * Attach the configuration to the library via
	 * attachConfiguration method
	 *
	 * argument 0: configuration object
	 * argument 1: configuration identifier - default value "default"
	 */
	$KML->attachConfiguration($config, 'demo');

	/*
	 * Optional Step 4:
	 * If your project has multiple database endpoints you may
	 * add more than one configuration to the library.
	 * If your configuration is similar to previously fiven one
	 * you may just edit the one created before as that would not
	 * affect already attached configurations. Alternatively, create
	 * a new configuration object via newConfiguration() method
	 */
	$config->setDatabase('projet_live');
	$KML->attachConfiguration($config, 'live');

	/*
	 * Step 5:
	 * You are all setup, start using the library
	 */

	// to access the "demo" database:
	$demo = $KML->connect('demo');
	// to access the "live" database:
	$live = $KML->connect('live');
	// to access the "default" database:
	$default = $KML->connect();
