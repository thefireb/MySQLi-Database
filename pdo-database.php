<?php

class PDOer
{

    /**
     * Object instance link.
     * @var object
     */
    private static $_instance = null;

    /**
     * Constructor
     */
    protected function __construct()
    {
        // ...
    }

    /**
     * Clone
     * @return string Advice message.
     */
    public function __clone()
    {
        die('Cheatin&#8217; huh?');
    }

    /**
     * Make a unique instance of class, if not exists.
     * 
     * @param  string $host     Host name
     * @param  string $database database name
     * @param  string $user     databases user name
     * @param  string $password database user password
     * @param  array  $options  PDO options
     * @return object           Class object
     */
    public static function getInstance($host, $database, $user, $password = null, $options = [])
    {
        if (null === self::$_instance) {
            
            $dsn = "mysql:host=$host;dbname=$database";
            self::$_instance = new PDO($dsn, $user, $password, $options);

            self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$_instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if (count($options)) {
                foreach ($options as $key => $option) {
                    self::$_instance->setAttribute($key, $option);
                }
            }
        }

        return new static;
    }

    /**
     * Perform a query to the database.
     * 
     * @param  string $sql  Database sql query
     * @param  array  $args Content to query
     * @return object       PDOStatement class
     */
    public static function get($sql, $args = array())
    {
        if (!count($args)) {
            $stmt = self::$_instance->query($sql);
        } elseif (is_array($args) && count($args)) {
            $stmt = self::$_instance->prepare($sql);
            $stmt->execute($args);
        }

        return $stmt;
    }
}