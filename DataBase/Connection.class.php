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
use EasyFast\App;
use EasyFast\Exceptions\DBException;

/**
 * Class Connection
 * Gerenciador de banco de dados
 * @package EasyFast\DataBase
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @version 1.2
 */
class Connection
{
    const _AND = 'AND ';
    const _OR  = 'OR ';

    /**
     * Traits
     * CommunCrud   - Métodos e propriêdades genericos
     * Insert       - Métodos para INSERT
     * Delete       - Métodos para DELETE
     * Update       - Métodos para UPDATE
     */
    use CommonCrud;
    use Insert;
    use Delete;
    use Update;
    use Select;

    /**
     * @var PDO Armagena conexão com o banco de dados
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
        $configs = App::getDBConfig();

        if (is_null($db)) {
            $dataBaseMain = App::dbMain();
            $dataBaseMain = !is_null($dataBaseMain) ? $dataBaseMain : 'Main';
            $this->open($configs[$dataBaseMain]);
        } elseif (is_array($db)) {
            $this->open($db);
        } else {
            $this->open($configs[$db]);
        }
    }

    /**
     * Method open
     * Abre uma conexão com o banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param array $db
     * @throws DBException
     * @return PDO
     */
    public function open ($db)
    {
        if (!is_array($db)) {
            throw new DBException('Configurações de banco de dados inválido.');
        }

        //Atribui os valores as váriaveis
        $userName   = isset($db['UserName'])    ? $db['UserName']   : null;
        $password   = isset($db['Password'])    ? $db['Password']   : null;
        $dbName     = isset($db['DBName'])      ? $db['DBName']     : null;
        $hostName   = isset($db['HostName'])    ? $db['HostName']   : null;
        $drive      = isset($db['Drive'])       ? $db['Drive']      : null;
        $port       = isset($db['Port'])        ? $db['Port']       : null;

        //Obtêm o drive do banco de dados
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

        //Define para que o PDO lance exceções na ocorrência de erros
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //Define que o retorno do PDO seja sempre em objetos
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

		return $this->conn;
    }

    /**
     * Method beginTransaction
     * Inicia uma transação
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
     * Confirma transação e fecha a conexão com o banco de dados
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
     * Cancela transação e fecha a conexão com o banco de dados
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
     * Executa script sql
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
     * Executa instrução SQL retornando um conjunto de objeto PDOStatement
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $sql
     * @throws DBException
     * @return \PDOStatement
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
     * Prepara um sql statement para execução e retorna um objeto de declaração
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $sql
     * @access public
     * @throws DBException
     * @return \PDOStatement
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
     * Escapa caractres especiais
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
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
     * Retorna o ID dá ultima linha inserida ou valor de sequência
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $name
     * @return int
     * @access public
     */
    public function lastInsertId ($name = null)
    {
        return $this->conn->lastInsertId($name);
    }

    /**
     * Method inTransaction
     * Verifica se existe transação ativa
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function inTransaction ()
    {
        if(!empty($this->conn)) {
            return $this->conn->inTransaction();
        } else {
            return false;
        }
    }

    /**
     * Method getAvailableDrivers
     * Retorna um array de drivers PDO disponíveis
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function getAvailableDrivers ()
    {
        return $this->conn->getAvailableDrivers();
    }
}
