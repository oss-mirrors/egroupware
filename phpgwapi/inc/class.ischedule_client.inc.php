<?php
/**
 * EGroupware: iSchedule client
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage groupdav
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2012 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * iSchedule client: clientside of iSchedule
 *
 * @link https://tools.ietf.org/html/draft-desruisseaux-ischedule-03 iSchedule draft from 2013-01-22
 */
class ischedule_client
{
	/**
	 * Own iSchedule version
	 */
	const VERSION = '1.0';

	/**
	 * Required headers in DKIM signature (DKIM-Signature is always a required header!)
	 */
	const REQUIRED_DKIM_HEADERS = 'Host:iSchedule-Version:iSchedule-Message-ID:Content-Type:Originator:Recipient';

	/**
	 * URL to use to contact iSchedule receiver
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Recipient email addresses
	 *
	 * @param array
	 */
	private $recipients;

	/**
	 * Originator email address
	 *
	 * @var string
	 */
	private $originator;

	/**
	 * Private key of originators domain
	 *
	 * @var string
	 */
	private $dkim_private_key;

	/**
	 * Constructor
	 *
	 * @param string|array $recipients=null recipient email-address(es)
	 * @param string $url=null ischedule url, if it should NOT be discovered
	 * @throws Exception in case of an error or discovery failure
	 */
	public function __construct($recipients, $url=null)
	{
		$this->recipients = (array)$recipients;
		$this->originator = $GLOBALS['egw_info']['user']['account_email'];

		if (is_null($url))
		{
			list(,$domain) = explode('@', $this->recipients[0]);
			$this->url = self::discover($domain);
		}
		else
		{
			$this->url = $url;
		}

		$this->dkim_private_key = $GLOBALS['egw_info']['server']['dkim_private_key'];
	}

	/**
	 * Generate private/public key pair
	 *
	 * Private and public key are stored in api config as dkim_private_key / dkim_public_key and loaded automatic by constructor.
	 *
	 * @return string public key
	 */
	public static function generateKeyPair()
	{
		// Create the keypair
		$res = openssl_pkey_new();

		// Get private key
		openssl_pkey_export($res, $dkim_private_key);

		// Get public key
		$details = openssl_pkey_get_details($res);
		$dkim_public_key = $details['key'];

		// store both in config
		config::save_value('dkim_private_key', $dkim_private_key, 'phpgwapi');
		config::save_value('dkim_public_key', $dkim_public_key, 'phpgwapi');

		return $dkim_public_key;
	}

	const EMAIL_PREG = '/^([a-z0-9][a-z0-9._-]*)?[a-z0-9]@([a-z0-9](|[a-z0-9_-]*[a-z0-9])\.)+[a-z]{2,6}$/i';

	/**
	 * Set originator and (optional) DKIM private key
	 *
	 * @param string $originator
	 * @param string $dkim_private_key=null
	 * @throws Exception for invalid / not an email originator
	 */
	public function setOriginator($originator, $dkim_private_key=null)
	{
		if (!preg_match(self::EMAIL_PREG, $originator))
		{
			throw new Exception("Invalid orginator '$originator'!");
		}
		$this->originator = $originator;

		if (!is_null($dkim_private_key))
		{
			$this->dkim_private_key = $dkim_private_key;
		}
	}

	/**
	 * Discover iSchedule url of a given domain
	 *
	 * @param string $domain
	 * @return string discovered ischedule url
	 * @throws Exception in case of an error or discovery failure
	 */
	public static function discover($domain)
	{
		static $scheme2port = array(
			'https' => 443,
			'http' => 80,
		);

		$d = $domain;
		for($n = 0; $n < 3; ++$n)
		{
			if (!($records = dns_get_record($host='_ischedules._tcp.'.$d, DNS_SRV)) &&
				!($records = dns_get_record($host='_ischedule._tcp.'.$d, DNS_SRV)))
			{
				// try without subdomain(s)
				$parts = explode('.', $d);
				if (count($parts) < 3) break;
				array_shift($parts);
				$d = implode('.', $parts);
			}
		}
		if (!$records) throw new Exception("Could not discover iSchedule service for domain '$domain'!");

		// ToDo: do we need to use priority and weight
		$record = $records[0];

		$url = strpos($host, '_ischedules') === 0 ? 'https' : 'http';
		if ($scheme2port[$url] == $record['port'])
		{
			$url .= '://'.$record['target'];
		}
		else
		{
			$url .= '://'.$record['target'].':'.$record['port'];
		}
		$url .= '/.well-known/ischedule';

		return $url;
	}

	/**
	 * Post dkim signed message to recipients iSchedule server
	 *
	 * @param string $content
	 * @param string $content_type
	 * @param boolean $debug=false true echo request before posting
	 * @param int $max_redirect=3 maximum number of redirect before failing
	 * @return string
	 * @throws Exception with http status code and message, if server responds other then 2xx
	 */
	public function post_msg($content, $content_type, $debug=false, $max_redirect=3)
	{
		if (empty($this->dkim_private_key))
		{
			throw new Exception('You need to generate a key pair first!');
		}
		$url_parts = parse_url($this->url);
		$headers = array(
			'Host' => $url_parts['host'].($url_parts['port'] ? ':'.$url_parts['port'] : ''),
			'iSchedule-Version' => self::VERSION,
			'iSchedule-Message-ID' => uniqid(),
			'Content-Type' => $content_type,
			'Originator' => $this->originator,
			'Recipient' => $this->recipients,
			'Cache-Control' => 'no-cache, no-transform',	// required by iSchedule spec
			'Content-Length' => bytes($content),
		);
		$header_string = '';
		foreach($headers as $name => $value)
		{
			foreach((array)$value as $val)
			{
				$header_string .= $name.': '.$val."\r\n";
			}
		}
		$header_string .= $this->dkim_sign($headers, $content)."\r\n";

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => $header_string,
		    	'user_agent' => 'EGroupware iSchedule client '.$GLOBALS['egw_info']['server']['versions']['phpgwapi'].' $Id$',
		    	//'follow_location' => 1,	// default 1=follow, but only for GET, not POST!
		        //'timeout' => $timeout,	// max timeout in seconds (float)
		        'content' => $content,
		    )
		);

		if ($debug) echo "POST $this->url HTTP/1.1\n$header_string\n$content\n";

		// need to suppress warning, if http-status not 2xx
		if (($response = @file_get_contents($this->url, false, stream_context_create($opts))) === false)
		{
			list(, $code, $message) = explode(' ', $http_response_header[0], 3);
			if ($max_redirect && $code[0] === '3')
			{
				foreach($http_response_header as $header)
				{
					if (stripos($header, 'location:') === 0)
					{
						list(,$location) = preg_split('/: ?/', $header, 2);
						if ($location[0] == '/')
						{
							$parts = parse_url($this->url);
							$location = $parts['scheme'].'://'.$parts['host'].($parts['port'] ? ':'.$parts['port'] : '').$location;
						}
						$this->url = $location;
						// follow redirect
						return $this->post_msg($content, $content_type, $debug, $max_redirect-1);
					}
				}
			}
			throw new Exception($message, $code);
		}
		return $response;
	}

	/**
	 * Calculate DKIM signature for headers and body using originators domains private key
	 *
	 * @param array $headers name => value pairs, names as in $sign_headers
	 * @param string $body
	 * @param string $selector='calendar'
	 * @param string $sign_headers='iSchedule-Version:Content-Type:Originator:Recipient'
	 * @return string DKIM-Signature: ...
	 */
	public function dkim_sign(array $headers, $body, $selector='calendar',$sign_headers=self::REQUIRED_DKIM_HEADERS)
	{
		$header_values = $header_names = array();
		foreach(explode(':', $sign_headers) as $header)
		{
			foreach((array)$headers[$header] as $value)
			{
				$header_values[] = $header.': '.$value;
				$header_names[] = $header;
			}
			// oversign multiple value header Recipient
			if ($header == 'Recipient')
			{
				$header_names[] = $header;
			}
		}
		include_once EGW_API_INC.'/php-mail-domain-signer/lib/class.mailDomainSigner.php';
		list(,$domain) = explode('@', $this->originator);
		$mds = new mailDomainSigner($this->dkim_private_key, $domain, $selector);
		// generate DKIM signature according to iSchedule spec
		$dkim = $mds->getDKIM(implode(':', $header_names), $header_values, $body, 'relaxed/simple', 'rsa-sha256',
			"DKIM-Signature: ".
	                "v=1; ".          // DKIM Version
	                "a=\$a; ".        // The algorithm used to generate the signature "rsa-sha1"
					"q=dns/txt:http/well-known; ".	// how to fetch public key: dns/txt, http/well-known or private-exchange
					"x=300; ".        // how long request will be valid in sec
					// end iSchedule specific
	                "s=\$s; ".        // The selector subdividing the namespace for the "d=" (domain) tag
	                "d=\$d; ".        // The domain of the signing entity
	                "l=\$l; ".        // Canonicalizated Body length count
	                "t=\$t; ".        // Signature Timestamp
	                "c=\$c; ".        // Message (Headers/Body) Canonicalization "relaxed/relaxed"
	                "h=\$h; ".        // Signed header fields
	                "bh=\$bh;\r\n\t". // The hash of the canonicalized body part of the message
	                "b=");             // The signature data (Empty because we will calculate it later));

		// as we do http, no need to fold dkim, in fact recommendation is not to
		$dkim = str_replace(array(";\r\n\t", "\r\n\t"), array('; ', ''), $dkim);

		return $dkim;
	}

	/**
	 * Capabilities
	 *
	 * @var array
	 */
	private $capabilities;

	/**
	 * Query capabilities of iSchedule server
	 *
	 * @param string $name=null name of capability to return, default null to return internal array with all capabilities
	 * @return mixed
	 * @throws Exception in case of an error or discovery failure
	 */
	public function capabilities($name=null)
	{
		if (!isset($this->capabilities))
		{
			$reader = new XMLReader();
			if (!$reader->open($this->url.'?action=capabilities'))
			{
				throw new Exception("Could not read iSchedule server capabilities $this->url!");
			}

			$this->capabilities = self::xml2assoc($reader);
			$reader->close();

			if (!isset($this->capabilities['query-result']) || !isset($this->capabilities['query-result']['capability-set']))
			{
				throw new Exception("Server returned invalid capabilities!");
			}
			$this->capabilities = $this->capabilities['query-result']['capability-set'];
			print_r($this->capabilities);
		}
		return $name ? $this->capabilities[$name] : $this->capabilities;
	}

	/**
	 * Parse capabilities xml into an associativ array
	 *
	 * @param XMLReader $xml
	 * @param &$target=array()
	 * @return mixed
	 */
	private static function xml2assoc(XMLReader $xml, &$target = array())
	{
		while ($xml->read())
		{
			switch ($xml->nodeType) {
				case XMLReader::END_ELEMENT:
					return $target;
				case XMLReader::ELEMENT:
					$name = $xml->name;
					$empty = $xml->isEmptyElement;
					$attr_name = $xml->getAttribute('name');
					if (($name_attr = $xml->getAttribute('name')))
					{
						$name = $attr_name;
					}
					if (isset($target[$name]))
					{
						if (!is_array($target[$name]))
						{
							$target[$name] = array($target[$name]);
						}
						$t = &$target[$name][count($target[$name])];
					}
					else
					{
						$t = &$target[$name];
					}
					if ($xml->isEmptyElement)
					{
						$t = '';
					}
					else
					{
						self::xml2assoc($xml, $t);
					}
					if ($xml->hasAttributes)
					{
						while($xml->moveToNextAttribute())
						{
							if ($xml->name != 'name')
					   	{
						   		$t['@'.$xml->name] = $xml->value;
							}
						}
					}
					break;
				case XMLReader::TEXT:
				case XMLReader::CDATA:
					$target = $xml->value;
			}
		}
		return $target;
	}

	/**
	 * Make private vars readable
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->$name;
	}
}
