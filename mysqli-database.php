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
 * @copyright  Copyright (c) 2016 - 2017
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 * @version    2.1
 * @since      1.0
*/
class MySQLi_Handler
{
    /**
     * Object instance link
     * @var object
     */
    private static $_instance;

    /**
     * Holds the last error
     * @var string
     */
    public  $error;
    
    /**
     * Holds the MySQL query result
     * @var string
     */
    public  $result;
    
    /**
     * Holds the total number of records returned
     * @var string
     */
    public  $count;           
    
    /**
     * Holds the total number of records affected
     * @var string
     */
    public  $affected;
    
    /**
     * Holds raw 'arrayed' results
     * @var array
     */
    public  $rawResults;
    
    /**
     * Holds an array of the result
     * @var array
     */
    public  $results;
    
    /**
     * MySQL host name
     * @var string
     */
    private $hostname;

    /**
     * MySQL user name
     * @var string
     */
    private $_username;          
    
    /**
     * MySQL password
     * @var string
     */
    private $_password;
    
    /**
     * MySQL database name
     * @var string
     */
    private $_database;
    
    /**
     * MySQL connection link
     * @var object
     */
    private $_handler;

    private $_errors = array();

    /**
     * Class constructor
     * 
     * @param array     $data Database information connection
     */
    private function __construct($data)
    {
        $this->_hostname = $data['hostname'];
        $this->_username = $data['username'];
        $this->_password = $data['password'];
        $this->_database = $data['database'];

        $this->handler = @new mysqli(
            $this->_hostname,
            $this->_username,
            $this->_password,
            $this->_database
        );

        try {
            // Throw an error message if not connected
            if ($this->handler->connect_error) {
                throw new \Exception("Error Database Connection : " . $this->handler->connect_error);
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            die($this->error);
        }

    }

    /**
     * Make a unique instance of class, if not exists.
     * 
     * @param  array     $data MySQL server connection information
     * @return object          Instance of unique object
     */
    public static function getInstance($data)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($data);
        }

        return self::$_instance;
    }

    /**
     * MySQL execute query
     * 
     * @param  string     $query A SQL query statement
     * @return mixed
     */
    public function query($query)
    {
        $this->result = $this->handler->query($query);

        if (is_object($this->result)) {
            $this->count = $this->result->num_rows;
        } else {
            $this->count = 0;
        }

        return $this;
    }

    /**
     * Array of multiple query results.
     * 
     * @return array     An array of found results.
     */
    public function results($mode = 'both')
    {
        switch ($mode) {
            case 'assoc':
                $mode = MYSQLI_ASSOC;
                break;
            case 'numeric':
                $mode = MYSQLI_NUM;
                break;
            default:
                $mode = MYSQLI_BOTH;
                break;
        }

        if ($this->count == 1) {
            $this->results = $this->result->fetch_array($mode);
        } elseif ($this->count > 1) {

            $this->results = array();
            
            while ($data = $this->result->fetch_array($mode)) {
                $this->results[] = $data;
            }
        
        }
        return $this->results;
    }

    public function haveResults()
    {
        return ($this->count >= 1) ? true : false;
    }

    public function remove($find, $from)
    {
        if (in_array($from, $find)) {
            return true;
        }
        return false;
    }

    public function addError($message)
    {
        $this->_errors[] = $message;
    }

    public function errors()
    {
        return $this->_errors;
    }

    /**
     * Insert new record to the database based on an array
     * 
     * @since    1.0.0
     * @param    string               $table        The table where records must be made
     * @param    array                $contents     Column names and content to be inserted as array value
     * @param    string               $excluded     The excluded column from insert query.
     */
    public function insert($table, $contents, $excluded = array())
    {
        if (is_string($excluded) && empty($excluded) !== null) {
            $excluded = explode(', ', $excluded);
        }

        // Add MAX_FILE_SIZE to excluded columns.
        array_push($excluded, 'MAX_FILE_SIZE');
        
        $query = "INSERT INTO `{$table}` SET ";
        
        foreach ($contents as $column => $content) {

            if ($this->remove($excluded, $column)) {
                continue;
            }
            $query .= "`{$column}` = '{$content}', ";
        
        }
        $query = $this->cutFrom($query);

        return $query;

        return $this->query($query);
    }

    public function cutFrom($string, $where = '', $position = 'both')
    {
        if (empty($where)) {
            $this->addError("Please fill this space.");
            return;
        }

        switch ($position) {
            case 'both':
                $from = trim($string, $where);
                break;
            
            case 'left':
                $from = ltrim($string, $where);
                break;

            case 'right':
                $from = rtrim($string, $where);
                break;

            default:
                $from = trim($string, $where);
                break;
        }

        return $from;
    }

    /**
     * Deletes a record from the database
     * 
     * @param  string                $table    Table whose contents will be deleted 
     * @param  string                $contents Content that will be deleted
     * @param  string                $limit    Number limit of deleted results
     * @param  boolean               $like     Like to search through the database table
     * @return                                 Make a query
     */
    public function delete($table, $contents = '', $limit = '', $like = false)
    {
        $query = "DELETE FROM `{$table}` WHERE ";
        
        if(is_array($contents) && $contents != '') {

            foreach ($contents as $column => $content) {
                if (true === boolval($like)) {
                    $query .= "`{$column}` LIKE '%{$content}%' AND ";
                } else {
                    $query .= "`{$column}` = '{$content}' AND ";
                }
            }
            $query = substr($query, 0, -5);

        }

        if (intval($limit) >= 1) {
            $query .= ' LIMIT ' . $limit;
        }
        
        return $this->query($query);
    }

    /**
     * Gets a single row from $table where $contents are found
     * 
     * @param  string                $table    Table name selected
     * @param  string                $contents Content to be selected
     * @param  string                $order    Order criteria with column name
     * @param  string                $limit    Limit of result selected
     * @param  boolean               $like     A search criteria that match column content
     * @param  string                $operand  Operand used like AND
     * @param  string                $cols     Columns to be selected 
     * @return                                 Make a query to the database
     */
    public function select($table, $contents = '', $order = '', $limit = '', $like = false, $operand = 'AND', $cols = '*' )
    {
        // Catch Exceptions
        if (trim($from) == '') {
            return false;
        }

        $query = "SELECT {$cols} FROM `{$table}` WHERE ";
        
        if (is_array($contents) && ! empty($contents)) {

            foreach ($contents as $column => $content) {
                
                if (true === boolval($like)) {
                    $query .= "`{$column}` LIKE '%{$content}%' {$operand} ";
                } else {
                    $query .= "`{$column}` = '{$content}' {$operand} ";
                }
            }
            $query = substr($query, 0, -(mb_strlen($operand) + 2));

        } else {
            $query = substr($query, 0, -6);
        }

        if ($order != '') {
            $query .= ' ORDER BY ' . $order;
        }

        if ($limit != '') {
            $query .= ' LIMIT ' . $limit;
        }

        $result = $this->query($query);
        
        if(is_array($result))
            return $result;

        return array();
    }

    /**
     * Updates a record in the database
     * 
     * @param  string               $table    Table name to be updated
     * @param  array                $contents Content to be add instead of old
     * @param  arrat                $searches Content to be searched and replaced
     * @param  string               $excluded Excluded columns
     * @return                      Make an update query
     */
    public function update($table, $contents, $searches, $excluded = '')
    {
        // Check if all variable content has been set correctly.
        if (empty(trim($table)) || !is_array($contents) || !is_array($searches)) {
            return false;
        }

        if ($excluded == '') {
            $excluded = array();
        }

        array_push($excluded, 'MAX_FILE_SIZE');
        
        $query = "UPDATE `{$table}` SET ";
        
        foreach ($contents as $column => $content) {
            
            if (in_array($column, $excluded)) {
                continue;
            }
            $query .= "`{$column}` = '{$content}', ";
        }
        
        $query = substr($query, 0, -2);
        
        $query .= ' WHERE ';
        
        foreach ($searches as $column => $search) {
            $query .= "`{$column}` = '{$search}' AND ";
        }

        $query = substr($query, 0, -5);
        return $this->query($query);
    }

    /**
     * Class destructor that close connection
     */
    public function __destruct()
    {
        $this->handler->close();
    }

}
