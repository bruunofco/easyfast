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

use ArrayObject;
use EasyFast\Exceptions\LogicException;
use EasyFast\Exceptions\InvalidArgException;

/**
 * Class Registry
 * Pattern Registry
 * Centraliza objetos e cria um indice para facil localizar uma classe
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Class\Exceptions
 */
class Registry
{
    private static $instance;
    private $storage;

    /**
     * Method __construct
     * Pattern Singleton
     * Cria um ArrayObject na propriedade $this->storage
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     */
    protected function __construct()
    {
        $this->storage = new ArrayObject();
    }

    /**
     * Method getInstance
     * Recupera uma instância única da classe Registry
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return Registry
     */
    public static function getInstance ()
    {
        if (empty(self::$instance)) {
            self::$instance = new Registry();
        }
        return self::$instance;
    }

    /**
     * Method get
     * Recupera um objecto usando a chave setada junto ao objeto
     * @param string $obj Nome dado ao objecto na sua instanciação
     * @throws InvalidArgException Se não houver um registro para a chave especificada
     * @return mixed
     */
    public function get ($obj)
    {
        if (!$this->storage->offsetExists($obj)) {
            throw new InvalidArgException(sprintf('Not exists a registry for key "%s".', $obj));
        }
        return $this->storage->offsetGet($obj);
    }



    /**
     * Method set
     * Registra um objeto e seta uma chave de acesso para este objeto
     * @param string $key Chave de acesso ao objeto
     * @param mixed $obj Objeto a ser armagenado
     * @throws LogicException Se a chave já estiver registrada
     * @return void
     */
    public function set ($key, $obj)
    {
        if ($this->storage->offsetExists($key)) {
            throw new LogicException(sprintf('Already exists a registry for key "%s".', $key));
        }
        $this->storage->offsetSet($key, $obj);
    }

    /**
     * Method deleteRecord
     * Remove o registro segundo a chave passada como parâmetro
     * @param string $key Chave do objeto a ser removido
     * @throws InvalidArgException Se não houver um registro para a chave especificada
     * @return void
     */
    public function deleteRecord ($key)
    {
        if (!$this->storage->offsetExists($key)) {
            throw new InvalidArgException(sprintf('Not exists a registry for key "%s".', $key));
        }
        $this->storage->offsetUnset($key);
    }
}
