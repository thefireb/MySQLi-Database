<?php

/**
 * Require class file.
 */
require_once dirname(__FILE__) . '/mysqli-database.php';

/**
 * Setup database connection
 * @var object
 */
$db = MySQLi_Handler::getInstance([
    'hostname' => 'localhost', 
    'username' => 'homestead', 
    'password' => 'secret', 
    'database' => 'examples', 
]);

/**
 * Check if we have a connection
 */
if ($db->isConnected()) {
    
    /**
     * Check if array data exists.
     */
    if ($db->exists($data)) {
        
        /**
         * Results with optional parameters.
         */
        $db->results(2, 'assoc');
    
    }

    /**
     * Insert new data to database
     */
    if ($db->insert($data)) {
        
        /**
         * Echo number of row inserted.
         */
        echo $db->affected();

    }

    /**
     * Update data on the database.
     */
    if ($db->update($data)) {
        
        /**
         * Echo number of row updated.
         */
        echo $db->affected();

    }

    /**
     * Delete data from the database.
     */
    if ($db->delete($data)) {
        
        /**
         * Echo number of row deleted.
         */
        echo $db->affected();

    }

} else {

    /**
     * Echo database error message.
     */
    echo $db->getMessage();

}