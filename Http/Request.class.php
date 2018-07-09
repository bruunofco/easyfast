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

//TODO: Rever métodos e verificar se existe outra melhor forma para fazer requisições. Ex: fsocket

use DateTime;

/**
 * Class Request
 * Executa requisições HTTP
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Http
 */
class Request
{
    public $url, $method, $responseHeader, $header, $time, $responseCode, $response, $content;

    /**
     * Method __construct
     * Atribui valores default as propriedades
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $url
     * @access public
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->header = array();
    }

    /**
     * Method getResponseToObject
     * Obtêm o tempo da requisição
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public function getResponseToObject()
    {
        if (is_array($this->content)) {
            return json_decode(json_encode($this->content), FALSE);
        }

        json_decode($this->content);
        if (json_last_error() == JSON_ERROR_NONE) {
            return json_decode($this->content);
        }
    }

    /**
     * Method getTime
     * Obtêm o tempo da requisição
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return string
     */
    public function getTime()
    {
        return $this->time->format('%s');
    }

    /**
     * Method getResponseHeader
     * Retorna o ResponseHeader
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $object = false
     * @return array
     */
    public function getResponseHeader($object = false)
    {
        $header = $this->parseHeaders($this->responseHeader);
        return $object ? (object)$header : $header;
    }

    /**
     * Method getHeader
     * Return header content by parameter
     * @author Hiago Souza <hiago@sparkweb.com.br>
     * @return String
     * @param $name
     *
     */
    public function getHeader($name)
    {
        foreach ($this->responseHeader as $headerline) {
            $check = explode(":", $headerline);
            if (strtolower($check[0]) == strtolower($name)) {
                return trim($check[1]);
            }
        }

        return null;
    }

    /**
     * Method getResponseCode
     * Retorna o ResponseCode
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return array
     */
    public function getResponseCode()
    {
        if (is_array($this->responseHeader)) {
            $this->responseCode = explode(' ', $this->responseHeader[0], 3);
            $this->responseCode = isset($this->responseCode[1]) ? $this->responseCode[1] : null;
        } else {
            $this->responseCode = $this->responseHeader;
        }

        return $this->responseCode;
    }

    /**
     * Method setHeader
     * Atribuir parametros ao header da requisição
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setHeader($key, $value)
    {
        $this->header[$key] = $value;
    }

    /**
     * Method setTimeout
     * Define timeout para a requisição
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $seconds
     * @return void
     */
    public function setTimeout($seconds)
    {
        $this->header['timeout'] = $seconds;
    }

    /**
     * Method exec
     * Executa uma requisição post
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param null|array $header
     * @param array $content
     * @param string $method
     * @return object
     */
    public function exec($method, $content = null, $header = array())
    {
        $timeInit = new DateTime();
        $this->method = $method;
        $httpHeader['header'] = null;

        if (is_array($this->header) && is_array($header)) {
            $header = array_merge($this->header, $header);
        }

        if (!is_null($header)) {
            foreach ($header as $key => $value) {
                $httpHeader['header'] .= "$key: $value \r\n";
            }
        }

        $httpHeader['content'] = is_array($content) ? http_build_query($content) : $content;
        if (is_string($httpHeader['content'])) {
            $httpHeader['header'] .= "Content-length: " . strlen($httpHeader['content']);
        }
        $httpHeader['method'] = $this->method;
        $httpHeader['ignore_errors'] = true;

        $headerContext = stream_context_create(array('http' => $httpHeader));
        $this->content = @file_get_contents($this->url, false, $headerContext);
        $this->responseHeader = @$http_response_header;

        $timeFinal = new DateTime();
        $this->time = $timeInit->diff($timeFinal);

        return $this->content;
    }

    /**
     * @param $headers
     * @return array
     */
    private function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1]))
                $head[trim($t[0])] = trim($t[1]);
            else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out))
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
    }
}
