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

use PDOException;
use EasyFast\Exceptions\DBException;

/**
 * Class Insert
 * Create and manage the SQL command for INSERT
 * @package EasyFast\DataBase
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait Insert
{
    /**
     * Method insert
     * Create and execute INSERT using PDO
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param array|null $rowData Array containing the properties
     * @throws DBException
     */
    public function insert ($rowData = null)
    {
        try {
            if (is_array($rowData)) {
                if (isset($rowData[0])) {
                    foreach ($rowData as $rd) {
                        $this->insert($rd);
                    }
                } else {
                    foreach ($rowData as $key => $value) {
                        $this->setRowData($key, $value);
                    }
                    $this->insert();
                }
            } else {
                $sql = 'INSERT INTO ' . $this->getTable() . '(';
                $sql .= implode(', ', array_keys($this->columnValue)) . ')';
                $sql .= ' VALUES (' . implode(', ', array_values($this->columnValue)) . ')';

                $this->setQuery($sql);
                $this->exec($sql);
                $this->columnValue = array();
            }
        } catch (PDOException $e) {
            $code = is_int($e->getCode()) ? $e->getCode() : 0;
            throw new DBException($e->getMessage(), $code, $this->getQuery());
        }

        return $this->lastInsertId();
    }
}
