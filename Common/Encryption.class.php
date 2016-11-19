<?php
/*
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

use EasyFast\Exceptions\EasyFastException;
use EasyFast\Exceptions\InvalidArgException;

/**
 * Class Encryption
 * Contem métodos de criptografia
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package EasyFast\Common
 */
class Encryption
{
    private static $saltPrefix  = '2a';
    private static $defaultCost = '08';
    private static $saltLength  = '22';

    /**
     * Method setCost
     * Seta valor ao cost
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param int $val
     * @throws InvalidArgException
     */
    public function setCost ($val)
    {
        if ($val >= 4 && $val <= 31) {
            self::$defaultCost = $val;
        } else {
            throw new InvalidArgException('Value must be an integer between 4 and 31.');
        }
    }

    /**
     * Method generateSalt
     * Gera Salt aleatório
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return string
     */
    public function generateSalt ()
    {
        $salt = str_replace('+', '.', base64_encode(uniqid(mt_rand(), true)));

        return substr($salt, 0, self::$saltLength);
    }

    /**
     * Method generateHash
     * Gera senha no padrão Blowfish
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $val String a ser codificada
     * @return string
     */
    public function generateHash ($val)
    {
        return crypt($val, '$' . self::$saltPrefix .'$'. self::$defaultCost . '$' . $this->generateSalt() . '$');
    }

    /**
     * Method check
     * Verifica se hash e string são compativeis
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $string
     * @param String $hash
     * @return boolean
     * @throws EasyFastException
     */
    public function check ($string, $hash)
    {
        if (crypt($string, $hash) === $hash) {
            return true;
        } else {
            throw new EasyFastException('Hash was not generated from that string');
        }
    }
}