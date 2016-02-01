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

use Exception;

/**
 * Class RouteException
 * @package EasyFast\Class\Exception
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class RouteException extends Exception
{
    /**
     * Method __construct
     * Adiciona mensagem e código que gerou a exceção
     * @param string $msg
     * @param int $code
     * @access public
     */
    public function __construct ($msg, $code = 0)
    {
        $code = is_int($code) ? $code : null;
        parent::__construct($msg, $code);
    }
}