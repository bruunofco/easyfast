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
 * Class Update
 * Abstrai a escrita de código SQL para executar UPDATE
 * @package EasyFast\DataBase
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait Update
{

    /**
     * Method update
     * Cria e executa script para UPDATE
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function update ($rowData = null)
    {
        if (is_array($rowData)) {
            if (isset($rowData[0])) {
                foreach ($rowData as $rd) {
                    $this->update($rd);
                }
            } else {
                foreach ($rowData as $key => $value) {
                    $this->setRowData($key, $value);
                }
                $this->update();
            }
        } else {
            $sql = 'UPDATE ' . $this->getTable() . ' SET ';
            foreach ($this->columnValue as $key => $value) {
                $sql .= $key . ' = ' . $value . ', ';
            }
            $sql  = substr($sql, 0, strripos($sql, ', '));
            $sql .= ' ' . $this->getWhere();

            $this->setQuery($sql);

            $sth = $this->prepare($sql);
            try {
                $sth->execute();
                $this->columnValue = null;
            } catch (PDOException $e) {
                $code = is_int($e->getCode()) ? $e->getCode() : 0;
                throw new DBException($e->getMessage(), $code, $sql);
            }

            return $this;
        }
    }
}