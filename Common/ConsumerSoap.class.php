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
	* @var array|string with the request to send
	*/
	private $request;

    /**
    * @var array with the retunr of function and other informations
    */
    private $return;

	/**
     * @var string with the URL for WSDL document
     */
    private $wsdl;

    /**
     * @var array with options for Soap Request
     * @see http://www.php.net/manual/en/soapclient.soapclient.php/
     */
    protected $soapOptions;

    /**
     * @var string with the URL for default namespace
     */
    protected $namespace;

    /**
     * @var SoapHeader with formated header of Soap Request
     */
    protected $header;

    /**
    * @var boolean that indicates if the response must contain the headers of the response
    */
    protected $outputHeadersIndicator;

    /**
    * @var boolean that indicates if the debug of request is needed
    */
    protected $debugIndicator;

    /**
    *@var boolean that indicates if the request must be made or not (the class could be use just for debug)
    */
    protected $sendRequestIndicator;


	/**
     * Method __construct
     * Create the object and set the wsdl
     * @author James Miranda <jameswpm@gmail.com>
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl = null, $options = null)
    {
    	$this->wsdl = $wsdl;
    	$this->soapOptions = $options;
    	$this->namespace = null; // default
        $this->debug = false; // default
        $this->sendRequest = true;//default
        $this->outputHeadersIndicator = false;//default
        $this->return = array();
    	$this->client = parent::__construct($this->wsdl, $this->soapOptions);
    }

	/**
     * Method setNamespace
     * Set a default namespáce for request overriding the null value
     * @author James Miranda <jameswpm@gmail.com>
     * @param string $ns
     */
    public function setNamespace($ns)
    {
    	$this->namespace = $ns;
    }

    /**
     * Method setRequest
     * Set a associative array or formated XML to do the soap request
     * @author James Miranda <jameswpm@gmail.com>
     * @param array|string $request
     */
    public function setRequest($request)
    {
    	if (is_array($request)){
        	$this->request = $request;
        }
        else {
            //String with the XML for the request
        	$this->request = new SoapVar($xml, XSD_ANYXML, null, $this->namespace);
        }
    }

    /**
     * Method setHeader
     * Set a associative array or ArrayObject to use as SoapHeader (optional)
     * @author James Miranda <jameswpm@gmail.com>
     * @param string $headerNS with the URL for Header Namespace
     * @param string $headerName with the tag name of Header
     * @param array|ArrayObject $headerBody
     */
    public function setHeader($headerNS, $headerName, $headerBody)
    {
        if (is_null($headerNS)) {
            $headerNS = $this->header;
        }
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
        $this->__setSoapHeaders($this->header);
    }

    /**
     * Method getRequest
     * Returns the setted request
     * @author James Miranda <jameswpm@gmail.com>
     * @return array|string
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
    * Method configurePreferences
    * Set the preferences for the request (optional)
    * @author James Miranda <jameswpm@gmail.com>
    * @param boolean $debugIndicator set if the debug is on
    * @param boolean $sendRequestIndicator set if the request must be sent
    * @param boolean $outputHeadersIndicator set if the headers of response must be in the response
    */
    public function configurePreferences ($debugIndicator,$sendRequestIndicator,$outputHeadersIndicator)
    {
        $this->debugIndicator = $debugIndicator;
        $this->sendRequestIndicator = $sendRequestIndicator;
        $this->outputHeadersIndicator = $outputHeadersIndicator;
    }

    /**
     * Method __call
     * Make the SoapCall of a function. Override the original function
     * @author James Miranda <jameswpm@gmail.com>
     * @throws SoapFault
     */
    public function __call($function,$args) 
    {
        try{
            if ($this->outputHeadersIndicator) {
                $outputHeaders = array();
                $this->return['response'] = $this->__soapCall($function, (array)$this->request, null, null, $outputHeaders);
                $this->return['xmlRequest'] = $this->__getLastRequest(); //overwrite the debug request if is setted
                $this->return['xmlResponse'] = $this->__getLastResponse();
                $this->return['responseHeaders'] = outputHeaders;
            }
            else {
                $this->return['response'] = $this->__soapCall($function, (array)$this->request);
                $this->return['xmlRequest'] = $this->__getLastRequest(); //overwrite the debug request if is setted
                $this->return['xmlResponse'] = $this->__getLastResponse();
            }
        }catch (SoapFault $fault) {
            throw $fault;
        }

    }

    /**
     * Method __doRequest
     * Make the request of a function with a optional debug. Override the original function
     * @author James Miranda <jameswpm@gmail.com>
     */
    public function __doRequest($request, $location, $action, $version, $one_way=0) {
        if ($this->debugIndicator) {
	    	//use this to see the request XML BEFORE send it
	        $doc = new DOMDocument;
	        $doc->preserveWhiteSpace = false;
	        $doc->loadxml($request);
	        $doc->formatOutput = true;
	        $formatedRequest = $doc->savexml();
	        $this->return['xmlRequest'] = $formatedRequest;
	    }
        if ($this->sendRequestIndicator) {
            return parent::__doRequest($request, $location, $action, $version, $one_way);
        }
  	}
}