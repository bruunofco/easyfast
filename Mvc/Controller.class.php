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

namespace EasyFast\Mvc;

use EasyFast\App;

/**
 * Abstract Class Controller
 * Classe Abstrata responsável pelo Controller
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Mvc
 */
abstract class Controller
{
    private $rest;

    /**
     * Method addRestfulServer
     * Add restful server
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $method
     * @param string $url
     * @param string $methodClass
     */
    protected function rest($method, $url, $methodClass)
    {
        App::getServerRestful()->server($method, $url, array($this, $methodClass));
    }


}
