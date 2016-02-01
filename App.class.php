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
    CONST VERSION    = '1.2';
    CONST NAME_FW    = 'EasyFast';
    CONST NAME_SPACE = 'EasyFast';
    CONST SITE       = 'https://github.com/bruunofco/easyfast/';
    CONST AUTHOR     = 'Bruno Oliveira';

    public function __construct()
    {
        header('Developed-With: ' . App::NAME_FW . ' | ' . App::SITE);
        $this->setDir(getcwd());
        spl_autoload_register(array($this, 'loader'));
    }

    /**
     * Method run
     * Executa a aplicação
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return void
     */
    public function run()
    {
        if ($this->getAppConfig('sessionAutoStart')) {
            new Session();
        }

        Route::interceptRequests();
    }

    /**
     * Method loader
     * Autoloader - dynamically include the required files
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $fileName Nome do arquivo a ser incluido na aplicação
     * @return void
     * @throws EasyFastException
     */
    private function loader($fileName)
    {
        $fileName = preg_filter("/\\\/", '/', $fileName);
        $fileCheck = explode(DIRECTORY_SEPARATOR, $fileName);
        if ($fileCheck[0] == self::NAME_SPACE) {
            unset($fileCheck[0]);
            $fileName = implode(DIRECTORY_SEPARATOR, $fileCheck);
            require_once __DIR__ . DIRECTORY_SEPARATOR . "{$fileName}.class.php";
        } else {
            if (file_exists(Config::getAppConfig('dir') . "{$fileName}.class.php")) {
                require_once Config::getAppConfig('dir') . "{$fileName}.class.php";
            } elseif (file_exists(strtolower(Config::getAppConfig('dir') . "{$fileName}.class.php"))) {
                require_once strtolower(Config::getAppConfig('dir') . "{$fileName}.class.php");
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
        //Registra AutoLoader
        spl_autoload_register(array($this, 'loader'));

        if (!is_object($class)) {
            $class = new $class;
        }

        call_user_func_array(array($class, $method), $params);
    }
}
