<?php
/**
 * EGroupware: iSchedule server
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage groupdav
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2012-13 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * iSchedule server: serverside of iSchedule
 *
 * @link https://tools.ietf.org/html/draft-desruisseaux-ischedule-03 iSchedule draft from 2013-01-22
 *
 * groupdav get's extended here to get it's logging, should separate that out ...
 */
class ischedule_server extends groupdav
{
	/**
	 * iSchedule xml namespace
	 */
	const ISCHEDULE = 'urn:ietf:params:xml:ns:ischedule';

	/**
	 * Own iSchedule version
	 */
	const VERSION = '1.0';

	/**
	 * Supported versions for capablities
	 *
	 * Might be more then current version above
	 *
	 * @var array
	 */
	static $supported_versions = array(self::VERSION);

	/**
	 * Required headers in DKIM signature (DKIM-Signature is always a required header!)
	 */
	const REQUIRED_DKIM_HEADERS = 'iSchedule-Version:Content-Type:Originator:Recipient';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// install our own exception handler sending exceptions as http status
		set_exception_handler(array(__CLASS__, 'exception_handler'));

		self::$instance = $this;
	}

	/**
	 * Serve an iSchedule request
	 */
	public function ServeRequest()
	{
		self::$log_level = $GLOBALS['egw_info']['user']['preferences']['groupdav']['debug_level'];
		self::$log_level = 'f';
		if (self::$log_level === 'r' || self::$log_level === 'f' || $this->debug)
		{
			self::$request_starttime = microtime(true);
			$this->store_request = true;
			ob_start();
		}
		// get raw request body
		$this->request = file_get_contents('php://input');

		switch($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':
				$this->get();
				break;

			case 'POST':
				$this->post();
				break;

			default:
				error_log(__METHOD__."() invalid iSchedule request using {$_SERVER['REQUEST_METHOD']}!");
				header("HTTP/1.1 400 Bad Request");
		}

		if (self::$request_starttime) self::log_request();
	}

	/**
	 * List of supported components and methods
	 *
	 * @var array
	 */
	static $supported_components = array(
		'VFREEBUSY' => array('REQUEST'),
		'VEVENT' => array('REQUEST', 'REPLY', 'CANCEL'),
		//'VTODO' => array('REQUEST', 'REPLY', 'CANCEL'),
	);

	/**
	 * List of supported calendar-data-types
	 *
	 * @var array
	 */
	static $supported_calendar_data_types = array(
		array('content-type' => 'text/calendar', 'version' => '2.0'),
	);

	/**
	 * Supported attachment types for capabilities
	 *
	 * @var array
	 */
	static $supported_attachments = array('external', 'inline');

	/**
	 * Other capablities
	 *
	 * @var array name => value pairs
	 */
	static $capablities = array(
		'max-content-length' => 102400,
		'min-date-time' => '19910101T000000Z',
		'max-date-time' => '20381231T000000Z',
		'max-instances' => 150,
		'max-recipients' => 250,
		// for server config: 'administrator' => 'mailto:ischedule-admin@example.com</administrator',
	);

	/**
	 * Requiremnt for originator to match depending on method
	 *
	 * @var array method => array('ORGANIZER','ATTENDEE')
	 * @link https://tools.ietf.org/html/draft-desruisseaux-ischedule-03#section-6.1
	 */
	static $supported_method2origin_requirement = array(
		//'PUBLISH' => null,	// no requirement
		'REQUEST' => array('ORGANIZER', 'ATTENDEE'),
		'REPLY'   => array('ATTENDEE'),
		'ADD'     => array('ORGANIZER'),
		'CANCEL'  => array('ORGANIZER'),
		//'REFRESH' => null,
		//'COUNTER' => array('ATTENDEE'),
		//'DECLINECOUNTER' => array('ORGANIZER'),
	);

	/**
	 * Serve an iSchedule POST request
	 */
	public function post()
	{
		// get and verify required headers
		$headers = array();
		foreach($_SERVER as $name => $value)
		{
			$name = strtolower(str_replace('_', '-', $name));
			list($first, $rest) = explode('-', $name, 2);
			switch($first)
			{
				case 'content':
					$headers[$name] = $value;
					break;
				case 'http':
					$headers[$rest] = $value;
					break;
			}
		}
		if (($missing = array_diff(explode(':', strtolower(self::REQUIRED_DKIM_HEADERS.':DKIM-Signature')), array_keys($headers))))
		{
			//error_log('headers='.array2string(array_keys($headers)).', required='.self::REQUIRED_DKIM_HEADERS.', missing='.array($missing));
			if (in_array('originator', $missing))
			{
				$error = 'originator-missing';
			}
			elseif(in_array('recipient', $missing))
			{
				$error = 'recipient-missing';
			}
			else
			{
				$error = 'invalid-scheduling-message';
			}
			throw new Exception ("Bad Request: $error: missing required headers: ".implode(', ', $missing), 400);
		}

		// validate dkim signature
		// for multivalued Recipient header: as PHP engine agregates them ", " separated,
		// we cant tell it apart from ", " separated recipients in one header, therefore we try to validate both.
		// It will fail if multiple recipients in a single header are also ", " separated (just comma works fine)
		if (!self::dkim_validate($headers, $this->request, $error))
		{
			throw new Exception('Bad Request: verification-failed: DKIM signature invalid: '.$error, 400);
		}
		// check if recipient is a user
		// todo: multiple recipients, currently we use last recipient for EGroupware enviroment
		// it is no error, to not specify a correct user, in that case we return just a request-status of "3.7;Invalid Calendar User"!
		//foreach(preg_split('/, */', $headers['recipient']) as $recipient)
		/*{
			if (!stripos($recipient, 'mailto:') === 0 ||
				!($account_id = $GLOBALS['egw']->accounts->name2id(substr($recipient, 7), 'account_email')))
			{
				throw new Exception("Bad Request: recipient-missing: unknown recipient '$recipient'", 400);
			}
		}
		// create enviroment for recipient user, as we act on his behalf
		$GLOBALS['egw']->session->account_id = $account_id;
		$GLOBALS['egw']->session->account_lid = $GLOBALS['egw']->accounts->id2name($account_id);
		//$GLOBALS['egw']->session->account_domain = $domain;
		$GLOBALS['egw_info']['user']  = $GLOBALS['egw']->session->read_repositories();
		translation::init();
		*/

		// check originator is allowed to iSchedule with recipient
		// ToDo: preference for user/admin to specify with whom to iSchedule: $allowed_origins
		$allowed_origins = preg_split('/, ?/', $GLOBALS['egw_info']['user']['preferences']['groupdav']['ischedule_allowed_origins']);
		/* disabled 'til UI is ready to specifiy
		 * ToDo: this should be no error but a response-status of "3.8;No authority"
		list(,$originator_domain) = explode('@', $headers['Originator']);
		if (!in_array($headers['Originator'], $allowed_orgins) && !in_array($originator_domain, $allowed_origins))
		{
			throw new Exception('Forbidden', 403);
		}*/

		// check method and component of Content-Type are valid
		// ToDo: no component or method in Content-Type should give an "invalid-scheduling-message" error
		// only unsupported (not in capablities) component should give "invalid-caledar-data-type"
		if (!preg_match('/component=([^;]+)/i', $headers['content-type'], $matches) ||
			(!in_array($component=strtoupper($matches[1]), self::$supported_components)))
		{
			throw new Exception ('Bad Request: invalid-calendar-data-type: missing or unsupported component in Content-Type header', 400);
		}
		if (!preg_match('/method=([^;]+)/i', $headers['content-type'], $matches) ||
			(!isset(self::$supported_method2origin_requirement[$method=strtoupper($matches[1])])) ||
			$component == 'VFREEBUSY' && $method != 'REQUEST')
		{
			throw new Exception ('Bad Request: missing or unsupported method in Content-Type header', 400);
		}
		// parse iCal
		// code copied from calendar_groupdav::outbox_freebusy_request for now
		include_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/lib/core.php';
		$vcal = new Horde_iCalendar();
		if (!$vcal->parsevCalendar($this->request, 'VCALENDAR', 'utf-8'))
		{
			throw new Exception('Bad Request: invalid-calendar-data: Failed parsing iCal', 400);
		}
		$version = $vcal->getAttribute('VERSION');
		$handler = new calendar_ical();
		$handler->setSupportedFields('GroupDAV',$this->agent);
		$handler->calendarOwner = $handler->user = 0;	// to NOT default owner/organizer to something
		if (!($vcal_comp = $vcal->getComponent(0)) ||
			!($event = $handler->vevent2egw($vcal_comp, $version, $handler->supportedFields,
				$principalURL='', $check_component='Horde_iCalendar_'.strtolower($component))))
		{
			throw new Exception('Bad Request: invalid-calendar-data: Failed converting iCal', 400);
		}

		// validate originator matches organizer or attendee
		$originator_requirement = self::$supported_method2origin_requirement[$method];
		if (isset($originator_requirement))
		{
			$matches = false;
			foreach($originator_requirement as $requirement)
			{
				$originator = $headers['originator'];
				if (stripos($originator, 'mailto:') === 0) $originator = substr($originator, 7);

				if ($requirement == 'ORGANIZER' &&
					($event['organizer'] == $originator || strpos($event['organizer'], '<'.$originator.'>') !== false) ||
					$requirement == 'ATTENDEE' &&
					(in_array('e'.$originator, $event['participants']) ||
					// ToDO: Participant could have CN, need to check that too
					 $originator_account_id = $GLOBALS['egw']->accounts->name2id($originator, 'account_email') &&
					 	in_array($originator_account_id, $event['participants'])))
			 	{
			 		$matches = true;
			 		break;	// no need to try further as we OR
			 	}
			}
			if (!$matches)
			{
				throw new Exception("Bad Request: originator-invalid: originator '$originator' invalid for given $component component!", 400);
			}
		}

		$xml = new XMLWriter;
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElementNs(null, 'schedule-response', self::ISCHEDULE);	// null = no prefix

		switch($component)
		{
			case 'VFREEBUSY':
				$this->vfreebusy($event, $handler, $vcal_comp, $xml);
				break;

			case 'VEVENT':
				$this->vevent($event, $handler, $component, $xml);
				break;

			default:
				throw new Exception ('Bad Request: invalid-calendar-data-type: not implemented', 400);
		}

		$xml->endElement();	// schedule-response
		$xml->endDocument();

		header('Content-Type: text/xml; charset=UTF-8');
		header('iSchedule-Version: '.self::VERSION);
		header('iSchedule-Capabilities: '.self::SERIAL);

		echo $xml->outputMemory();
	}

	/**
	 * Handle VEVENT component
	 *
	 * @param array $event
	 * @param calendar_ical $handler
	 * @param string $component
	 * @param XMLWriter $xml
	 */
	function vevent(array $event, calendar_ical $handler, Horde_iCalendar_vevent $component, XMLWriter $xml)
	{
		$organizer = $component->getAttribute('ORGANIZER');
		$attendees = (array)$component->getAttribute('ATTENDEE');

		$handler->importVCal($vCalendar, $eventId,
			self::etag2value($this->http_if_match), false, 0, $this->groupdav->current_user_principal, $user, $charset, $id);

		foreach($event['participants'] as $uid => $status)
		{
			$xml->startElement('response');

			$xml->writeElement('recipient', $attendee=array_shift($attendees));	// iSchedule has not DAV:href!

			if (is_numeric($uid))
			{
				$xml->writeElement('request-status', '2.0;Success');
				$xml->writeElement('responsedescription', 'Delivered to recipient');
			}
			else
			{
				$xml->writeElement('request-status', '3.7;Invalid Calendar User');
				$xml->writeElement('responsedescription', 'Recipient not a local user');
			}
			$xml->endElement();	// response
		}
	}

	/**
	 * Handle VFREEBUSY component
	 *
	 * @param array $event
	 * @param calendar_ical $handler
	 * @param Horde_iCalendar_vfreebusy $component
	 * @param XMLWriter $xml
	 */
	function vfreebusy(array $event, calendar_ical $handler, Horde_iCalendar_vfreebusy $component, XMLWriter $xml)
	{
		$organizer = $component->getAttribute('ORGANIZER');
		$attendees = (array)$component->getAttribute('ATTENDEE');

		foreach($attendees as $attendee)
		{
			$xml->startElement('response');

			$xml->writeElement('recipient', $attendee);	// iSchedule has not DAV:href!

			if (stripos($attendee, 'mailto:') === 0 &&
				($uid = $GLOBALS['egw']->accounts->name2id(substr($attendee, 7), 'account_email')))
			{
				$xml->writeElement('request-status', '2.0;Success');
				$xml->writeElement('calendar-data',
					$handler->freebusy($uid, $event['end'], true, 'utf-8', $event['start'], 'REPLY', array(
						'UID' => $event['uid'],
						'ORGANIZER' => $organizer,
						'ATTENDEE' => $attendee,
					)));
			}
			else
			{
				$xml->writeElement('request-status', '3.7;Invalid Calendar User');
			}
			$xml->endElement();	// response
		}
	}
	/**
	 * Required DKIM tags
	 *
	 * @link https://tools.ietf.org/html/rfc6376#section-3.5
	 *
	 * @var array
	 */
	public static $required_dkim_tags = array('v', 'a', 'b', 'bh', 'd', 'h', 's');

	/**
	 * Validate DKIM signature
	 *
	 * For multivalued and multiple Recipient header(s): PHP engine agregates them ", " separated.
	 * ischedule-relaxed canonisation takes care of that.
	 *
	 * @param array $headers header-name in lowercase(!) as key
	 * @param string $body
	 * @param string &$error=null error if false returned
	 * @return boolean true if signature could be validated, false otherwise
	 */
	public static function dkim_validate(array $headers, $body, &$error=null)
	{
		// parse dkim signature, after unfolding it
		if (!isset($headers['dkim-signature']) ||
			!preg_match_all('/[\t\s]*([a-z]+)\s*=\s*([^;]+);?/i',
				preg_replace("|\r\n\s+|", "", $headers['dkim-signature']), $matches))
		{
			$error = "Can't parse DKIM signature";
			return false;
		}
		$dkim = array_combine($matches[1], $matches[2]);

		// check required DKIM tags
		if (($missing = array_diff(self::$required_dkim_tags, array_keys($dkim))))
		{
			$error = 'missing required DKIM tags: '.implode(', ', $missing);
			return false;
		}

		// check dkim version, have to fail if it's not 1
		if ($dkim['v'] !== '1')
		{
			$error = "Wrong DKIM version '$dkim[v]'";
			return false;
		}

		// create headers array, h tag can contain whitespace, which need to be removed
		$dkim_headers = array();
		$check = $headers;
		foreach(explode(':', strtolower(preg_replace("|\s|", "", $dkim['h']))) as $header)
		{
			// dkim oversigning: ommit not existing headers in signing
			if (!isset($check[$header])) continue;

			$value = $check[$header];
			unset($check[$header]);

			$dkim_headers[] = $header.': '.$value;
		}
		// dkim signature is obvious without content of signature, but must not necessarly be last tag
		$dkim_unsigned = 'DKIM-Signature: '.str_replace($dkim['b'], '', $headers['dkim-signature']);

		// c defaults to 'simple/simple', check on valid canonicalization methods is performed further down
		list($header_canon, $body_canon) = explode('/', isset($dkim['c']) ? $dkim['c'] : 'simple/simple');

		// Canonicalization for Body
		switch($body_canon)
		{
			case 'relaxed':
				$_b = mailDomainSigner::bodyRelaxCanon($body);
				break;

			case 'simple':
				$_b = mailDomainSigner::bodySimpleCanon($body);
				break;

			default:
				$error = "Unknown body canonicalization '$body_canon'";
				return false;
		}

		// check signing and hashing algorithms
		list($sign_algo, $hash_algo) = explode('-', $dkim['a']);
		if ($sign_algo != 'rsa' || !in_array($hash_algo, array('sha1', 'sha256', 'sha384', 'sha512')))
		{
			$error = "Unknown or unimplemented algorithm a='$dkim[a]'";
			return false;
		}

		// Hash of the canonicalized body [tag:bh]
		$_bh = base64_encode(hash($hash_algo, $_b, true));

		// check body hash, need to remove optional whitespace
		if ($_bh != preg_replace("|\s|", "", $dkim['bh']))
		{
			$error = 'Body hash does NOT verify';
			error_log(__METHOD__."() body-hash='$_bh' != '$dkim[bh]'=dkim-bh $error");
			return false;
		}

		// Canonicalization Header Data
		switch($header_canon)
		{
			case 'relaxed':
				$_unsigned  = mailDomainSigner::headRelaxCanon(implode("\r\n", $dkim_headers). "\r\n".$dkim_unsigned);
				break;

			case 'ischedule-relaxed':
				$_unsigned  = mailDomainSigner::headIScheduleRelaxCanon(implode("\r\n", $dkim_headers). "\r\n".$dkim_unsigned);
				break;

			case 'simple':
				$_unsigned  = mailDomainSigner::headSimpleCanon(implode("\r\n", $dkim_headers). "\r\n".$dkim_unsigned);
				break;

			default:
				$error = "Unknown header canonicalization '$header_canon'";
				return false;
		}

		// fetch public key using method in dkim q
		foreach(explode(':', $dkim['q']) as $method)
		{
			switch($method)
			{
				case 'dns/txt':
					$public_key = self::dns_txt_pubkey($dkim['d'], $dkim['s']);
					break;

				case 'http/well-known':
					$public_key = self::well_known_pubkey($dkim['d'], $dkim['s']);
					break;

				case 'private-exchange':
					$public_key = self::private_exchange_pubkey($dkim['d'], $dkim['s']);
					break;

				default:	// not understood q method
					$public_key = false;
					break;
			}
			if ($public_key) break;
		}
		// for testing purpose allways try private-exchange, if none other match
		if (!$public_key) $public_key = self::private_exchange_pubkey($dkim['d'], $dkim['s']);

		if (!$public_key)
		{
			$error = "No public key for d='$dkim[d]' and s='$dkim[s]' using methods q='$dkim[q]'";
			return false;
		}
		// value of b tag can contain whitespace, which need to be removed
		$ok = openssl_verify($_unsigned, base64_decode(preg_replace("|\s|", "", $dkim['b'])), $public_key, $hash_algo);
		if ($ok != 1) error_log(__METHOD__."() openssl_verify('$_unsigned', ..., '$public_key', '$hash_algo') returned ".array2string($ok));

		switch($ok)
		{
			case -1:
				$error = 'Error while verifying DKIM';
				return false;

			case 0:
				$error = 'DKIM signature does NOT verify';
				return false;
		}

		// verify optional x tag, unix timestamp when signature expires
		if (isset($dkim['x']) && (!is_numeric($dkim['x']) || $dkim['x'] < time()))
		{
			$error = "DKIM signature is expired x=$dkim[x] < ".time();
			return false;
		}

		return true;
	}

	/**
	 * Provisional private-exchange public keys
	 *
	 * @var array domain => selector => public key
	 */
	static $private_exchange = array(
		'example.com' => array(
			'ischedule' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDtocSHvSS1Nn0uIL4Sg+0wp6Kc
W31WRC4Fww8P+jvsVAazVOxvxkShNSd18EvApiNa55P8WgKVEu02OQePjnjKNqfg
JPeajkWy/0CJn+d6rX/ncPMGX2EYzqXy/CyVqpcnVAosToymo6VHL6ufhzlyLJFD
znLtV121CZLUZlAySQIDAQAB
-----END PUBLIC KEY-----',
		),
		'mysite.edu' => array(
			'selector' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCuv+6UtGUdPerJ3s0HCng2sv3c
R3ttma0JB6rMFfOTi1oHgk+h328MfGzhZK+SA9tsRPBcrJE/3uxs4SS2XNG9qRCG
0YMmNFOmubht4RhQhS9drSNyMZbhy2MPVbl9lHAJULFdaDdLj1hc3xTMWy8sDa8s
M8r0gHvp/sPSe9CQQQIDAQAB
-----END PUBLIC KEY-----',
		),
		'ken.name' => array(
			'ischedule' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCfAWRKjVWS/W4F3nUYnj4XWYsy
+DsJdTjMKiHilUgQoz5MLnWpE/rt0LZklMG4Vz0io82pvnUzRrhmiaTTynlEwkj7
gDABr/WCxv2j5vKNayz13my8z+D6efYkuHsPP8z5iQp9yzbKa8FXKb+O7AXbK5fS
3U7S1OJgMpBeCrcpdwIDAQAB
-----END PUBLIC KEY-----',
		),
		'caldav.egroupware.net' => array(
			'calendar' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCiawhLuTSVhnl1zz5pXs1A748y
N3aNE181dni8nsYqIQB1h4H32J4dZurEiAnP9nflQRjCmmg1NTvFcNz11Bem4zo1
K4r4mcfbjlheorK2Mwoh445HR3fo/pP7uV6CcXTNboBJLTxs6ZHswmQjxyuKBKmx
yXUKsIQVi3qPyPdB3QIDAQAB
-----END PUBLIC KEY-----',
		),
		'outdoor-training.de' => array(
			'calendar' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCiawhLuTSVhnl1zz5pXs1A748y
N3aNE181dni8nsYqIQB1h4H32J4dZurEiAnP9nflQRjCmmg1NTvFcNz11Bem4zo1
K4r4mcfbjlheorK2Mwoh445HR3fo/pP7uV6CcXTNboBJLTxs6ZHswmQjxyuKBKmx
yXUKsIQVi3qPyPdB3QIDAQAB
-----END PUBLIC KEY-----',
		),
	);

	/**
	 * Fetch public key from dns txt recored dkim q=private-exchange
	 *
	 * @param string $d domain
	 * @param string $s selector
	 * @return string|boolean string with (full) public key or false if not found or other error retrieving it
	 */
	public static function private_exchange_pubkey($d, $s)
	{
		if (!isset(self::$private_exchange[$d]) || !isset(self::$private_exchange[$d][$s]))
		{
			return false;
		}
		return self::$private_exchange[$d][$s];
	}

	/**
	 * Fetch public key via http for q=http/well-known
	 *
	 * GET request to https://$domain/.well-known/domainkey/$selector
	 * Content should be identical to txt record for dns/txt.
	 *
	 * @param string $d domain
	 * @param string $s selector
	 * @return string|boolean string with (full) public key or false if not found or other error retrieving it
	 */
	public static function well_known_pubkey($d, $s)
	{
		if (!($keys = @file_get_contents('https://'.$d.'/.well-known/domainkey/'.$s)) ||
			preg_match('/p=([^;]+)/i', $keys, $matches))
		{
			return false;
		}
		return "-----BEGIN PUBLIC KEY-----\n".chunk_split($matches[1], 64, "\n")."-----END PUBLIC KEY-----\n";
	}

	/**
	 * Fetch public key from dns txt recored dkim q=dns/txt
	 *
	 * @param string $d domain
	 * @param string $s selector
	 * @return string|boolean string with (full) public key or false if not found or other error retrieving it
	 */
	public function dns_txt_pubkey($d, $s)
	{
		if (!($dns = self::fetch_dns($d, $s)))
		{
			return false;
		}
		return "-----BEGIN PUBLIC KEY-----\n".chunk_split($dns['p'], 64, "\n")."-----END PUBLIC KEY-----\n";
	}

	/**
	 * Fetch dns record and return parsed array
	 *
	 * @param string $domain
	 * @param string $selector
	 * @return array with values for keys parsed from eg. "v=DKIM1\;k=rsa\;h=sha1\;s=calendar\;t=s\;p=..."
	 */
	public static function fetch_dns($domain, $selector='calendar')
	{
		if (!($records = dns_get_record($host=$selector.'._domainkey.'.$domain, DNS_TXT))) return false;

		if (!isset($records[0]['text']) &&
			!preg_match_all('/[\t\s]*([a-z]+)=([^;]+);?/i', $records[0]['txt'], $matches))
		{
			return false;
		}
		return array_combine($matches[1], $matches[2]);
	}

	const SERIAL = '124';

	/**
	 * Serve an iSchedule GET request, currently only action=capabilities
	 *
	 * GET /.well-known/ischedule?action=capabilities HTTP/1.1
	 * Host: cal.example.com
	 *
	 * HTTP/1.1 200 OK
	 * Date: Mon, 15 Dec 2008 09:32:12 GMT
	 * Content-Type: application/xml; charset=utf-8
	 * Content-Length: xxxx
	 * iSchedule-Version: 1.0
	 * iSchedule-Capabilities: 123
	 * ETag: "afasdf-132afds"
	 *
	 * <?xml version="1.0" encoding="utf-8" ?>
	 * <query-result xmlns="urn:ietf:params:xml:ns:ischedule">
	 *   <capabilities>
	 *     <serial-number>123</serial-number>
	 *     <versions>
	 *       <version>1.0</version>
	 *     </versions>
	 *     <scheduling-messages>
	 *       <component name="VEVENT">
	 *         <method name="REQUEST"/>
	 *         <method name="ADD"/>
	 *         <method name="REPLY"/>
	 *         <method name="CANCEL"/>
	 *       </component>
	 *       <component name="VTODO"/>
	 *       <component name="VFREEBUSY"/>
	 *     </scheduling-messages>
	 *     <calendar-data-types>
	 *       <calendar-data-type content-type="text/calendar" version="2.0"/>
	 *     </calendar-data-types>
	 *     <attachmens>
	 *       <inline/>
	 *       <external/>
	 *     </attachments>
	 *     <max-content-length>102400</max-content-length>
	 *     <min-date-time>19910101T000000Z</min-date-time>
	 *     <max-date-time>20381231T000000Z</max-date-time>
	 *     <max-instances>150</max-instances>
	 *     <max-recipients>250</max-recipients>
	 *     <administrator>mailto:ischedule-admin@example.com</administrator>
	 *   </capabilities>
	 * </query-result>
	 */
	public function get()
	{
		if (!isset($_GET['action']) || $_GET['action'] !== 'capabilities')
		{
			error_log(__METHOD__."() invalid iSchedule request using GET without action=capabilities!");
			header("HTTP/1.1 400 Bad Request");
			echo "<h1>Invalid iSchedule request using GET without action=capabilities!</h1>\n";
			return;
		}

		// generate capabilities
		$xml = new XMLWriter;
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElementNs(null, 'query-result', self::ISCHEDULE);
		$xml->startElement('capabilities');

		$xml->writeElement('serial-number', self::SERIAL);

		$xml->startElement('versions');
		foreach(self::$supported_versions as $version)
		{
			$xml->writeElement('version', $version);
		}
		$xml->endElement();	// versions

		$xml->startElement('scheduling-messages');
		foreach(self::$supported_components as $component => $methods)
		{
			$xml->startElement('component');
			$xml->writeAttribute('name', $component);
			foreach($methods as $method)
			{
				$xml->startElement('method');
				$xml->writeAttribute('name', $method);
				$xml->endElement();	// method
			}
			$xml->endElement();	// component
		}
		$xml->endElement();	// scheduling-messages

		$xml->startElement('calendar-data-types');
		foreach(self::$supported_calendar_data_types as $data)
		{
			$xml->startElement('calendar-data-type');
			foreach($data as $name => $value)
			{
				$xml->writeAttribute($name, $value);
			}
			$xml->endElement();	// calendar-data-type
		}
		$xml->endElement();	// calendar-data-types

		$xml->startElement('attachments');
		foreach(self::$supported_attachments as $type)
		{
			$xml->writeElement($type, '');
		}
		$xml->endElement();	// attachments

		if (!empty($GLOBALS['egw_info']['server']['admin_mails']))
		{
			self::$capablities['administrator'] = 'mailto:'.$GLOBALS['egw_info']['server']['admin_mails'];
		}
		foreach(self::$capablities as $name => $value)
		{
			$xml->writeElement($name, $value);
		}

		$xml->endElement();	// capabilities
		$xml->endElement();	// query-result
		$xml->endDocument();
		$capabilities = $xml->outputMemory();

		// returning capabilities
		header('Content-Type: application/xml; charset=utf-8');
		header('iSchedule-Version: '.self::VERSION);
		header('iSchedule-Capabilities: '.self::SERIAL);
		header('Content-Length: '.bytes($capabilites));
		header('ETag: "'.md5($capabilites).'"');

		echo $capabilities;
		common::egw_exit();
	}

	/**
	 * Exception handler, which additionally logs the request (incl. a trace)
	 *
	 * Does NOT return and get installed in constructor.
	 *
	 * @param Exception $e
	 */
	public static function exception_handler(Exception $e)
	{
		// logging exception as regular egw_execption_hander does
		_egw_log_exception($e,$headline);

		// exception handler sending message back to the client as http status
		$code = $e->getCode();
		$msg = $e->getMessage();
		list($http_status, $error, $description) = explode(': ', $msg, 3);
		// check if we have a valid iSchedule error element, if not we use invalid-scheduling-message
		if (!empty($error) && strpos($error, ' ') !== false)
		{
			$description = $error.($description ? ': '.$description : '');
			$error = 'invalid-scheduling-message';
		}
		if (!in_array($code, array(400, 403, 407, 503))) $code = 500;

		header('HTTP/1.1 '.$code.' '.$http_status, true, $code);
		header('Content-Type: text/xml; charset=UTF-8');
		header('iSchedule-Version: '.self::VERSION);
		header('iSchedule-Capabilities: '.self::SERIAL);

		if ($error)
		{
			echo '<?xml version="1.0" encoding="utf-8" ?>
<error xmlns="urn:ietf:params:xml:ns:ischedule">
	<'.$error.' />
	<response-description>'.htmlspecialchars($description).'</response-description>
</error>
';
		}

		// if our groupdav logging is active, log the request plus a trace, if enabled in server-config
		if (groupdav::$request_starttime && isset(self::$instance))
		{
			self::$instance->_http_status = $code.' '.$http_status;
			if ($GLOBALS['egw_info']['server']['exception_show_trace'])
			{
				self::$instance->log_request("\n".$e->getTraceAsString()."\n");
			}
			else
			{
				self::$instance->log_request();
			}
		}
		if (is_object($GLOBALS['egw']))
		{
			common::egw_exit();
		}
		exit;
	}
}
