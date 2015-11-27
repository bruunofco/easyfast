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

namespace EasyFast;

include __DIR__ . '/Config/Config.class.php';
include __DIR__ . '/Http/Restful.class.php';

use EasyFast\Common\Utils;
use EasyFast\Config\Config;
use EasyFast\Sessions\Session;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class App
 * Main framework class, contain utils methods for application
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast
 * @access public
 */
class App extends Config
{
    /**
     * Constantes do framework
     */
    CONST VERSION    = '1.0';
    CONST NAME_FW    = 'EasyFast';
    CONST NAME_SPACE = 'EasyFast';
    CONST SITE       = 'https://github.com/bruunofco/easyfast/';
    CONST AUTHOR     = 'Bruno Oliveira';

    /**
     * Armazena objeto Registry
     * @var object $registry
     */
    public static $registry;

    /**
     * Method run
     * Executa a aplicação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return void
     */
    public function run ()
    {
        spl_autoload_register(array($this, 'loader'));
        if ($this->sessionAutoStart) {
            new Session();
        }
        $route = new Route();
        $route->intercepRequests();
    }

    /**
     * Method loader
     * Autoloader - dynamically include the required files
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $fileName Nome do arquivo a ser incluido na aplicação
     * @return void
     * @throws EasyFastException
     */
    private function loader ($fileName)
    {
        $fileCheck = explode('\\', $fileName);

        if ($fileCheck[0] == self::NAME_SPACE) {
            unset($fileCheck[0]);
            $fileName = implode(DIRECTORY_SEPARATOR, $fileCheck);
            require_once __DIR__ . DIRECTORY_SEPARATOR . "{$fileName}.class.php";
        } else {
            if (!isset(Config::getConfig()->App->Dir)) {
                throw new EasyFastException('Directory not defined');
            }
            if (file_exists(Config::getConfig()->App->Dir . "{$fileName}.class.php")) {
                require_once Config::getConfig()->App->Dir . "{$fileName}.class.php";
            } elseif (file_exists(strtolower(Config::getConfig()->App->Dir . "{$fileName}.class.php"))) {
                require_once strtolower(Config::getConfig()->App->Dir . "{$fileName}.class.php");
            }
        }
    }

    /**
     * Method execMethodBeforeRunApp
     * Add method execute before run app
     * @author Bruno Oliveira bruno@salluzweb.com.br>
     * @param object|string $class
     * @param string $method
     * @param array $params
     */
    public function execMethodBeforeRunApp ($class, $method, $params = array())
    {
        spl_autoload_register(array($this, 'loader'));

        if (!is_object($class)) {
            $class = new $class;
        }

        call_user_func_array(array($class, $method), $params);
    }
}
