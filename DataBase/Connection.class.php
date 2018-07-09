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

use PDO;
use PDOException;
use PDOStatement;
use EasyFast\App;
use EasyFast\Exceptions\DBException;

/**
 * Class Connection
 * DataBase Manager
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\DataBase
 */
class Connection
{
    const _AND = 'AND ';
    const _OR  = 'OR ';

    /**
     * Traits
     * CommunCrud   - Generic Methods and properties
     * Insert       - INSERT Methods
     * Delete       - DELETE Methods
     * Update       - UPDATE Methods
     */
    use CommonCrud;
    use Insert;
    use Delete;
    use Update;
    use Select;

    /**
     * @var PDO Stores the database connection
     * @access public
     */
    public $conn;

    /**
     * Method __construct
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string|array $db
     */
    public function __construct ($db = null)
    {
        $configs = App::getDataBaseConfig();

        if (is_null($db)) {
            $dataBaseMain = 'Main';
            $this->open($configs->{$dataBaseMain});
        } elseif (is_array($db)) {
            $this->open($db);
        } else {
            $this->open($configs->{$db});
        }
    }

    /**
     * Method open
     * Open a new connection with the database
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param array $db
     * @throws DBException
     * @return PDO
     */
    public function open ($db)
    {
        if (!is_object($db)) {
            throw new DBException('Invalid configuration for Database');
        }

        //Set Values
        $userName   = isset($db->UserName)    ? $db->UserName   : null;
        $password   = isset($db->Password)    ? $db->Password   : null;
        $dbName     = isset($db->DBName)      ? $db->DBName     : null;
        $hostName   = isset($db->HostName)    ? $db->HostName   : null;
        $drive      = isset($db->Drive)       ? $db->Drive      : null;
        $port       = isset($db->Port)        ? $db->Port       : null;

        //Get the correct drive for each database
        switch ($drive) {
            case 'pgsql':
                $port  = is_null($port) ? '5432' : $port;
                try {
                    $this->conn = new PDO("pgsql:dbname={$dbName}; users={$userName}; password={$password}; host={$hostName}; port={$port}");
                } catch (PDOException $e) {
                    throw new DBException($e->getMessage());
                }
                break;

            case 'mysql':
                $port  = is_null($port) ? '3306' : $port;
                try {
                    $this->conn = new PDO("mysql:host={$hostName}; port={$port}; dbname={$dbName}", $userName, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'));
                } catch (PDOException $e) {
                    throw new DBException($e->getMessage());
                }
                break;

            case 'sqlite':
                try {
                    $this->conn = new PDO("sqlite:{$dbName}");
                } catch (PDOException $e) {
                    throw new DBException($e->getMessage());
                }
                break;

            case 'ibase':
                try {
                    $this->conn = new PDO("firebird:dbname={$dbName}", $userName, $password);
                } catch (PDOException $e) {
                    throw new DBException($e->getMessage());
                }
                break;

            case 'oci8':
                try {
                    $this->conn = new PDO("oci:dbname={$dbName}", $userName, $password);
                } catch (PDOException $e) {
                    throw new DBException($e->getMessage());
                }
                break;

            case 'mssql':
                $port = is_null($port) ? '1433' : $port;
                try {
                    $this->conn = new PDO("mssql:host={$hostName}, $port; dbname={$dbName}", $userName, $password);
                } catch (PDOException $e) {
                    throw new DBException($e->getMessage());
                }
                break;
        }

        //Set PDO to throw Exceptions for errors
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //Set PDO to always return objects
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

		return $this->conn;
    }

    /**
     * Method beginTransaction
     * Starts a transaction
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @throws DBException
     */
    public function beginTransaction ()
    {
        try {
            $this->conn->beginTransaction();
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method commit
     * Commit a open transaction and close the connection with database
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @throws DBException
     */
    public function commit ()
    {
        try {
            $this->conn->commit();
            $this->conn = null;
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method rollback
     * Cancel (rollback) a open transaction and close the connection with database
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @throws DBException
     */
    public function rollback ()
    {
        try {
            $this->conn->rollback();
            $this->conn = null;
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method exec
     * Performs a SQL script
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return int
     * @param string $sql
     * @throws DBException
     */
    public function exec ($sql)
    {
        try {
            return $this->conn->exec($sql);
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), (int)$e->getCode(), $sql);
        }
    }

    /**
     * Method query
     * Performs a SQL instruction and return objectos of type PDOStatement
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $sql
     * @throws DBException
     * @return PDOStatement
     */
    public function query ($sql)
    {
        try {
            return $this->conn->query($sql);
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method prepare
     * Prepare a SQL Statement for execution and return a PDOStatement
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $sql
     * @throws DBException
     * @return PDOStatement
     */
    public function prepare ($sql)
    {
        try {
            return $this->conn->prepare($sql);
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method quote
     * Escape special characters
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $str
     * @param int $paramType
     * @return string
     */
    public function quote ($str, $paramType)
    {
        return $this->conn->quote($str, $paramType);
    }

    /**
     * Method lastInsertId
     * Returns the ID of last inserted line
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $name
     * @return int
     */
    public function lastInsertId ($name = null)
    {
        return $this->conn->lastInsertId($name);
    }

    /**
     * Method inTransaction
     * Checks for active transaction
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return boolean
     */
    public function inTransaction ()
    {
    	if(is_null($this->conn)) {
            return false;
        }
        
        return $this->conn->inTransaction();
    }

    /**
     * Method getAvailableDrivers
     * Returns a array with available PDO drivers
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return array
     */
    public function getAvailableDrivers ()
    {
        return $this->conn->getAvailableDrivers();
    }
}
