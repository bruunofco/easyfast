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

use App;
use EasyFast\Common\Log;

/**
 * Class RestException
 * Provê Exception para Restful
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Class\Exceptions
 */
class RestException extends EasyFastException
{
    //TODO: Inserir restante dos métodos

    /**
     * Method __construct
     * Atribui mensagem e código de erro
     * @param string $msg Mensagem descrevendo o motivo da geração da exeption
     * @param int|string $code Código do erro gerado
     */
    public function __construct ($msg, $code = 0)
    {
        parent::__construct($msg, $code);
        $log = new Log(Log::ERROR, "$msg :: File: {$this->getFile()} :: Line: {$this->getLine()}");
        $log->save();
    }

    /**
     * Method getMessageJson
     * Retorna Mensagem da exception em json
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function getMessageJson ()
    {
        return json_encode(array($this->getMessage()));
    }

    /**
     * Method getHttpStatus
     * Retorna Mensagem da exception em json
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return void
     */
    public function getHttpStatus ()
    {
        header('HTTP/1.1 ' . $this->getCode());
    }


}