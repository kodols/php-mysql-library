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
 * Start using generic PDO functions as per PHP documentation.
 */

// http://php.net/manual/en/pdo.prepare.php
$res = $db->prepare('SELECT * FROM users WHERE email LIKE ?');
$res->execute([
    '%ed%'
]);

var_dump([
    'count' => $res->rowCount(),
    'one_row' => $res->fetch(),
    'all_rows' => $res->fetchAll()
]);

// http://php.net/manual/en/pdo.query.php
$res = $db->query('SELECT * FROM users');

var_dump([
    'count' => $res->rowCount(),
    'one_row' => $res->fetch(),
    'all_rows' => $res->fetchAll()
]);

// etc.., full method view is available at http://php.net/manual/en/class.pdo.php