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

use SoapClient;
use SoapHeader;
use SoapVar;
use ArrayObject;
use DOMDocument;//TODO: Use MXML

/**
 * Class ConsumerSoap
 * Class to facilitate and expand the functions of the SoapClient
 * @author James Miranda <jameswpm@gmail.com>
 * @package Commom
 */
class ConsumerSoap extends SoapClient
{

	/**
	* @var Array|String with the request to send
	*/
	private $request;

	/**
     * @var String with the URL for WSDL document
     */
    protected $wsdl;

    /**
     * @var Array with options for Soap Request
     * @see http://www.php.net/manual/en/soapclient.soapclient.php/
     */
    protected $soapOptions;

    /**
     * @var String with the URL for default namespace
     */
    protected $namespace;

    /**
     * @var SaopHeader with formated header of Soap Request
     */
    protected $header;

	/**
     * Method __construct
     * Create the object and set the wsdl
     * @author James Miranda <jameswpm@gmail.com>
     * @param String $wsdl
     * @param Array $options
     */
    public function __construct($wsdl = null, $options = null)
    {
    	$this->wsdl = $wsdl;
    	$this->soapOptions = $options;
    	$this->namespace = null; // default
    	$this->client = parent::__construct($this->wsdl, $this->soapOptions);
    }

	/**
     * Method setNamespace
     * Set a default namespáce for request overriding the null value
     * @author James Miranda <jameswpm@gmail.com>
     * @param String $ns
     */
    public function setNamespace($ns)
    {
    	$this->namespace = $ns;
    }

    /**
     * Method setRequest
     * Set a associative array or formated XML to do the soap request
     * @author James Miranda <jameswpm@gmail.com>
     * @param Array|String $request
     */
    public function setRequest($request)
    {
    	if (is_array($request)){
        	$this->request = $request;
        }
        else {
        	$this->request = new SoapVar($xml, XSD_ANYXML, null, $this->namespace);
        }
    }

    /**
     * Method setHeader
     * Set a associative array or ArrayObject to use as SoapHeader (optional)
     * @author James Miranda <jameswpm@gmail.com>
     * @param String $headerNS with the URL for Header Namespace
     * @param String $headerName with the tag name of Header
     * @param Array|ArrayObject $headerBody
     */
    public function setHeader($headerNS = $this->namespace, $headerName, $headerBody)
    {
    	if (is_array($headerBody)) {
    		$this->header = new SoapHeader($headerNS, $headerName, $headerBody);
    	}
    	if (is_object($headerBody)) {
    		//case for ArrayObject, but also works with stdClass
    		foreach ($headerBody as $key => $var) {
    			$headerBody->$key = new SoapVar($var, XSD_STRING, null, null, "$key");
    		}
    		$headerVar = new SoapVar($headerBody, SOAP_ENC_OBJECT,null, null, $headerName);
    		$this->header = new SoapHeader($headerNS, $headerName, $headerVar);
    	}
        $this->cliente->__setSoapHeaders($this->header);
    }

    /**
     * Method getRequest
     * Returns the setted request
     * @author James Miranda <jameswpm@gmail.com>
     * @return Array|String
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Method getHeader
     * Returns the setted header
     * @author James Miranda <jameswpm@gmail.com>
     * @return SoapHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Method __doRequest()
     * Make the SoapCall of a function with a debug option. Override the original function
     * @author James Miranda <jameswpm@gmail.com>
     * @param String $op with the name of requested service
     * @return Array with the result
     * @throws SoapFault
     */
    public function __doRequest($request, $location, $action, $version, $one_way=0, $debug= false, $outputHeadersIndicator= false) {
	    if ($debug) {
	    	//use this to see the request XML BEFORE send it
	        $doc = new DOMDocument;
	        $doc->preserveWhiteSpace = false;
	        $doc->loadxml($request);
	        $doc->formatOutput = true;
	        $formatedRequest = $doc->savexml();
	        print_r($formatedRequest);
	        exit;
	    }
	    try{
            $output_headers = array();
            if ($outputHeadersIndicator) {
                return parent::__doRequest($request, $location, $action, $version, $one_way);
            }
            else {
                return parent::__doRequest($request, $location, $action, $version, $one_way);
                $lastRequest = $this->cliente->__getLastRequest();
            }
        }catch (Soapfault $fault) {
            throw $fault;
        }
  	}
}