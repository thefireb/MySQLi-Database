<?php

/**
 * Require main class file
 */
require_once dirname(__FILE__) . '/database.php';

/**
 * Setup database connection with Singleton pattern.
 * @var object
 */
$db = Database::getInstance([
    'hostname' => 'localhost',
    'username' => 'homestead',
    'password' => 'secret',
    'database' => 'examples',
]);

$user = [
    'table' => 'names',
    'lname' => 'Zouhir',
    'age'   => 25,
    /*'fname' => 'Yassine',
    'email' => 'yasiso@example.com',*/
];

if ($db->exists($user)) {
    foreach ($result = $db->results() as $key => $value) {
        if (is_array($result[$key])) {
            foreach ($value as $k => $v) {
                echo $k, ' -- ', $v, '<br>';
            }
            echo '<br>';
        } else {
            echo $key, ' -- ', $value, '<br>';
        }
    }
} else {
    var_dump($db->insert($user));
}

die();

/**
 * Make a simple query to the database
 * @var object
 */
if ($db->exists($user)) {
    foreach ($db->results('assoc') as $key => $value) {
        echo $key, " is ", $value, "<br>"; 
    }
} elseif (!$db->exists($user)) {
    $db->insert($user);

    if ($db->affected()) {
        echo 'User Added.';
    }
}