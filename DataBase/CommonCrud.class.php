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
 * Métodos comum para CRUD
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
     * Where - Cria critério de seleção
     */
    use Where;

    /**
     * Method setQuery
     * Seta query executada
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     * @param string $qr
     * @throws DBException
     */
    protected static function setQuery ($qr)
    {
        if (!is_string($qr)) {
            throw new DBException('Query inválida.');
        }
        self::$query = $qr;
    }

    /**
     * Method getQuery
     * Obtêm a ultima query executa
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
     * Seta a tabela a ser usada na query
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $table
     * @throws DBException
     * @return Connection
     */
    public static function table ($table)
    {
        if (!is_string($table)) {
            throw new DBException('Nome inválido. Nome de ser uma string.');
        }

        self::$table = $table;

        return new self::$class;
    }

    /**
     * Method getTable
     * Obtêm a tabela que está sendo usada
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     * @throws DBException
     */
    protected static function getTable ()
    {
        if (!isset(self::$table)) {
            throw new DBException('Tabela não informada. É necessário informar a tabela para executar query.');
        }
        return self::$table . "\n";
    }

    /**
     * Method setRowData
     * Set vals to colunas - INSERT, UPDATE
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
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
            throw new DBException('Parâmetros inválidos. Não foi possível atribuir valores.');
        }

        return $this;
    }

    /**
     * Method cleanQuery
     * Limpa a última query executada na classe
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
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
