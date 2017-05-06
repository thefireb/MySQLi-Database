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
class MySQLi_Database
{
    private $_handler;

    private $_count = 0;

    public $result = null;

    public $results = array();

    private static $_instance = null;

    private function __construct(array $data)
    {
        $this->_handler = @new mysqli(
            $data['hostname'],
            $data['username'],
            $data['password'],
            $data['database']
        );
    }

    public static function getInstance($data)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($data);
        }

        return self::$_instance;
    }

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

    public function query($sql)
    {
        $this->result = $this->_handler->query($sql);

        if (is_object($this->result)) {
            $this->_count = $this->result->num_rows;
        }

        return $this;
    }

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

    public function delete($contents)
    {
        if (count($contents)) {

            if ($this->in_array('table', $contents)) {
                
                $table = $contents['table'];
                unset($contents['table']);
            
            }

            $sql = "DELETE FROM `$table` WHERE ";
            foreach ($contents as $column => $content) {
                $sql .= "`$column` = '$content'";
            }

        }

        return $this->query($sql);
    }

    public function count()
    {
        return $this->_count;
    }

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
}