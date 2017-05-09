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

    public $error = false;

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

        /**
         * If no connection, then setup an error message.
         */
        if ($this->_handler->connect_errno) {
            $this->error = $this->_handler->connect_error;
        }
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
     * Check database connection.
     * 
     * @return boolean    True if connection exists.
     */
    public function isConnected()
    {
        if ($this->error === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Show a connection error message.
     * 
     * @return string    Error message
     */
    public function errorMessage()
    {
        return $this->error;
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
    public function select(array $contents, $operator = 'AND', $types = array())
    {
        if ($this->in_array('table', $contents)) {
            
            $table = $contents['table'];
            unset($contents['table']);
        
        }

        $contents = $this->escape($contents, $types);

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
        if (!$this->isConnected()) {
            return false;
        }

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
    public function insert(array $contents, $types = array())
    {
        if (count($contents)) {
            
            if ($this->in_array('table', $contents)) {
                
                $table = $contents['table'];
                unset($contents['table']);
            
            }

            $contents = $this->escape($contents, $types);

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
    public function update(array $contents, array $conditons, $types = array())
    {
        if ($this->in_array('table', $contents)) {
            
            $table = $contents['table'];
            unset($contents['table']);
        
        }

        $contents = $this->escape($contents, $types);

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
    public function delete($conditions, $types = array())
    {
        if (count($conditions)) {

            if ($this->in_array('table', $conditions)) {
                
                $table = $conditions['table'];
                unset($conditions['table']);
            
            }

            $conditions = $this->escape($conditions, $types);

            $sql = "DELETE FROM `$table` WHERE ";
            $i = 0;
            foreach ($conditions as $column => $content) {
                $i++;
                $sql .= "`$column` = '$content' AND ";
                if (count($conditions) == $i) {
                    $sql .= "`$column` = '$content'";
                }
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

    private function cleaner($data, $type = '')
    {
        switch($type) {
            case 'none':
                // useless do not reaffect just do nothing
                //$data = $data;
                break;
            case 'str':
            case 'string':
                settype( $data, 'string');
                break;
            case 'int':
            case 'integer':
                settype( $data, 'integer');
                break;
            case 'float':
                settype( $data, 'float');
                break;
            case 'bool':
            case 'boolean':
                settype( $data, 'boolean');
                break;
            // Y-m-d H:i:s
            // 2014-01-01 12:30:30
            case 'datetime':
                $data = trim( $data );
                $data = preg_replace('/[^\d\-: ]/i', '', $data);
                preg_match( '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2})$/', $data, $matches );
                $data = $matches[1];
                break;
            case 'ts2dt':
                settype( $data, 'integer');
                $data = date('Y-m-d H:i:s', $data);
                break;
            // bonus types
            case 'hexcolor':
                preg_match( '/(#[0-9abcdef]{6})/i', $data, $matches );
                $data = $matches[1];
                break;
            case 'email':
                $data = filter_var($data, FILTER_VALIDATE_EMAIL);
                break;
            default:
                break;
        }
        return $data;
    }

    public function escape($data, $types = array())
    {
        if (is_array($data)) {
            
            if (!is_array($types) && is_string($types) && mb_strlen($types) >= 3) {
                $types = explode('|', $types);
            }

            $i = 0;
            foreach ($data as $key => $value) {
                if (!is_array($data[$key])) {
                    $data[$key] = $this->cleaner($data[$key], count($types) ? $types[$i] : $types);
                    $data[$key] = $this->_handler->real_escape_string($data[$key]);
                }
                $i++;
            }

        } else {
            $data = $this->cleaner($data);
            $data = $this->_handler->real_escape_string($data);
        }

        return $data;
    }

    /**
     * Close database connection.
     */
    public function __destruct()
    {
        /**
         * Check if has an active connection.
         */
        if (!$this->isConnected()) {
            return;
        }

        /**
         * Close mysqli connection.
         */
        $this->_handler->close();
    }
}