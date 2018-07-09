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

namespace EasyFast\Http;

use EasyFast\Common\Utils;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class Authenticate
 * Provê métodos para autenticação por Token
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Http
 */
class Authenticate
{
    public $conn;

    /**
     * @var string Define nome do campo reponsável em armazenar token
     */
    public $columnToken    = 'token';

    /**
     * @var string Define nome do campo responsável em armazenar data final de expiração do token
     */
    public $columnDateEnd  = 'date_end';

    /**
     * @var string Define classe model com informações sobre o token
     */
    public $tableName      = 'Model\Sessions';

    /**
     * Method start
     * Verifica se está sendo utilizado o parâmetro token
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @throws EasyFastException
     */
    public function start ()
    {
        $header = getallheaders();

        if (isset($header['Token']) || isset($header['token'])) {
            $token = isset($header['Token']) ? $header['Token'] : $header['token'];
            // Verifica se token é existente no banco de dados
            try {
                $tableName  = $this->tableName;
                //TODO: Verificar métodos da classe model, utilizados aqui
                $session    = $tableName::query()->add($this->columnToken, '=', $token)->add('ip', '=', $_SERVER['REMOTE_ADDR'])->fetchAll()->toArray();
            } catch (EasyFastException $e) {
                throw new EasyFastException('Token inválido.');
            }

            /** Verifica se token está expirado **/
            if ($session[0]->getDateEnd() < date('Y-m-d H:i:s')) {
                throw new EasyFastException('Token expirado.');
            }
        } else {
            throw new EasyFastException('Paramêtro token não recebido.');
        }
    }


    /**
     * Method stopSystem
     * Para a execução do sistema, envia status http e retorna json com mensagem de erro
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $msg
     */
    public function stopSystem ($msg)
    {
        header('HTTP/1.1 401');
        echo Utils::jsonEncode("Status => Error | Message => $msg");
        die;
    }
}
