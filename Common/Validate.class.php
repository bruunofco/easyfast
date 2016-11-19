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

namespace EasyFast\Common;

use EasyFast\Exceptions\InvalidArgException;

/**
 * Class Validate
 *
 * Contém métodos para realizar algumas validações
 * @package EasyFast\Common
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class Validate
{

    /**
     * Method isMail
     * Verifica se é um e-mail válido
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $email
     * @throws InvalidArgException
     * @return true
     */
    public static function isMail ($email)
    {
        if (!preg_match('/^([0-9,a-z,A-Z]+)([._-]([0-9,a-z,A-Z]+))*[@]([0-9,a-z,A-Z]+)([._-]([0-9,a-z,A-Z]+))*[.]([a-z,A-Z]){2,3}([0-9,a-z,A-Z])?$/', $email)) {
            throw new InvalidArgException('E-mail não é válido.');
        }
        return true;
    }
}
