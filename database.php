<?php
/**
 * This class handles most used aspects when interacting with the MySQL database server
 * using Singleton pattern, that allows just one class instance
 *
 * The singleton pattern is useful when we need to make sure we only have a single
 * instance of a class for the entire request lifecycle in a web application.
 * This typically occurs when we have global objects (such as a Configuration class)
 * or a shared resource (such as an event queue).
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Anass Rahou
 * @package    MySQLi_Handler
 * @copyright  Copyright (c) 2017
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 * @version    1.0 Beta
 * @since      1.0
*/
class Database
{
    /**
     * MySQL connection link
     * @var object
     */
    private $_handler;
    
    /**
     * Holds the total number of records returned
     * @var string
     */
    private $_count = 0;

    /**
     * [$result description]
     * @var null
     */
    public $result = null;

    /**
     * Holds an array of the result
     * @var array
     */
    public $results = array();

    /**
     * Object instance link
     * @var object
     */
    private static $_instance = null;

    /**
     * Class constructor
     * 
     * @param array     $data Database information connection
     */
    private function __construct(array $data)
    {
        $this->_handler = @new mysqli(
            $data['hostname'],
            $data['username'],
            $data['password'],
            $data['database']
        );
    }

    /**
     * Make a unique instance of class, if not exists.
     * 
     * @param  array     $data    MySQL server connection information
     * @return object             Instance of unique object
     */
    public static function getInstance($data)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($data);
        }

        return self::$_instance;
    }

    /**
     * Check if key exists in an array.
     * 
     * @param  string   $key        Key to search
     * @param  array    $haystack   Array where to search
     * @return string               Return value
     */
    public function in_array($key, $haystack)
    {
        $keys = array_keys($haystack);

        foreach ($haystack as $k => $v) {
            if (in_array($key, $keys)) {
                return $haystack[$key];
            }
        }
        return array();
    }

    /**
     * Perform a select query to the database.
     * 
     * @param  array    $contents   Content to insert
     * @param  string   $operator   SQL Operator
     * @return object               Handler of class
     */
    public function select(array $contents, $operator = 'AND')
    {
        if ($this->in_array('table', $contents)) {
            
            $table = $contents['table'];
            unset($contents['table']);
        
        }

        $sql = "SELECT * FROM `$table`";

        if (count($contents) >= 1) {
            $sql .= " WHERE ";
        }

        $i = 0;
        foreach ($contents as $column => $content) {
            $i++;
            if (count($contents) != $i) {
                $sql .= "`$column` = '$content' $operator ";
            } else {
                $sql .= "`$column` = '$content'";
            }
        }

        return $this->query($sql);
    }

    /**
     * Main class query.
     * 
     * @param  string   $sql    SQL to execute
     * @return object           Handler of class
     */
    public function query($sql)
    {
        $this->result = $this->_handler->query($sql);

        if (is_object($this->result)) {
            $this->_count = $this->result->num_rows;
        }

        return $this;
    }

    /**
     * Results from query.
     * 
     * @param  string   $type   Type of array
     * @return array            Results array
     */
    public function results($type = 'both')
    {
        if ($this->count()) {

            switch ($type) {
                case 'assoc':
                case 'associative':
                    $type = MYSQLI_ASSOC;
                    break;

                case 'num':
                case 'numeral':
                case 'numeric':
                    $type = MYSQLI_NUM;
                    break;
                
                default:
                    $type = MYSQLI_BOTH;
                    break;
            }

            $this->results = $this->result->fetch_array($type);
        }

        return $this->results;
    }

    /**
     * Perform an insert query to the database.
     * 
     * @param  array    $contents   Content to insert
     * @return object               Handler of class
     */
    public function insert(array $contents)
    {
        if (count($contents)) {
            
            if ($this->in_array('table', $contents)) {
                
                $table = $contents['table'];
                unset($contents['table']);
            
            }

            $sql = "INSERT INTO `{$table}`";
            $sql .= " (" . implode(', ', array_keys($contents)) . ") VALUES";
            $sql .= " ('" . implode('\', \'', $contents) . "')";
        }

        return $this->query($sql);
    }

    /**
     * Perform an update query to the database.
     * 
     * @param  array    $contents     Content to update
     * @param  array    $conditons    Condition to meet
     * @return object                 Handler of class
     */
    public function update(array $contents, array $conditons)
    {
        if ($this->in_array('table', $contents)) {
            
            $table = $contents['table'];
            unset($contents['table']);
        
        }

        $sql = "UPDATE `$table` SET ";

        $i = 0;
        foreach ($contents as $column => $content) {
            $i++;
            
            if (count($contents) != $i) {
                $sql .= "`$column` = '$content', ";
            } else {
                $sql .= "`$column` = '$content'";
            }
        }

        $sql .= " WHERE ";

        foreach ($conditons as $column => $content) {
            $sql .= "`$column` = '$content'";
        }

        return $this->query($sql);
    }

    /**
     * Perform a delete query to the database.
     * 
     * @param  array      $conditions     Content to delete
     * @return object                     Handler of class
     */
    public function delete($conditions)
    {
        if (count($conditions)) {

            if ($this->in_array('table', $conditions)) {
                
                $table = $conditions['table'];
                unset($conditions['table']);
            
            }

            $sql = "DELETE FROM `$table` WHERE ";
            foreach ($conditions as $column => $content) {
                $sql .= "`$column` = '$content'";
            }

        }

        return $this->query($sql);
    }

    /**
     * Check if content exists in the database.
     * 
     * @param  array      $contents    Content to find
     * @param  string     $operator    SQL operator
     * @param  string     $type        Type of array
     * @return boolean                 Check if content exists
     */
    public function exists(array $contents, $operator = 'AND', $type = 'both')
    {
        if (is_object($this->select($contents, $operator))) {
            if (!empty($this->results($type))) {
                $this->select($contents, $operator);
                return true;
            }
        }
        return false;
    }

    /**
     * Get row count.
     * 
     * @return integer    Number    of results found
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Close database connection.
     */
    public function __destruct()
    {
        $this->_handler->close();
    }
}