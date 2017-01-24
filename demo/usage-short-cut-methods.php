<?php

	/*
	 * Integrate the library
	 */
	use \Kodols\MySQL\Library;
	$KML = new Library;

	$config = $KML->newConfiguration();
	$config->setDatabase('project');

	$KML->attachConfiguration($config);

	/*
	 * Get the server object
	 */
	$db = $KML->connect();

	/*
	 * INSERT INTO users (username, password) VALUES("ed","w3lcome")
	 */
	$insert_id = $db->insert('users', [
		'username' => 'ed',
		'password' => 'w3lcome'
	]);

	/*
	 * INSERT IGNORE INTO users (username, password) VALUES("ed","w3lcome")
	 */
	$insert_id = $db->ignore('users', [
		'username' => 'ed',
		'password' => 'w3lcome'
	]);

	/*
	 * REPLACE INTO users (username, password) VALUES("ed","w3lcome")
	 */
	$insert_id = $db->replace('users', [
		'username' => 'ed',
		'password' => 'w3lcome'
	]);

	/*
	 * DELETE FROM users WHERE username = "ed" AND password = "w3lcome"
	 */
	$db->delete('users', [
		'username' => 'ed',
		'password' => 'w3lcome'
	]);

	/*
	 * UPDATE users SET username = "edward" WHERE username = "ed" AND password = "w3lcome"
	 */
	$db->update('users', [
		'username' => 'edward'
	],[
		'username' => 'ed',
		'password' => 'w3lcome'
	]);
