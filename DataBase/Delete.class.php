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
 * Class Delete
 * Abstrai a escrita de código SQL para executar DELETE
 * @package EasyFast\DataBase
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
trait Delete
{
    /**
     * Method delete
     * Cria e executa script para DELETE
     * Usando Prepare PDO
     * @uses Where::getWherePrepare Obtêm Where para preprare
     * @uses Where::getPrepareVals Obtêm valores do prepare
     * @uses Connection::prepare Prepara o PDO para receber um script SQL
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function delete ()
    {
        $this->setQuery('DELETE FROM ' . $this->getTable() . $this->getWhere());

        $sth = $this->prepare('DELETE FROM ' . $this->getTable() . $this->getWherePrepare());

        if ($this->getPrepareVals()) {
            foreach ($this->getPrepareVals() as $key => $val) {
                $key += 1;
                $sth->bindValue($key, $val);
            }
        }

        try {
            $sth->execute();
        } catch (PDOException $e) {
            $code = is_int($e->getCode()) ? $e->getCode() : 0;
            throw new DBException($e->getMessage(), $code);
        }

        if (!$sth->rowCount()) {
            throw new DBException('Não foi possível executar delete.');
        }

        return $this;
    }
}
