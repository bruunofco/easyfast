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

use EasyFast\Exceptions\EasyFastException;
use EasyFast\Common\Utils;
use StdClass;

/**
 * Class Restful
 * Cria servidor restful
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFAst\Http
 */
class Restful
{
    /**
     * @var array Store querystring
     */
    private $queryString;

    /**
     * Method __construct
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public function __construct ()
    {
        if (empty($_SERVER['REQUEST_METHOD'])) {
            throw new EasyFastException('REQUEST_METHOD não disponivel.');
        }
    }

    /**
     * Method restful
     * Executa servidor restful, apenas entra dentro do contexto se URL e método forem os mesmos declarados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $method Método da requisição, POST, GET, DELETE, PUT
     * @param string $url
     * @param callback|array $callback
     * @param bool $argsAssoc Informa se os argumentos são associativos
     * @return bool|callback
     */
    public function server ($method, $url, $callback, $argsAssoc = true)
    {
        $method = strtoupper($method);
        try {
            if ($this->checkUrl($url) && ($_SERVER['REQUEST_METHOD'] == $method && is_callable($callback))) {

                if (is_array($callback) && $argsAssoc) {
                    $data = Utils::decodeRequest();

                    if (empty($data)) {
                        $data = new StdClass();
                    }
                    
                    foreach ($this->queryString as $key => $val) {
                        $data->$key = $val;
                    }

                    Utils::callMethodArgsOrder($callback[0], $callback[1], (array) $data);
                } elseif (is_array($callback)) {                             
                    call_user_func_array(array(new $callback[0], $callback[1]), $this->queryString);
                } else {
                    call_user_func_array($callback, $this->queryString);
                }

                exit();
            }

        } catch (EasyFastException $e) {
            $this->response('status => error | message => '. $e->getMessage(), 412);
        }

        return false;
    }


    /**
     * Method crossOrigin
     * Habilita crossOrigin
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param bool $bool
     */
    public function crossOrigin ($bool) {
        if ($bool) {
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && (   
                   $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' || 
                   $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'DELETE' || 
                   $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'PUT' )) {
                         header('Access-Control-Allow-Origin: *');
                         header('Access-Control-Allow-Headers: X-Requested-With');
                         header('Access-Control-Allow-Headers: Content-Type');
                         header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT'); // http://stackoverflow.com/a/7605119/578667
                         header('Access-Control-Max-Age: 86400'); 
                  }
              exit;
            } else {
                header('Access-Control-Allow-Origin: *');
            }
        }
    }

    /**
     * Method checkUrl
     * Verifica url da requisição
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $url
     * @throws EasyFastException
     * @return array
     */
    private function checkUrl ($url)
    {
        $url = array_filter(explode('/', $url));
        $queryString = array_filter(explode('/', isset($_GET['url']) ? $_GET['url'] : null));
        $this->queryString = array();

        if (count($url) != count($queryString)) {
            return false;
        }

        foreach ($url as $key => $val) {
            if (preg_match('/^:/', $val)) {
                $newKey = str_replace(':', '', $val);
                $this->queryString[$newKey] = $queryString[$key];
            } elseif ($queryString[$key] != $val) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method response
     * Return response in json, HTTP Status and exit system
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public static function response ($response, $httpStatus = null, $break = true)
    {
        if (!empty($httpStatus)) {
            header("HTTP/1.1 {$httpStatus}");
        }

        echo Utils::jsonEncode($response);
        if ($break) {
            exit();
        }
    }

}
