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

/**
 * Trait WHERE
 * Create selection criteria for database
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\DataBase
 */
trait WHERE
{
    private $where;
    private $wherePrepare;
    private $vals;

    /**
     * Method where
     * Create comparison string
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $column
     * @param string $operator
     * @param string|null $value
     * @param string|null $opLogic
     * @return WHERE
     */
    public function where ($column, $operator, $value = null, $opLogic = Connection::_AND)
    {
        if (is_null($value)) {
            if (is_null($value)) {
                $value = $operator;
                $operator = '=';
            }

            $value = addslashes($value);
            $this->where .= "{$column} {$operator} '{$value}' {$opLogic}";
            $this->wherePrepare($column, $operator, $value, $opLogic);    
        } elseif (is_array($operator) && is_null($value)) {
            $value = implode('\',\'', $operator);
            $operator = 'in';
            
            $this->where .= "{$column} {$operator} ('{$value}') {$opLogic}";
            $this->wherePrepare($column, $operator, "($value)", $opLogic); 
        } elseif (is_array($value)) {
            $value = implode('\',\'', $value);
            
            $this->where .= "{$column} {$operator} ('{$value}') {$opLogic}";
            $this->wherePrepare($column, $operator, "($value)", $opLogic); 
        } else {
            $value = addslashes($value);
            $this->where .= "{$column} {$operator} '{$value}' {$opLogic}";
            $this->wherePrepare($column, $operator, $value, $opLogic); 
        }
        
        return $this;
    }

    /**
     * Method wherePrepare
     * Prepare comparison string
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     * @param string $column
     * @param string $operator
     * @param string|null $value
     * @param string|null $opLogic
     * @return $this
     */
    protected function wherePrepare ($column, $operator, $value = null, $opLogic = Connection::_AND)
    {
        //TODO: Improve this solution.
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

//        $value = addslashes($value);
        $this->wherePrepare .= "{$column} {$operator} ? {$opLogic}";
        $this->vals[] = $value;

        return $this;
    }

    /**
     * Method andWhere
     * Create the string AND () for the comparison
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param $call
     * @return $this
     */
    public function andWhere ($call)
    {
        $this->where = substr($this->where, 0, -4);
        $this->where .= Connection::_AND . '(';
        call_user_func($call, $this);
        $this->where = substr($this->where, 0, -4);
        $this->where .= ') ' . Connection::_AND;
    }

    /**
     * Method orWhere
     * Create the string OR () for the comparison
     * @param $call
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return $this
     */
    public function orWhere ($call)
    {
        $this->where = substr($this->where, 0, -4);
        $this->where .= Connection::_OR . '(';
        call_user_func($call, $this);
        $this->where = substr($this->where, 0, -4);
        $this->where .= ') ' . Connection::_AND;
    }

    /**
     * Method getPrepareVals
     * Return the values of WherePrepare
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     * @return mixed
     */
    protected function getPrepareVals ()
    {
        //TODO: Improve this solution
        return $this->vals;
    }

    /**
     * Method getWhere
     * Get the WHERE string
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public function getWhere ()
    {
        if (!empty($this->where)) {
            return 'WHERE ' . substr($this->where, 0, strripos(trim($this->where), ' '));
        }
    }

    /**
     * Method getWherePrepare
     * Get the WHERE prepared string
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access protected
     * @return string
     */
    protected function getWherePrepare ()
    {
        if (!empty($this->where)) {
            return 'WHERE ' . substr($this->wherePrepare, 0, strripos(trim($this->wherePrepare), ' '));
        }
    }

    /**
     * Method cleanWhere
     * Clean the where clause
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return bool
     */
    public function cleanWhere ()
    {
        $this->where = null;
        $this->wherePrepare = null;

        return true;
    }
}
