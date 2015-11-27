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

namespace EasyFast\Config;

include_once __DIR__ . '/../Common/Utils.class.php';
include_once __DIR__ . '/../Exceptions/EasyFastException.class.php';

use EasyFast\Route;
use EasyFast\Common\Utils;
use EasyFast\Exceptions\InvalidArgException;

/**
 * Class Config
 * Define as variaveis de configuração da aplicação
 * @package EasyFast\Common
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @version 1.1
 */
class Config
{
    /**
     * @var object
     */
    private static $configs;

    /**
     * @var array $dbConfigs
     * @access private
     */
    private static $dbConfigs;

    /**
     * @var bool $routeDynamic
     * @access protected
     * Se for true irá instanciar método de rota dinamica automaticamente
     */
    protected $routeDynamic = false;

    /**
     * @var bool $sessionAutoStart
     * @access protected
     * Se for true irá instanciar a classe de sessão
     */
    protected $sessionAutoStart = true;

    /**
     * setConfigFile
     * @param $file
     * @param string $ext
     */
    public function setConfigFile($file, $ext = 'ini')
    {
        if ($ext == 'ini') {
            $this->setConfigIni($file);
        } elseif ($ext == 'xml') {
            $this->setConfigXml($file);
        }
    }

    /**
     * setConfigIni
     * @param $file
     */
    private function setConfigIni($file)
    {
        self::$configs = Utils::arrayToObject(parse_ini_file($file, true));
    }

    /**
     * Method setConfig
     * Seta arquivo XMl ou Array de configuração e atribui configuração as variaveis
     * @param string $file
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    private function setConfigXml($file)
    {
        if (is_array($config)) {
            $this->config($config);
        } else {
            $config = simplexml_load_file($config);
            $this->config($config);
            $this->setViewConfig($config);
        }
    }

    /**
     * getConfig
     * @return object
     */
    public static function getConfig()
    {
        return self::$configs;
    }

    /**
     * setConfigFileRoute
     * @param $file
     */
    public function setConfigFileRoute($file)
    {
        $route = new Route();
        $route->setConfigFile($file);
    }

    /**
     * routeAutomatic
     * @param bool|true $bool
     */
    public function routeAutomatic($bool = true)
    {
        self::$configs->App->RouteAutomatic = $bool;
    }

    /**
     * setDirApp
     * Set directory Application
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $dir
     */
    public function setDir($dir)
    {
        if (preg_match('/[\\\\|\/]$/', $dir)) {
            self::$configs->App->Dir = $dir;
        } else {
            self::$configs->App->Dir = "$dir/";
        }
    }

    /**
     * setSessionName
     * Set name session
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $name
     * @throws InvalidArgException
     */
    public function setSessionName($name)
    {
        self::$configs->App->SessioName = $name;
    }

    /**
     * setConfigDataBase
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $name
     * @param array $config
     * @throws InvalidArgException
     */
    public function setConfigDataBase($name, array $config)
    {
        if (is_array($config)) {
            self::$configs->DataBase->{$name} = (object)$config;
        } else {
            throw new InvalidArgException('Parameter is not an array');
        }
    }

    /**
     * Method getDBCongig
     * Restaga configurações do banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string|null $name
     * @return object
     */
    public static function getConfigDataBase($name = null)
    {
        if (is_null($name)) {
            return self::$configs->DataBase;
        }
        return self::$configs->DataBase->{$name};
    }

    /**
     * setDirTpl
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $dir
     */
    public static function setDirTpl($dir)
    {
        if (preg_match('/[\\\\|\/]$/', $dir)) {
            self::$configs->View->DirTpl = $dir;
        } else {
            self::$configs->View->DirTpl = $dir . '/';
        }
    }

    /**
     * Method sessionAutoStart
     * Instancia a classe session
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function sessionAutoStart($bool)
    {
        $this->sessionAutoStart = $bool;
    }

    /**
     * Get WebHost Application
     *
     * @return string
     */
    public static function getWebHost()
    {
        $webhost = self::getConfig()->App->WebHost;
        if (preg_match('/[\\\\|\/]$/', $webhost)) {
            return $webhost;
        }
        return "$webhost/";
    }
}
