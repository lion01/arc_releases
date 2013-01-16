<?php
require( 'HTTP\OAuth\Consumer.php' );
require( 'HTTP\Request2\Adapter\Curl.php' );

class HTTP_OAuth_Consumer_Arc extends HTTP_OAuth_Consumer
{
	/**
	 * Gets instance of HTTP_OAuth_Consumer_Request_Arc
	 *
	 * @see accept()
	 * @return HTTP_OAuth_Consumer_Request_Arc
	 */
	public function getOAuthConsumerRequest()
	{
		if (!$this->consumerRequest instanceof HTTP_OAuth_Consumer_Request_Arc) {
			$this->consumerRequest = new HTTP_OAuth_Consumer_Request_Arc;
			if( isset( $this->_config ) ) {
				$this->consumerRequest->setConfig( $this->_config );
			}
		} 
		return $this->consumerRequest;
	}
	
	/**
	 * Allow configuration of the Request
	 * 
	 * @param array $config  Associative array of config options suitable to send to HTTP_Request2's setConfig function
	 * @see HTTP_Request2::setConfig
	 */
	public function setConfig( $config = array() )
	{
		$this->_config = $config;
	}
}



class HTTP_OAuth_Consumer_Request_Arc extends HTTP_OAuth_Consumer_Request
{
	protected $_config = array();
	
	/**
	 * Returns $this->request if it is an instance of HTTP_Request.  If not, it 
	 * creates one.
	 * 
	 * @return HTTP_Request2
	 */
	protected function getHTTPRequest2()
	{
		parent::getHTTPRequest2();
		
		// set up config info
		$this->request->setConfig( $this->_config );
		
		return $this->request;
	}
	
	/**
	 * Public function to allow setting of config options to be sent to protected Request object
	 * 
	 * @param array $config  Associative array of config options suitable to send to HTTP_Request2's setConfig function
	 * @see HTTP_Request2::setConfig
	 */
	public function setConfig( $config )
	{
		$this->_config = $config;
	}
}

class HTTP_Request2_Adapter_Curl_Arc extends HTTP_Request2_Adapter_Curl
{
	
	// The following is a copy of the function of the same name from HTTP_Request2_Adapter_Curl
	// There are only a couple of differences and they're flagged. 
    /**
     * Creates a new cURL handle and populates it with data from the request
     *
     * @return   resource    a cURL handle, as created by curl_init()
     * @throws   HTTP_Request2_LogicException
     */
    protected function createCurlHandle()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            // setup write callbacks
            CURLOPT_HEADERFUNCTION => array($this, 'callbackWriteHeader'),
            CURLOPT_WRITEFUNCTION  => array($this, 'callbackWriteBody'),
            // buffer size
            CURLOPT_BUFFERSIZE     => $this->request->getConfig('buffer_size'),
            // connection timeout
            CURLOPT_CONNECTTIMEOUT => $this->request->getConfig('connect_timeout'),
            // save full outgoing headers, in case someone is interested
            CURLINFO_HEADER_OUT    => true,
            // request url
            CURLOPT_URL            => $this->request->getUrl()->getUrl()
        ));

        // set up redirects
        if (!$this->request->getConfig('follow_redirects')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        } else {
            if (!@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true)) {
                throw new HTTP_Request2_LogicException(
                    'Redirect support in curl is unavailable due to open_basedir or safe_mode setting',
                    HTTP_Request2_Exception::MISCONFIGURATION
                );
            }
            curl_setopt($ch, CURLOPT_MAXREDIRS, $this->request->getConfig('max_redirects'));
            // limit redirects to http(s), works in 5.2.10+
            if (defined('CURLOPT_REDIR_PROTOCOLS')) {
                curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            }
            // works in 5.3.2+, http://bugs.php.net/bug.php?id=49571
            if ($this->request->getConfig('strict_redirects') && defined('CURLOPT_POSTREDIR')) {
                curl_setopt($ch, CURLOPT_POSTREDIR, 3);
            }
        }

        // request timeout
        if ($timeout = $this->request->getConfig('timeout')) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }

        // set HTTP version
        switch ($this->request->getConfig('protocol_version')) {
        case '1.0':
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            break;
        case '1.1':
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }

        // set request method
        switch ($this->request->getMethod()) {
        case HTTP_Request2::METHOD_GET:
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case HTTP_Request2::METHOD_POST:
            curl_setopt($ch, CURLOPT_POST, true);
// *** Arc change:
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request->getBody() );
// *** end
            break;
        case HTTP_Request2::METHOD_HEAD:
            curl_setopt($ch, CURLOPT_NOBODY, true);
            break;
        case HTTP_Request2::METHOD_PUT:
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            break;
        default:
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->request->getMethod());
        }

        // set proxy, if needed
        if ($host = $this->request->getConfig('proxy_host')) {
            if (!($port = $this->request->getConfig('proxy_port'))) {
                throw new HTTP_Request2_LogicException(
                    'Proxy port not provided', HTTP_Request2_Exception::MISSING_VALUE
                );
            }
            curl_setopt($ch, CURLOPT_PROXY, $host . ':' . $port);
            if ($user = $this->request->getConfig('proxy_user')) {
                curl_setopt(
                    $ch, CURLOPT_PROXYUSERPWD,
                    $user . ':' . $this->request->getConfig('proxy_password')
                );
                switch ($this->request->getConfig('proxy_auth_scheme')) {
                case HTTP_Request2::AUTH_BASIC:
                    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                    break;
                case HTTP_Request2::AUTH_DIGEST:
                    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_DIGEST);
                }
            }
            if ($type = $this->request->getConfig('proxy_type')) {
                switch ($type) {
                case 'http':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
                case 'socks5':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    break;
                default:
                    throw new HTTP_Request2_NotImplementedException(
                        "Proxy type '{$type}' is not supported"
                    );
                }
            }
        }

        // set authentication data
        if ($auth = $this->request->getAuth()) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth['user'] . ':' . $auth['password']);
            switch ($auth['scheme']) {
            case HTTP_Request2::AUTH_BASIC:
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                break;
            case HTTP_Request2::AUTH_DIGEST:
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            }
        }

        // set SSL options
        foreach ($this->request->getConfig() as $name => $value) {
            if ('ssl_verify_host' == $name && null !== $value) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $value? 2: 0);
            } elseif (isset(self::$sslContextMap[$name]) && null !== $value) {
                curl_setopt($ch, self::$sslContextMap[$name], $value);
            }
        }

        $headers = $this->request->getHeaders();
        // make cURL automagically send proper header
        if (!isset($headers['accept-encoding'])) {
            $headers['accept-encoding'] = '';
        }

        if (($jar = $this->request->getCookieJar())
            && ($cookies = $jar->getMatching($this->request->getUrl(), true))
        ) {
            $headers['cookie'] = (empty($headers['cookie'])? '': $headers['cookie'] . '; ') . $cookies;
        }

        // set headers having special cURL keys
        foreach (self::$headerMap as $name => $option) {
            if (isset($headers[$name])) {
                curl_setopt($ch, $option, $headers[$name]);
                unset($headers[$name]);
            }
        }

// *** Arc change:
/*
        $this->calculateRequestLength($headers);
        if (isset($headers['content-length'])) {
            $this->workaroundPhpBug47204($ch, $headers);
        }
// */
// end
        
        // set headers not having special keys
        $headersFmt = array();
        foreach ($headers as $name => $value) {
            $canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
            $headersFmt[]  = $canonicalName . ': ' . $value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFmt);

        return $ch;
    }
}
?>