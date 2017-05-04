<?php

/**
 * Require main class file
 */
require_once dirname(__FILE__) . '/mysqli-database.php';

/**
 * Setup database connection with Singleton pattern.
 * @var object
 */
$db = MySQLi_Handler::getInstance([
    'hostname' => 'localhost',
    'username' => 'homestead',
    'password' => 'secret',
    'database' => 'anass',
]);

/**
 * Make a simple query to the database
 * @var object
 */
$result = $db->query("SELECT * FROM names");

var_dump($result->results('assoc'));