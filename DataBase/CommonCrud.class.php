<?php
/*
 * Copyright 2015 Bruno de Oliveira Francisco <bruno@salluzweb.com.br>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace EasyFast\DataBase;

use EasyFast\Exceptions\DBException;

/**
 * Class CommonCrud
 * Methods for CRUD
 * @package EasyFast\DataBase
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait CommonCrud
{
    private static $query;
    private static $table;
    private $columnValue;
    private static $class = 'EasyFast\DataBase\Connection';

    /**
     * Traits
     * Where - Selection Criteria
     */
    use Where;

    /**
     * Method setQuery
     * Set a query to execute
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     * @param string $qr
     * @throws DBException
     */
    protected static function setQuery ($qr)
    {
        if (!is_string($qr)) {
            throw new DBException('Invalid Query.');
        }
        self::$query = $qr;
    }

    /**
     * Method getQuery
     * Get last executed query
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public static function getQuery ()
    {
        return trim(self::$query);
    }

    /**
     * Method setTable
     * Set a table to be used in the query
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $table
     * @throws DBException
     * @return Connection
     */
    public static function table ($table)
    {
        if (!is_string($table)) {
            throw new DBException('Invalid Name. Must be a string.');
        }

        self::$table = $table;

        return new self::$class;
    }

    /**
     * Method getTable
     * Get the current Table
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     * @throws DBException
     * @return string
     */
    protected static function getTable ()
    {
        if (!isset(self::$table)) {
            throw new DBException('Unknown table. It is necessary to inform the table to perform a query.');
        }
        return self::$table . "\n";
    }

    /**
     * Method setRowData
     * Set valuess to columns - INSERT, UPDATE
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string|bool|int $column
     * @param string|bool|int $value
     * @throws DBException
     * @return Connection
     */
    public function setRowData ($column, $value)
    {
        if (is_string($value)) {
            $value = addslashes($value);
            $this->columnValue[$column] = "'$value'";
        } elseif (is_bool($value)) {
            $this->columnValue[$column] = $value ? 'TRUE' : 'FALSE';
        } elseif (is_null($value)) {
            $this->columnValue[$column] = 'NULL';
        } elseif (is_int($value)) {
            $this->columnValue[$column] = $value;
        } else {
            throw new DBException('Invalid Parameters. Could not assign values');
        }

        return $this;
    }

    /**
     * Method cleanQuery
     * Clean the last executed query
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return void
     */
    public function cleanQuery () 
    {
		foreach ($this as $key => $value) {
			if ($key != 'conn' && $key != 'class') {
                $this->$key = null;
            }
		}	
    }
}
