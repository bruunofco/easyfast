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

namespace EasyFast\Exceptions;


/**
 * Class InvalidArgException
 * Gera Exception para erros de argumentos
 * @package EasyFast\Class\Exception
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class DBException extends EasyFastException
{
    /**
     * @var Armagena query executada
     */
    private $query;

    /**
     * Method __construct
     * Adiciona mensagem e código que gerou a exceção
     * @param string $msg
     * @param int $code
     * @access public
     */
    public function __construct ($msg, $code = 0, $query = null)
    {
        parent::__construct($msg, $code);
        $this->setQuery($query);
    }

    /**
     * Method getQuery
     * retorna a query executada
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function getQuery ()
    {
        return $this->query;
    }

    /**
     * Method setQuery
     * armagena a query executada
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access private
     * @param string $query
     */
    private function setQuery ($query)
    {
        $this->query = $query;
    }
}