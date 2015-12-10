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
use ReflectionMethod;
use ReflectionObject;
use RecursiveArrayIterator;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class Utils
 * Métodos util para utilizar na aplicação
 * @package EasyFast\Common
 */
trait Utils
{
    /**
     * Method jsonEncode
     * Transforma String e Array em json
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string|array $val - valor a ser convertido a json
     * @param string|array|null $key - Poder ser um valor null, caso exista será usado como identificador
     * @example jsonEncode("Key1 => Value | Key2 => Value | Key3 => Value") = Transforma string em array e converte em jSon;
     * @example jsonEncode(array('Key1' => 'Value', 'Key2' => 'Value')) = Converte array em jSon;
     * @example jsonEncode('Key', 'Value') = Transforma parametros em array e converte em Json;
     * @example jsonEncode('My value') = Transforma string em array e converte em json, identificador é 0;
     * @return string
     */
    public static function jsonEncode ($val, $key = null) {
        //TODO: Repensar lógica deste método
        if (is_array($val)) {
            
            if (isset($val[0]) && is_object($val[0])) {
                $arr = array();
                foreach ((array) $val as $k => $v) {
                    $arr[] = (array) $v;
                }
                return json_encode($arr);
            } else {
                return json_encode($val);
            }
            
        } elseif (is_array($val) && is_array($key)) {
            $string = null;
            foreach ($val as $ide => $value) {
                $string .= "$key[$ide] => $value,";
            }
            $string = substr($string, -1);
            return json_encode(array($string));
        } elseif (is_object($val)) {
            return json_encode($val);
        } elseif (!is_null($key)) {
            return json_encode(array($key => $val));
        } else {
            $val = explode('|', $val);
            $array = Array();
            foreach ($val as $value) {
                $value = explode('=>', $value);

                if (array_key_exists(1, $value)) {
//                    if (is_array($value[1])) {
//                        foreach ($value[1] as $k => $v) {
//                            $array[trim($k)] = trim($v);
//                        }
//                    } else {
                        $array[trim($value[0])] = trim($value[1]);
//                    }

                } else {
                    array_push($array, $value[0]);
                }
            }

            return json_encode($array);
        }
    }

    /**
     * Method hiphenToCamelCase
     * Transforma hiphen-case para camelCase
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $string
     * @return string
     */
    public static function hiphenToCamelCase ($string)
    {
        $str1 = array('-a','-b','-c','-d','-e','-f','-g','-h','-i','-j','-k','-l','-m','-n','-o','-p','-q','-r','-s','-t','-u','-v','-w','-x','-y','-z');
        $str2 = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        return str_replace($str1, $str2, $string);
    }

    /**
     * Method snakeToCamelCase
     * Transforma snake_case para camelCase
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $string
     * @return string
     */
    public static function snakeToCamelCase ($string)
    {
        $str1 = array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z');
        $str2 = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        return ucfirst(str_replace($str1, $str2, $string));
    }

    /**
     * Method camelToSnakeCase
     * Transforma camelCase para skane_case
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $string
     * @return string
     */
    public static function camelToSnakeCase ($string)
    {
        $str1 = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $str2 = array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z');
        $string = substr(str_replace($str1, $str2, $string), 0, 1) == '_' ? substr(str_replace($str1, $str2, $string),
            1) : str_replace($str1, $str2, $string);
        return $string;
    }

    /**
     * Method requireParam
     * Checa se existe parametro dentro do objeto ou array
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param array|object $data
     * @param string $param
     * @throws EasyFastException
     * @access public
     * @return void
     */
    public static function requireParam ($data, $param)
    {
        if (is_object($data)){
            if (is_array($param)) {
                foreach ($param as $p) {
                    if (!property_exists($data, $p)) {
                        throw new EasyFastException("\"$param\" parameter is required.");
                    }
                }
            } else {
                if (!property_exists($data, $param)) {
                    throw new EasyFastException("\"$param\" parameter is required.");
                }
            }
        } elseif (is_array($data)) {
            $array = new RecursiveArrayIterator($data);
            self::arrayExistsKeys($data, $param);

        } else {
            throw new EasyFastException('Data deve ser um objeto ou um array.');
        }
    }


    /**
     * Method arrayExistsKeys
     * Verifica se existe indice em array
     * @param array $array
     * @param string $key
     * @throws EasyFastException
     * @return mixed
     */
    public static function arrayExistsKeys ($array, $key)
    {
        $array = new ArrayObject($array);

        if (is_array($key)) {
            print_r($key);
            foreach ($key as $k => $val) {
                if (!$array->offsetExists($k)) {
                    throw new EasyFastException("Chave inexistente \"$k\".");
                }
                if (is_array($val)) {
                    $array2 = new ArrayObject($array->offsetGet($k));
                    foreach ($val as $v) {
                        if (!$array2->offsetExists($v)) {
                            throw new EasyFastException("Chave inexistente \"$k => $v\".");
                        }
                    }
                }
            }
        } else {
            if (!$array->offsetExists($key)) {
                throw new EasyFastException("Chave inexistente \"$key\".");
            }
        }

        return true;
    }

    /**
     * Method decodeResquest
     * Decodes request and turns into object or string
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param bool $getString Return string or object
     * @access public
     * @return mixed
     */
    public static function decodeRequest ($getString = false)
    {
        $contentType = explode(';', $_SERVER['CONTENT_TYPE']);
        if(in_array('multipart/form-data', $contentType)) {
            return (object) $_POST;
        }

        if ($getString) {
            return file_get_contents("php://input");
        }

        $data = json_decode(file_get_contents("php://input"));
        if (json_last_error() == JSON_ERROR_NONE) {
            return $data;
        } else {
            parse_str(file_get_contents("php://input"), $result);
            return (object) $result;
        }
    }

    /**
     * Method callMethodArgsOrder
     * Instancia método e associa os parametros
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string|object $class
     * @param string $method
     * @param array $args
     * @throws EasyFastException
     */
    public static function callMethodArgsOrder ($class, $method, $args)
    {
        $arguments  = array();
        $refle      = new ReflectionMethod($class, $method);

        foreach ($refle->getParameters() as $arg) {
            if (isset($args[$arg->name])) {
                $arguments[$arg->name] = $args[$arg->name];
            } elseif ($arg->isDefaultValueAvailable()) {
                $arguments[$arg->name] = $arg->getDefaultValue();
            } else {
                throw new EasyFastException("It is mandatory to pass parameters, {$arg->name} is a parameter mandatory.");
            }
        }

        if (!is_object($class)) {
            $class = new $class;
        }

        call_user_func_array(array($class, $method), $arguments);
        exit();
    }

    /**
     * Method mask
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param $val
     * @param $mask
     * @return string
     */
    public static function mask ($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask)-1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    /**
     * getGUID
     * Return GUID
     * @author Bruno Oliveira <bruno.oliveira@riosoft.com.br>
     * @return string
     */
    public static function getGUID ()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = '-';
            return substr($charid, 0, 8).$hyphen
                   .substr($charid, 8, 4).$hyphen
                   .substr($charid,12, 4).$hyphen
                   .substr($charid,16, 4).$hyphen
                   .substr($charid,20,12);
        }
    }

    /**
     * arrayToObject
     * @param $array
     * @param $object
     * @return \stdClass
     */
    public static function arrayToObject($array, $object = '\stdClass')
    {
        $obj = new $object;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = self::arrayToObject($v);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }
}
