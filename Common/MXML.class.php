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

use DOMXPath;
use DomElement;
use DOMDocument;
use EasyFast\Exceptions\EasyFastException;

/**
 * Class MXML
 * Classe para manipulação de XML
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 * @package Common
 */
class MXML extends DOMDocument
{
    private $find;
    private $namespace;

    /**
     * Method __construct
     * Desabilita erros externos
     * Não preserva espaços em branco
     * Formata a saida
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = null, $encoding = null)
    {
        libxml_use_internal_errors(true);
        $this->preserveWhiteSpace = false;
        parent::__construct($version, $encoding);
        $this->formatOutput = true;
    }

    /**
     * Method getErrors
     * Obtêm os o erros
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return Array
     */
    public function getErrors()
    {
        return libxml_get_errors();
    }

    /**
     * Method load
     * Carrega um arquivo XML e atribui a propriedade $dom
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $dirXml Arquivo ou URL do XML
     * @param string $option Opções
     * @param bool $checkXml Check XML is valid
     * @return void
     * @throws EasyFastException
     */
    public function load($dirXml, $option = null, $checkXml = false)
    {
        if (!file_exists($dirXml)) {
            throw new EasyFastException("File \"$dirXml\"not found.");
        }

        $this->preserveWhiteSpace = false;
        parent::load($dirXml, $option);
        parent::xinclude();
        if (!parent::validate() && $checkXml) {
            throw new EasyFastException('Invalid XML File.');
        }
    }

    /**
     * Method loadXml()
     * Carrega XML em formato string e atribui a propriedade $dom
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param string $xml Arquivo ou URL do XML
     * @param string $option Opções
     * @return void
     * @throws EasyFastException
     */
    public function loadXML($stringXml, $option = null)
    {
        $this->preserveWhiteSpace = false;
        parent::loadXML($stringXml, $option);
        if (parent::validate()) {
            throw new EasyFastException('Invalid XML.');
        }

    }

    /**
     * Method checkExistsTag()
     * Verifica se tag existe no arquivo XML
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $tag Nome da Tag
     * @return boolean
     */
    public function checkExistTag($tag)
    {
        if ($this->getElementsByTagName($tag)->length == 0) {
            return false;
        }
        return true;
    }

    /**
     * Method getTag()
     * Verifica existencia da tag, caso exista retorna tag
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $tagName Nome da tag
     * @return DOMElement
     */
    public function getTag($tagName)
    {
        if ($this->checkExistTag($tagName)) {

            if ($this->getElementsByTagName($tagName)->length > 1) {
                $return = array();
                for ($i = 0; $i <= $this->getElementsByTagName($tagName)->length - 1; $i++) {
                    $return["item_$i"] = $this->getElementsByTagName($tagName)->item($i);
                }
                return (object)$return;
            }
            return (object)$this->getElementsByTagName($tagName)->item(0);
        }
        return false;
    }

    /**
     * Method getTagValue()
     * Verifica existencia da tag, caso exista retorna valor da tag
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $tagName Nome da tag
     * @return String|Object retorna string caso encontre apenas uma tag e objeto quanto encontra mais de uma.
     */
    public function getTagValue($tagName)
    {
        if ($this->checkExistTag($tagName)) {

            if ($this->getElementsByTagName($tagName)->length > 1) {
                $return = array();
                for ($i = 0; $i <= $this->getElementsByTagName($tagName)->length - 1; $i++) {
                    $return["item_$i"] = trim($this->getElementsByTagName($tagName)->item($i)->nodeValue);
                }
                return (object)$return;
            }
            //return (object) array('item_0' => trim($this->getElementsByTagName($tagName)->item(0)->nodeValue));
            return $this->getElementsByTagName($tagName)->item(0)->nodeValue;
        }
        return false;
    }

    /**
     * Method getTagAttr()
     * Verifica existencia da tag, caso exista retorna valor da tag
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $tagName Nome da tag
     * @param String $attr Atributo a ser recuperado
     * @return String|Object quando encontrado apenas uma tag|quando encontrado mais de uma tag
     * @throws EasyFastException
     */
    public function getTagAttr($tagName, $attr)
    {
        if ($this->checkExistTag($tagName)) {
            $return = array();
            $i = 0;
            if (count((array)$this->getTag($tagName)) >= 2) {
                foreach ($this->getTag($tagName) as $tag) {
                    if ($tag->hasAttribute($attr)) {
                        $return["item_$i"] = $tag->getAttribute($attr);
                        $i++;
                    }
                }
                if ($i == 0) {
                    throw new EasyFastException("Attribute \"$attr\" not found in Tag \"$tagName\"");
                }
                return (object)$return;
            } else {
                if (!$this->getTag($tagName)->hasAttribute($attr)) {
                    throw new EasyFastException("Attribute \"$attr\" not found in Tag \"$tagName\"");
                }

                return $this->getTag($tagName)->getAttribute($attr);
            }
        }
        return false;
    }

    /**
     * Method createAttribute
     * Cria atributo e atribui valor ao atributo
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $attr
     * @param String $value
     * @return DOMDocument
     */
    public function createAttribute($attr, $value = null)
    {
        $xml = parent::createAttribute($attr);
        if (!is_null($value)) {
            $xml->value = $value;
        }
        return $xml;
    }

    /**
     * Method createElement
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @return DOMDocument
     */
    public function createElement($name, $value = null)
    {
        return parent::createElement($name, $value);
    }

    /**
     * Method query
     * Busca Tag no XML com o criterio
     * @author Bruno Oliveira <bruno@salluzweb.com.br>
     * @param String $query
     * @param DOMElement|null $context
     * @param string $namespace
     * @param string $prefix
     * @return DOMXPath
     * @throws EasyFastException
     */
    public function query($query, $context = null, $namespace = null, $prefix = 'ns')
    {
        $find = new DOMXPath($this);

        if (!is_null($namespace)) {
            $find->registerNamespace($prefix, $namespace);
        }

        $return = $find->query($query, $context);

        return $return;
    }

    /**
     * @param DOMElement $element
     * @return string
     */
    public static function elementToXml(DOMElement $element)
    {
        $newdoc = new \DOMDocument('1.0', 'utf-8');
        $cloned = $element->cloneNode(true);
        $newdoc->appendChild($newdoc->importNode($cloned, true));
        return $newdoc->saveXML();
    }

    /**
     * @param DOMElement $element
     * @param string $namespace
     * @return mixed
     */
    public static function elementToJson(DOMElement $element, $namespace = null)
    {
        $obj = simplexml_load_string(self::elementToXml($xml));
        return json_encode($obj);
    }
}
