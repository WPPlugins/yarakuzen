<?php
/**
 * Client library for the Yaraku REST API.
 * PHP Version 5
 * @category	YarakuZen
 * @package	  ClientLibrary
 * @author		Marcelo C. de Freitas <marcelo@yaraku.com>
 * @copyright 2015 Yaraku, Inc.
 * @license		MIT
 * @link			http://www.yarakuzen.com
 */

/**
 * Used to declare the available tiers for using with the API.
 *
 * @category	YarakuZen
 * @package		ClientLibrary
 * @author		Marcelo C. de Freitas <marcelo@yaraku.com>
 * @copyright 2015 Yaraku, Inc.
 * @license		MIT
 * @link			http://www.yarakuzen.com
 */
abstract class YarakuZenApi_Tier {
	public abstract function toString();
}

class YarakuZenApi_CasualTier extends YarakuZenApi_Tier {
	public function toString() {
		return 'casual';
	}
}

class YarakuZenApi_StandardTier extends YarakuZenApi_Tier {
	public function toString() {
		return 'standard';
	}
}

class YarakuZenApi_BusinessTier extends YarakuZenApi_Tier {
	public function toString() {
		return 'business';
	}
}

/**
 * A text block that will be sent to the YarakZen API.
 * Each method actually sets the variable for the same name.
 * For more information, see the official API documentation.
 *
 * @category	YarakuZen
 * @package		ClientLibrary
 * @author		Marcelo C. de Freitas <marcelo@yaraku.com>
 * @copyright 2015 Yaraku, Inc.
 * @license		MIT
 * @link			http://www.yarakuzen.com
 */
class YarakuZenApi_TextData {
	public function customData( $customData ) {
		$this->customData = $customData;
		return $this;
	}

	public function text( $text ) {
		$this->text = $text;
		return $this;
	}

	public function textFromFile( $fileName ) {
		$this->text = file_get_contents( $fileName );
		return $this;
	}
}

/**
 * This represents the data being sent to the API.
 * Each method actually sets the variable for the same name,
 * except for the addText method.
 * For more information, see the official API documentation.
 *
 * @category	YarakuZen
 * @package		ClientLibrary
 * @author		Marcelo C. de Freitas <marcelo@yaraku.com>
 * @copyright 2015 Yaraku, Inc.
 * @license		MIT
 * @link			http://www.yarakuzen.com
 */
class YarakuZenApi_RequestPayload {

	public function lcSrc( $source ) {
		$this->lcSrc = $source;
		return $this;
	}

	public function lcTgt( $target ) {
		$this->lcTgt = $target;
		return $this;
	}

	public function machineTranslate( $machineTranslate = true ) {
		$this->machineTranslate = $machineTranslate != false;
		return $this;
	}

	public function tier( YarakuZenApi_Tier $tier ) {
		$this->tier = $tier->toString();
		return $this;
	}

	public function quote( $quote = true ) {
		$this->quote = $quote ? 1 : 0;
		return $this;
	}

	public function persist( $persist = true ) {
		$this->persist = $persist;
		return $this;
	}

	public function addText( YarakuZenApi_TextData $text ) {
		if ( ! isset( $this->texts ) ) {
			$this->texts = array();
		}

		$this->texts[] = $text;
		return $this;
	}
}

/**
 * The client is responsible for actually handling the call and
 * signing the request.
 *
 * @category	YarakuZen
 * @package		ClientLibrary
 * @author		Marcelo C. de Freitas <marcelo@yaraku.com>
 * @copyright 2015 Yaraku, Inc.
 * @license		MIT
 * @link			http://www.yarakuzen.com
 */
class YarakuZenApi_Client {

  const HTTP_POST = 1;
  const HTTP_GET = 2;

	private $publicKey;
	private $privateKey;

	/////////////////////////////////////////////////////////
	// Atributes that shouldn't actually be changed often //
	///////////////////////////////////////////////////////

	// default to Yaraku production env
	protected $_url = '';

	// default to 5 min
	protected $_timeout = 300;

	protected $_httpUser = null;
	protected $_httpPass;

	protected $_userAgent = "YarakuZen PHP Client v1.0";
	protected $_referer = "";

	/**
	 * When using HTTPS disable the certificate validation
	 */
	protected $_insecure = false;

	protected $_source = "jp";
	protected $_target = "en";

	/**
	 * The charset for the request
	 */
	protected $_charset = "UTF-8";

	/**
	 * Initializes the client using the given API Key and
	 * Secret generated at Yaraku.
	 *
	 * @param publicKey the API publicKey
	 * @param privateKey the API privateKey
	 */
	function __construct( $publicKey, $privateKey ) {
		$this->publicKey = $publicKey;
		$this->privateKey = $privateKey;
		$this->_url = yarakuzen_api_base_url();
	}

	////////////////////////////
	// Some Extra Parameters //
	//////////////////////////

	/**
	 * Sets an alternative URL for calling the API.
	 *
	 * @param	url (optional) the URL for YarakuZen.
	 * @return $this
	 */
	public function url( $url ) {
		$this->_url = $url;
		return $this;
	}

	/**
	 * Timeout for each request, in sec
	 * @return $this
	 */
	public function timeout( $timeout ) {
		$this->_timeout = $timeout;
		return $this;
	}

	/**
	 * Disables the certificate validation for HTTPS requests if
	 * parameter is unset or true.
	 * @return $this
	 */
	public function insecure( $insecure = true ) {
		$this->_insecure = ! isset( $insecure ) || $insecure == true;
		return $this;
	}

	/**
	 * Use HTTP autentication on top of publicKey/privateKey auth
	 *
	 * @param	$username the username for HTTP auth
	 * @param	$password the password for HTTP auth
	 * @return $this
	 */
	public function auth( $username, $password ) {
		$this->_httpUser = $username;
		$this->_httpPass = $password;
		return $this;
	}

	/**
	 * Sets the userAgent informed to the server
	 *
	 * @param	$userAgent should be a string that identifies your application to the server.
	 * @return $this
	 */
	public function userAgent( $userAgent ) {
		$this->_userAgent = $userAgent;
		return $this;
	}

	/**
	 * Set the referer informed to the server.
	 *
	 * @param	$referer the referer
	 * @return $this
	 */
	public function referer( $referer ) {
		$this->_referer = $referer;
		return $this;
	}

	/**
	 * Sets the charset for this request.
	 *
	 * @param	$charset the charset of the request
	 * @return $this
	 */
	public function charset( $charset ) {
		$this->_charset = $charset;
		return $this;
	}

	//////////////////////
	// Text API Method //
	////////////////////

	public function postTexts( $payload ) {
		return $this->__callApi( YarakuZenApi_Client::HTTP_POST,
			'texts', $payload );
	}

  /**
   * @param string $customData
   * @param int $count
   * @return mixed
   * @throws Exception
   */
  public function getTextsByCustomData( $customData, $count = 10 ) {
    $params = array( "customData" => $customData );
    return $this->getTexts( $params, $count );
  }

  /**
   * @param array $params
   * @param int $count
   * @return mixed
   * @throws Exception
   */
  private function getTexts( array $params, $count = 10 ) {
    $params['count'] = $count;
    return $this->__callApi( YarakuZenApi_Client::HTTP_GET,
      "texts", $params );
  }

	////////////////////
	// Inner Working //
	//////////////////

	/**
	 * Sign the given payload, returning it.
	 */
	protected function __sign( $payload ) {
		$payload->publicKey = $this->publicKey;
		$payload->timestamp = time(); // A Unix timestamp
		$payload->signature = hash_hmac( 'sha1',
			$payload->timestamp.$this->publicKey,
			$this->privateKey ); // Create a sha1 hash

		return $payload;
	}

	/**
	 * Return the given payload as a JSON encoded string of an object.
	 * Make sure it's actually signed before returning.
	 */
	protected function __preparePayload( $payload ) {
		if ( is_object( $payload ) ) {
			$obj = clone $payload;
		} elseif( is_array( $payload ) ) {
			$obj = (object) $payload;
		} else {
			$obj = (object) array();
		}

		$o = $this->__sign( $obj );
		return http_build_query( $o );
	}

	/**
	 * Calls the API with the given payload returning the response
	 * in proper PHP object.
	 */
	protected function __callApi( $httpMethod, $apiMethod, $payload ) {
		$curl = curl_init();
		$pl = $this->__preparePayload( $payload );

		curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );

		// we need the response as a string
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		if ( $this->_httpUser != null ) {
			curl_setopt( $curl, CURLOPT_USERPWD,
				$this->_httpUser . ':' . $this->_httpPass );
		}

		switch ( $httpMethod ) {
			case YarakuZenApi_Client::HTTP_POST:
				curl_setopt( $curl, CURLOPT_POST, true );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $pl );
        $curlOptUrl = $this->_url . "/" . $apiMethod;
				break;
    	case YarakuZenApi_Client::HTTP_GET:
        $curlOptUrl = $this->_url . "/" . $apiMethod . "?" . $pl;
        break;
			default:
				throw new Exception( "HTTP Method Not Supported" );
		}

		curl_setopt( $curl, CURLOPT_URL, $curlOptUrl );

		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, ! $this->_insecure );
		curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
		curl_setopt( $curl, CURLOPT_REFERER, $this->_referer );

		$contentType = 'application/x-www-form-urlencoded;charset='
			. $this->_charset;

		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			"Content-type: $contentType",
			"User-Agent: YarakuZen WordPress Client" ) );

		$this->_response = curl_exec( $curl );
		$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		return json_decode( $this->_response, true );
	}
}
