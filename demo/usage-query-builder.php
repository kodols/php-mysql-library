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

//	SELECT
//		*
//	FROM
//		users
$db->build('select')
    ->from('users')
    ->execute();

//	SELECT
//		username
//	FROM
//		users
$db->build('select')
    ->column('username')
    ->from('users')
    ->execute();

//	SELECT
//		a.username
//	FROM
//		users a
//	RIGHT JOIN projects b
//		ON b.user = a.id
$db->build('select')
    ->column('a.username')
    ->from('users', 'a')
    ->join('projects', 'b', 'right')
    ->on('b.user', '=', 'a.id')
    ->execute();

//	SELECT
//		a.username,
//		(
//			SELECT
//				count(0)
//			FROM
//				projects b
//			WHERE
//				b.id = a.id
//		)
//	FROM
//		users a
$db->build('select')
    ->column('a.username')
    ->subquery(
        $db->build('select')
            ->raw_column('count(0)')
            ->from('projects', 'b')
            ->raw_where('b.id', '=', 'a.id')
    )
    ->from('users', 'a')
    ->execute();

//	SELECT
//		*
//	FROM
//		business
//	WHERE
//		related IN("4","3","1")
$db->build('select')
    ->from('business')
    ->where_in_values('related', [4, 3, 1])
    ->execute();

//	SELECT
//		a.name
//	FROM
//		users a
//	WHERE
//		a.id IN(
//			SELECT
//				b.user
//			FROM
//				projects b
//			WHERE
//				b.validated = "yes"
//		)
$db->build('select')
    ->column('a.name')
    ->from('users', 'a')
    ->where_in_subquery(
        'a.id',
        $db->build('select')
            ->column('b.user')
            ->from('projects', 'b')
            ->where('b.validated', '=', 'yes')
    )
    ->execute();

//	SELECT
//		id
//	FROM
//		users
//	WHERE
//		(
//			created BETWEEN "1485229920" AND "1485249932"
//			OR validated = "yes"
//		) AND (
//			activated = "yes"
//			OR (
//				validated = "yes" AND
//				single = "no"
//			)
//		)
$db->build('select')
    ->column('id')
    ->from('users')
    ->open()
    ->where('created', 'between', '1485229920', 'and', '1485249932')
    ->or_where('validated', '=', 'yes')
    ->close()
    ->open()
    ->where('activated', '=', 'yes')
    ->or_open()
    ->where('validated', '=', 'yes')
    ->where('single', '=', 'no')
    ->close()
    ->close()
    ->execute();

//	UPDATE
//		users
//	SET
//		username = "ed",
//		update_count = update_count + 1
//	WHERE
//		id = 1234
$db->build('update')
    ->table('users')
    ->set('username', '=', 'ed')
    ->set('update_count', '=', 'update_count + 1', true)
    ->where('id', '=', '1234')
    ->execute();

//	UPDATE
//		original a
//	INNER JOIN updated b
//			ON a.URL = b.URL
//	SET a.funded = b.funded,
//			a.days = b.days
$db->builder('update')
    ->table('original', 'a')
    ->join('updated', 'b', 'innser')
    ->on('a.URL', '=', 'b.URL')
    ->set('a.funded', '=', 'b.funded', true)
    ->set('a.days', '=', 'b.days', true)
    ->execute();

//	DELETE posts
//	FROM posts
//	INNER JOIN projects ON projects.project_id = posts.project_id
//	WHERE projects.client_id = "312"
$db->builder('delete')
    ->column('posts')
    ->from('posts')
    ->join('projects')
    ->on('projects.project_id', '=', 'posts.project_id')
    ->where('projects.client_id', '=', "312")
    ->execute();

//	INSERT INTO
//		table
//	(username, password)
//	VALUES("ed","w3lcome")
$insert_id = $db->builder('insert')
    ->into('table')
    ->set('username', 'ed')
    ->set('password', 'w3lcome')
    ->execute();

//	INSERT IGNORE INTO
//		table
//	(username, password)
//	VALUES("ed","w3lcome")
$insert_id = $db->builder('ignore')
    ->into('table')
    ->set('username', 'ed')
    ->set('password', 'w3lcome')
    ->execute();

//	REPLACE INTO
//		table
//	VALUES("ed","w3lcome")
$insert_id = $db->builder('replace')
    ->into('table')
    ->value('ed')
    ->value('w3lcome')
    ->execute();
