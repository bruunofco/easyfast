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

use EasyFast\Exceptions\InvalidArgException;

/**
 * Class Config
 * Define as variaveis de configuração da aplicação
 * @package EasyFast\Common
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @version 1.1
 */
trait Config
{
    /**
     * Propriedades de configuração da aplicação
     * @var string $appName        Nome da aplicação
     * @var string $appUrl         URL da aplicação
     * @var string $appSessionName Nome da sessão criada no sistema
     * @var string $appDir         Diretório da aplicação
     * @var string $appDirLog      Diretório de Logs
     * @var string $appDirTpl      Diretório de Templates
     * @var string $dirEasyFast    Diretório do EasyFast
     * @access public
     */
    public static $appName, $appSessionName, $appUrl, $appDir, $appDirLog, $appDirTpl, $dirEasyFast, $appConfigs;
    
    /**
     * @var array
     */
    public static $viewConfig = array();

    /**
     * @var array $dbConfigs
     * @access private
     */
    private static $dbConfigs;

    /**
     * @var string $dbMain
     * @access private
     */
    private static $dbMain;

    /**
     * @var bool $routeDynamic
     * @access protected
     * Se for true irá instanciar método de rota dinamica automaticamente
     */
    protected $routeDynamic = true;

    /**
     * @var bool $sessionAutoStart
     * @access protected
     * Se for true irá instanciar a classe de sessão
     */
    protected $sessionAutoStart = true;


    /**
     * Method setConfig
     * Seta arquivo XMl ou Array de configuração e atribui configuração as variaveis
     * @param string $config
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function setConfig ($config)
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
     * Method config
     * Lê arquivo XML de configuração e atribui valores as propriedades
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access private
     * @param array $config Array com as configurações
     */
    private function config ($config)
    {
        static::$appSessionName = 'EasyFast';

        static::$dirEasyFast = __DIR__ . '/../../';
        $this->setDirApp($config->app->dir);
        $this->setDirLog($config->app->dirLog);
        $this->appUrl($config->app->url);
    }
    
    /**
     * setViewConfig
     * Set vars utils in View
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $config
     */
    private function setViewConfig($config)
    {
        if (isset($config->view)) {
            $total = count($config->view);
            foreach ($config->view->children() as $key => $value) {
                static::$viewConfig[$key] = $config->view->{$key}->__toString();
            }
        }
    }

    /**
     * Method getConfig
     * Obtêm as variaveis de configuração da aplicação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public static function getConfig ()
    {
        return get_class_vars(get_class());
    }

    /**
     * Method setDirApp
     * Seta diretório a aplicação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $dir
     */
    public function setDirApp ($dir)
    {
        if (preg_match('/[\\\\|\/]$/', $dir)) {
            static::$appDir = $dir;
        } else {
            static::$appDir = "$dir/";
        }
    }

    /**
     * Method setDirLog
     * Seta diretório de log
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string $dir
     */
    public function setDirLog ($dir)
    {
        if (preg_match('/[\\\\|\/]$/', $dir)) {
            static::$appDirLog = $dir;
        } else {
            static::$appDirLog = "$dir/";
        }
    }

    /**
     * Method setUrl
     * Seta url da aplicação ou retorna
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @param string|null $url
     */
    public static function appUrl ($url = null)
    {
        if (is_null($url)) {
            return static::$appUrl;
        } else {
            if (preg_match('/[\\\\|\/]$/', $url)) {
                static::$appUrl = $url;
            } else {
                static::$appUrl = "$url/";
            }
        }
    }

    /**
     * Method setSessionName
     * Seta nome para sessão
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $name
     * @throws InvalidArgException
     */
    public function setSessionName ($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgException('O nome da sessão deve ser uma string.');
        }
        static::$sessionName = $name;
    }

    /**
     * Method setDBConfig
     * Seta configurações do banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param array|string $db Array de configuração ou caminho fisíco para arquivo de configuração
     * @throws InvalidArgException
     */
    public function setDBConfig ($db)
    {
        if (is_string($db)) {
            static::$dbConfigs = parse_ini_file($db, true);
        } elseif (is_array($db)) {
            static::$dbConfigs = $db;
        } else {
            throw new InvalidArgException('As configurações de banco de dados devem ser um array ou um arquivo de configuração.');
        }
    }

    /**
     * Method getDBCongig
     * Restaga configurações do banco de dados
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public static function getDBConfig ()
    {
        return self::$dbConfigs;
    }

    /**
     * Method routeDynamic
     * Atribui valor a $routeDynamic
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param bool $arg
     */
    public function routeDynamic ($arg)
    {
        if (is_bool($arg)) {
            $this->routeDynamic = $arg;
        } else {
            InvalidArgException('Parâmetro deve ser um boleano.');
        }
    }

    /**
     * Method appDirTpl
     * Atribur valor a $appDirTpl ou se o parametro for null retornar o valor
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    public static function appDirTpl ($dir = null)
    {
        if (is_null($dir)) {
            return self::$appDirTpl;
        } else {
            if (preg_match('/[\\\\|\/]$/', $dir)) {
                self::$appDirTpl = $dir;
            } else {
                self::$appDirTpl = $dir . '/';
            }
        }
    }

    /**
     * Method sessionAutoStart
     * Instancia a classe session
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     */
    public function sessionAutoStart ($bool)
    {
        $this->sessionAutoStart = $bool;
    }

    /**
     * Method dbMain
     * informs or get the main application database
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return mixed
     */
    public static function dbMain ($db = null)
    {
        if (is_null($db)) {
            return self::$dbMain;
        } else {
            self::$dbMain = $db;
        }
    }

    /**
     * Method setNewVarConfig
     * set new var configuration
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return void
     */
    public static function setNewVarConfig ($config, $value)
    {
        self::$appConfigs[$config] = $value;
    }

    /**
     * Method getVarConfig
     * get var configuration
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @access public
     * @return mixed
     */
    public static function getVarConfig ($config)
    {
        return self::$appConfigs[$config];
    }
}
