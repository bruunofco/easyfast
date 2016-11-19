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

namespace EasyFast\Mvc;

use PDO;
use ReflectionClass;
use EasyFast\Common\Utils;
use EasyFast\DataBase\Where;
use EasyFast\DataBase\Connection;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class Model
 * Classe abstrata contem métodos necessários para clonar, inserir, deletar, atualizar e criar dados.
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package easyFast/class/MVC
 * @version 1.2
 */
abstract class Model
{
    /**
     * @var $conn object a instancia de conexão com o banco de dados
     */
    private static $conn;

    /**
     * @var $result object o resultado do método executado
     */
    private static $result;

    /**
     * Method construct()
     * Passando o parametro $id executa o método $this->fetch()
     * @param int|null $param1
     * @param int|null $param2
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @throws EasyFastException
     */
    public function __construct($param1 = null, $param2 = null)
    {
        $conn = self::conn();
        $conn->cleanQuery();
        $conn->table(self::getTable());

        if (!is_null($param1) && is_null($param2)) {
            $conn->where(self::getPrimaryKey($conn), $param1);
        } elseif (!is_null($param1) && !is_null($param2)) {
            $conn->where($param1, $param2);
        }

        if (!is_null($param1) || !is_null($param2)) {
            $result = $conn->select();
            $conn->cleanQuery();

            if (isset($result[0])) {
                foreach ($result[0] as $key => $val) {
                    $methodProp = 'set' . Utils::snakeToCamelCase($key);
                    $this->$methodProp($val);
                }
            } else {
                throw new EasyFastException('Não existe nenhum registro em \'' . self::getTable() . '\' com \'' . self::getPrimaryKey($conn) . '\' = \'' . $param1 . '\'');
            }

            self::$result = $this;
        }
    }

    public function __destruct()
    {
        if (!is_null(self::$conn) && !self::$conn->inTransaction()) {
            self::$conn = null;
        }
    }

    /**
     * Method conn
     * Retorna a conexão ativa com o banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     */
    public static function conn($dataBase = null)
    {
        if (empty(self::$conn)) {
            self::$conn = new Connection($dataBase);
        }

        return self::$conn;
    }

    /**
     * Method getTable
     * Obtêm nome da tabela referente a classe
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access private
     * @return string
     */
    private static function getTable()
    {
        $class = get_called_class();
        //TODO: Tem que rever
        $class = str_replace("Model", "", $class);
        if (defined("{$class}::TABLE_NAME")) {
            return constant("{$class}::TABLE_NAME");
        } else {
            $entity = explode('\\', $class);
            return Utils::camelToSnakeCase($entity[1]);
        }
    }

    /**
     * Method getPrimaryKey
     * Obtêm a nome da propriedade que é chave primária
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access private
     * @return string
     */
    private static function getPrimaryKey()
    {
        $class = get_called_class();
        $sth = self::conn()->query('SHOW KEYS FROM ' . self::getTable() . " WHERE Key_name = 'PRIMARY'");
        $result = $sth->fetch();
        return isset($result->Column_name) ? $result->Column_name : null;
    }

    /**
     * Method getPrimaryKeys
     * get the primary key in a vector (useful in composite keys)
     * @author Hiago Souza <hiago@sparkweb.com.br>
     * @access private
     * @return array|null
     */
    private static function getPrimaryKeys()
    {
        $class = get_called_class();
        $sth = self::conn()->query('SHOW KEYS FROM ' . self::getTable() . " WHERE Key_name = 'PRIMARY'");
        $result = $sth->fetchAll();

        if(count($result) == 0) {
            return null;
        }
        $primarykeys = array();
        foreach($result as $r) {
            array_push($primarykeys, $r->Column_name);
        }
        return $primarykeys;
    }

    /**
     * Method getLastId
     * Retorna o ultimo Id da primeira Primary Key
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public static function getLastId()
    {
        $conn = self::conn();
        $conn->col("max(" . self::getPrimaryKey() . ") as id");
        $conn->table(self::getTable());
        return $conn->select();
    }

    /**
     * Method count
     * Conta os registros segundo a query
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return string
     */
    public static function count()
    {
        $conn = self::conn();
        $sth = $conn->query("SELECT COUNT(*) as count FROM " . self::getTable() . ' ' . $conn->getWhere());
        return $sth->fetch()->count;
    }

    /**
     * Method where
     * Cria string de comparação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return Model
     */
    public function where($column, $operator, $value = null, $opLogic = Connection::_AND)
    {
        $conn = self::conn();
        $conn->where($column, $operator, $value, $opLogic);

        // Retorna objeto
//        $class = get_called_class();
//        return new $class;
    }

    /**
     * Method orWhere
     * Cria string OR () para comparação separada
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return Model
     */
    public static function orWhere($call)
    {
        $conn = self::conn();
        $conn->orWhere($call);

        // Retorna objeto
        $class = get_called_class();
        return new $class;
    }

    /**
     * Method andWhere
     * Cria string OR () para comparação separada
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return Model
     */
    public static function andWhere($call)
    {
        $conn = self::conn();
        $conn->andWhere($call);

        // Retorna objeto
//        $class = get_called_class();
//        return new $class;
    }

    /**
     * Method col
     * seleciona as colunas
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $col
     * @return $this
     */
    public function col($col)
    {
        self::conn()->col($col);
    }

    /**
     * Method toJson
     * Transforma o retorno em json
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public function toJson()
    {
        if (is_object(self::$result)) {
            $json = get_object_vars(self::$result);
        } elseif (is_array(self::$result)) {
            $json = array();
            foreach (self::$result as $r) {
                $json[] = get_object_vars($r);
            }
        }

        return Utils::jsonEncode($json);
    }

    /**
     * Method toArray
     * Transforma o retorno em array
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return array
     */
    public function toArray()
    {
        if (empty(self::$result)) {
            return get_object_vars($this);
        } else {
            if (is_object(self::$result)) {
                $array = get_object_vars(self::$result);
                return $array;
            } elseif (is_array(self::$result)) {
                $array = array();
                foreach (self::$result as $r) {
                    $array[] = get_object_vars($r);
                }
                return $array;
            }
        }
    }

    /**
     * Method find
     * Busca resultado pela chave primaria
     * @param int $pk chave primaria
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param int $pk
     * @return object|array
     * @throws EasyFastException
     */
    public static function find($pk = null)
    {
        $conn = self::conn();
        $conn->table(self::getTable());


        // Quando a busca for feita por chave primária
        if (!empty($pk)) {
            $conn->where(self::getPrimaryKey($conn), $pk);

            $result = $conn->select();

            $nameClass = get_called_class();
            $instance = new $nameClass;

            if (isset($result[0])) {
                foreach ($result[0] as $key => $val) {
                    $methodProp = 'set' . Utils::snakeToCamelCase($key);
                    $instance->$methodProp($val);
                }
            } else {
                throw new EasyFastException('Não existe nenhum registro em \'' . self::getTable() . '\' com \'' . self::getPrimaryKey($conn) . '\' = \'' . $pk . '\'');
            }

            self::$result = $instance;
        } else {
            $result = $conn->select();

            $nameClass = get_called_class();
            $instance = array();

            if (isset($result[0])) {
                foreach ($result as $k => $r) {
                    $instance[$k] = new $nameClass;
                    foreach ($r as $key => $val) {
                        $methodProp = 'set' . Utils::snakeToCamelCase($key);
                        $instance[$k]->$methodProp($val);
                    }
                }
            } else {
                throw new EasyFastException('Não foi encontrado nenhum registro em \'' . self::getTable() . '\'');
            }

            self::$result = $instance;
        }

        if (!is_null(self::$conn) && !self::$conn->inTransaction()) {
            self::$conn = null;
        }

        return $instance;
    }

    /**
     * Method all
     * Retorna todos os registros referente ao objeto
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return array
     */
    public static function all()
    {
        $conn = self::conn();
        $sth = $conn->query("SELECT * FROM " . self::getTable());
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $object = array();

        foreach ($result as $r) {

            $nameClass = get_called_class();
            $instance = new $nameClass;

            foreach ($r as $key => $val) {
                $methodProp = 'set' . Utils::snakeToCamelCase($key);
                $instance->$methodProp($val);
            }

            array_push($object, $instance);
        }

        self::$result = $object;

        return $object;
    }

    /**
     * Method save
     * Insere as propriedades no banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return string Id da inserção
     */
    public function save()
    {
        $class = get_class($this);
        $class = explode('\\', $class);
        $class = "$class[0]\\Traits\\Trait$class[1]";

        $r = new ReflectionClass($class);

        $vars = get_object_vars($this);
        $varsDB = array();

        foreach ($vars as $key => $val) {
            if ($r->hasProperty($key)) {
                $varsDB[Utils::camelToSnakeCase($key)] = $val;
            }
        }

        $conn = self::conn();
        $conn->table($this->getTable());

        $pk = $this->getPrimaryKey();
        $this->$pk = $conn->insert($varsDB);
        $var = lcfirst(Utils::snakeToCamelCase($pk));

        return $this->$var;
    }

    /**
     * Method delete
     * Deleta o objeto do banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return bool
     */
    public function delete()
    {
        $primaryKeys = $this->getPrimaryKeys();
        $conn = $this->conn();
        $conn->table($this->getTable());
        $vars = get_object_vars($this);

        foreach ($vars as $k => $v) {
            if (!is_null($v) || $v != '') {
                if (in_array($k, $primaryKeys)) {
                    $conn->where(Utils::camelToSnakeCase($k), $v);
                }
            }
        }

        $conn->delete();
    }

    /**
     * Method update
     * Deleta o objeto do banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return bool
     */
    public function update()
    {
        $primaryKeys = $this->getPrimaryKeys();
        $class = get_class($this);
        $class = explode('\\', $class);
        $class = "$class[0]\\Traits\\Trait$class[1]";

        $r = new ReflectionClass($class);

        $vars = get_object_vars($this);
        $varsDB = array();

        foreach ($vars as $key => $val) {
            if (($r->hasProperty($key) && isset($val)) && !in_array(Utils::camelToSnakeCase($key), $primaryKeys)) {
                $varsDB[Utils::camelToSnakeCase($key)] = $val;
            }
        }

        $conn = self::conn();
        $conn->table($this->getTable());

        if (!empty($primaryKeys)) {
            foreach ($primaryKeys as $primary) {
                if (!is_null($primary)) {
                    $snake = lcfirst(Utils::snakeToCamelCase($primary));
                    $conn->where($primary, '=', $vars[$snake]);
                }
            }
        }

        $conn->update($varsDB);
        $conn->cleanQuery();
    }

    /**
     * Method select
     * Executa com um select
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function select()
    {
        $conn = self::conn();
        $conn->table($this->getTable());
        self::$result = $conn->select();

        if (empty(self::$result)) {
            throw new EasyFastException('Não foi encontrado nenhum registro em \'' . self::getTable() . '\'');
        }

        self::$result = $conn->select();

        if (!is_null(self::$conn) && !$conn->inTransaction()) {
            self::$conn = null;
        }

        return self::$result;
    }
}
