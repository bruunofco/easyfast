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

namespace EasyFast\Sessions;

use EasyFast\App;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class Session
 * Gerencia sessão da aplicação
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Sessions
 */
class Session
{
    /**
     * Method __construct
     * Inicia a sessão e o constrole de buffer
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function __construct ()
    {
        try {
            $this->start();
        } catch (EasyFastException $e) {}
    }

    /**
     * start
     * Start sesssion
     */
    public static function start()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            ob_start();
            session_start();
        }
    }

    /**
     * Method set
     * Armazena valor na sessão
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $var
     * @param $value
     * @param bool|null $serialize
     */
    public static function set ($var, $value, $serialize = false)
    {
        self::sessionStatus();
        if ($serialize) {
            $_SESSION[$var] = serialize($value);
        } else {
            $_SESSION[$var] = $value;
        }

    }

    /**
     * Method get
     * Resgata valor da sessão
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string|null $var
     * @param bool|null $unserialize
     * @throws EasyFastException
     * @return string|array|object
     */
    public static function get ($var = null, $unserialize = false)
    {
        self::start();
        self::sessionStatus();
        if (isset($_SESSION[$var])) {
            if ($unserialize) {
                return unserialize($_SESSION[$var]);
            } else {
                return $_SESSION[$var];
            }
        } elseif (is_null($var)) {
            return $_SESSION;
        } else {
            throw new EasyFastException('It is impossible obtain the session.');
        }
    }

    /**
     * Method destroy
     * Destroi sessão
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string|null $var
     */
    public static function destroy ($var = null)
    {
        if (isset($_SESSION[$var])) {
            unset($_SESSION[$var]);
        } else {
            session_destroy();
        }
    }

    /**
     * Method sessionStatus
     * Verifica status da sessão
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @throws EasyFastException
     * @return mixed
     */
    public static function sessionStatus ()
    {
        switch (session_status()) {
            case PHP_SESSION_NONE:
                throw new EasyFastException('Sessão não foi iniciada.');
                break;
            case PHP_SESSION_DISABLED:
                throw new EasyFastException('Sessão desativada.');
                break;
            case PHP_SESSION_ACTIVE:
                return true;
                break;
        }
        return false;
    }
}
