<?php

/**
 * Require main class file
 */
require_once dirname(__FILE__) . '/mysqli-database.php';

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

/**
 * Check if connected to the database.
 */
if ($db->isConnected()) {
    
    $content = [
        'table' => 'names',
        'fname' => 'Anass',
        'lname' => 'Rahou',
        'age'   => 28,
        'email' => 'anass@example.com',
    ];

    $conditions = [
        'id'    => 28,
        'email' => 'anass@example.com',
    ];

    /**
     * Check if have results.
     */
    if ($db->exists($content)) {

        /**
         * Foreach result content.
         */
        foreach ($db->results('assoc') as $key => $value) {
            echo $value, ", ";
        }

    } else {

        /**
         * Insert content.
         */
        $db->insert($content);

    }

    /**
     * Update database content.
     */
    if ($db->update($content, $conditions)) {

        echo 'User has been updated.';

    }

    /**
     * Find fname value and delete row.
     */
    $delete = [
        'table' => 'names',
        'fname' => 'Anass',
    ];

    /**
     * Delete from 'names' where 'fname' = 'Anass'.
     */
    if ($db->delete($delete)) {

        echo 'User has been deleted.';

    }

} else {

    /**
     * Fire Database error message.
     */
    echo $db->errorMessage();
}