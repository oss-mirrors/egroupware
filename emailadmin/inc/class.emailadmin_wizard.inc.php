<?php
/**
 * EGroupware EMailAdmin: Wizard to create mail accounts
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Wizard to create mail accounts
 *
 * Wizard uses follow heuristic to search for IMAP accounts:
 * 1. query Mozilla ISPDB for domain from email (perfering SSL over STARTTLS over insecure connection)
 * 2. guessing and verifying in DNS server-names based on domain from email:
 *	- imap.$domain, mail.$domain
 *  - MX for $domain
 *  - replace host in MX with imap or mail
 */
class emailadmin_wizard
{
	/**
	 * Methods callable via menuaction
	 *
	 * @var array
	 */
	public $public_functions = array(
		'add' => true,
	);

	/**
	 * Supported ssl types including none
	 *
	 * @var array
	 */
	public static $ssl_types = array(
		//'2' => 'TLS',	// SSL with minimum TLS (no SSL v.2 or v.3), requires newer Horde_Imap_Client
		'1' => 'SSL',
		'3' => 'STARTTLS',
		'no' => 'no',
	);
	/**
	 * Convert ssl-type to Horde secure parameter
	 *
	 * @var array
	 */
	public static $ssl2secure = array(
		'SSL' => 'ssl',
		'STARTTLS' => 'tls',
		//'TLS' => 'tlsv1',	// SSL with minimum TLS (no SSL v.2 or v.3), requires newer Horde_Imap_Client
	);
	/**
	 * Convert ssl-type to eMailAdmin acc_(imap|sieve|smtp)_ssl integer value
	 *
	 * @var array
	 */
	public static $ssl2type = array(
		'SSL' => 1,
		'TLS' => 2,
		'STARTTLS' => 3,
		'' => 0,
	);

	/**
	 * Wizard to add email account
	 *
	 * @param array $content
	 * @param type $msg
	 */
	public function add(array $content=null, $msg='')
	{
		$tpl = new etemplate_new('emailadmin.wizard');
		$content = array(
			'ident_realname' => $GLOBALS['egw_info']['user']['account_fullname'],
			'ident_email' => $GLOBALS['egw_info']['user']['account_email'],
			'acc_imap_port' => 993,
			'manual_class' => 'emailadmin_manual',
		);
		$tpl->exec('emailadmin.emailadmin_wizard.autoconfig', $content, array(
			'acc_imap_ssl' => self::$ssl_types,
		));
	}

	/**
	 * Try to autoconfig an account
	 *
	 * @param array $content
	 */
	public function autoconfig(array $content)
	{
		$content['output'] = '';
		$sel_options = $readonlys = $preserv = array();

		$content['connected'] = $preserv['conected'] = $connected = false;
		if (empty($content['acc_imap_username']))
		{
			$content['acc_imap_username'] = $content['ident_email'];
		}
		if (!empty($content['acc_imap_host']))
		{
			$hosts = array($content['acc_imap_host'] => true);
			if ($content['acc_imap_port'] > 0 && !in_array($content['acc_imap_port'], array(143,993)))
			{
				$ssl_type = array_search($content['acc_imap_ssl'], self::$ssl_types);
				if ($ssl_type == 'no') $ssl_type = '';
				$hosts[$content['acc_imap_host']] = array(
					$ssl_type => $content['acc_imap_port'],
				);
			}
		}
		elseif (($ispdb = $this->mozilla_ispdb($content['ident_email'])) && count($ispdb['imap']))
		{
			$preserv['ispdb'] = $ispdb;
			$content['output'] .= lang('Using data from Mozilla ISPDB for provider %1', $ispdb['displayName'])."\n";
			$hosts = array();
			foreach($ispdb['imap'] as $server)
			{
				if (!isset($hosts[$server['hostname']]))
				{
					$hosts[$server['hostname']] = array('username' => $server['username']);
				}
				$hosts[$server['hostname']][strtoupper($server['socketType'])] = $server['port'];
				// make sure we prefer SSL over STARTTLS over insecure
				if (count($hosts[$server['hostname']]) > 2)
				{
					$hosts[$server['hostname']] = self::fix_ssl_order($hosts[$server['hostname']]);
				}
			}
		}
		else
		{
			$hosts = $this->get_imap_hosts($content['ident_email']);
		}

		// iterate over all hosts and try to connect
		foreach($hosts as $host => $data)
		{
			$content['acc_imap_host'] = $host;
			// by default we check SSL, STARTTLS and at last an insecure connection
			if (!is_array($data)) $data = array('SSL' => 993, 'STARTTLS' => 143, 'insecure' => 143);

			foreach($data as $ssl => $port)
			{
				if ($ssl === 'username') continue;

				$content['acc_imap_ssl'] = (int)self::$ssl2type[$ssl];

				try {
					$content['output'] .= "\n".egw_time::to('now', 'H:i:s').": Trying $ssl connection to $host:$port ...\n";
					$content['acc_imap_port'] = $port;

					$imap = new Horde_Imap_Client_Socket(array(
						'username' => $content['acc_imap_username'],
						'password' => $content['acc_imap_password'],
						'hostspec' => $content['acc_imap_host'],
						'port' => $content['acc_imap_port'],
						'secure' => self::$ssl2secure[$ssl],
						'timeout' => 1,	// just connection timeout
						'debug' => '/tmp/imap.log',
					));
					//$content['output'] .= array2string($imap->capability());
					$imap->login();
					$content['output'] .= "\n".lang('Successful connected to server and loged in :-)')."\n";
					if (!$imap->isSecureConnection())
					{
						$content['output'] .= lang('Connection is NOT secure! Everyone can read eg. your credentials.')."\n";
					}
					//$content['output'] .= "\n\n".array2string($imap->capability());
					$content['connected'] = $preserv['conected'] = $connected = true;
					break 2;
				}
				catch(Horde_Imap_Client_Exception $e)
				{
					switch($e->getCode())
					{
						case Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED:
							$content['output'] .= "\n".$e->getMessage()."\n";
							break 3;	// no need to try other SSL or non-SSL connections, if auth failed

						case Horde_Imap_Client_Exception::SERVER_CONNECT:
							$content['output'] .= "\n".$e->getMessage()."\n";
							if ($ssl == 'STARTTLS') break 2;	// no need to try insecure connection on same port
							break;

						default:
							$content['output'] .= "\n".get_class($e).': '.$e->getMessage().' ('.$e->getCode().')'."\n";
							//$content['output'] .= $e->getTraceAsString()."\n";
					}
				}
				catch(Exception $e) {
					$content['output'] .= "\n".get_class($e).': '.$e->getMessage().' ('.$e->getCode().')'."\n";
					$content['output'] .= $e->getTraceAsString()."\n";
				}
			}
		}
		// add validation error, if we can identify a field
		if (!$connected && $e instanceof Horde_Imap_Client_Exception)
		{
			switch($e->getCode())
			{
				case Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED:
					etemplate_new::set_validation_error('acc_imap_password', lang($e->getMessage()));
					break;

				case Horde_Imap_Client_Exception::SERVER_CONNECT:
					etemplate_new::set_validation_error('acc_imap_host', lang($e->getMessage()));
					break;
			}
		}
		$readonlys['button[manual]'] = true;
		$sel_options['acc_imap_ssl'] = self::$ssl_types;
		$tpl = new etemplate_new('emailadmin.wizard');
		$tpl->exec('emailadmin.emailadmin_wizard.autoconfig', $content, $sel_options, $readonlys, $preserv);
	}

	/**
	 * Reorder SSL types to make sure we start with TLS, SSL, STARTTLS and insecure last
	 *
	 * @param array $data ssl => port pairs plus other data like value for 'username'
	 * @return array
	 */
	protected static function fix_ssl_order($data)
	{
		$ordered = array();
		foreach(array_merge(array('TLS', 'SSL', 'STARTTLS'), array_keys($data)) as $key)
		{
			if (array_key_exists($key, $data)) $ordered[$key] = $data[$key];
		}
		return $ordered;
	}

	/**
	 * Query Mozilla's ISPDB
	 *
	 * @param type $email
	 * @return array with values for keys 'displayName', 'imap', 'smtp', 'pop3', which each contain
	 *	array of arrays with values for keys 'hostname', 'port', 'socketType'=(SSL|STARTTLS), 'username'=%EMAILADDRESS%
	 */
	protected function mozilla_ispdb($email)
	{
		list(,$domain) = explode('@', $email);
		$url = 'https://autoconfig.thunderbird.net/v1.1/'.$domain;
		try {
			$xml = simplexml_load_file($url);
			if (!$xml->emailProvider) throw new egw_exception_not_found();
			$provider = array(
				'displayName' => (string)$xml->emailProvider->displayName,
			);
			foreach($xml->emailProvider->children() as $tag => $server)
			{
				if (!in_array($tag, array('incomingServer', 'outgoingServer'))) continue;
				foreach($server->attributes() as $name => $value)
				{
					if ($name == 'type') $type = (string)$value;
				}
				$data = array();
				foreach($server as $name => $value)
				{
					foreach($value->children() as $tag => $val)
					{
						$data[$name][$tag] = (string)$val;
					}
					if (!isset($data[$name])) $data[$name] = (string)$value;
				}
				$provider[$type][] = $data;
			}
		}
		catch(Exception $e) {
			// ignore own not-found exception or xml parsing execptions
			$provider = array();
		}
		//error_log(__METHOD__."('$email') returning ".array2string($provider));
		return $provider;
	}

	/**
	 * Guess possible imap server hostnames from email address:
	 *	- imap.$domain, mail.$domain
	 *  - MX for $domain
	 *  - replace host in MX with imap or mail
	 *
	 * @param type $email
	 * @return array of hostname => true pairs
	 */
	protected function get_imap_hosts($email)
	{
		list(,$domain) = explode('@', $email);

		$hosts = array();

		// try usuall names
		$hosts['imap.'.$domain] = true;
		$hosts['mail.'.$domain] = true;

		if (($dns = dns_get_record($domain, DNS_MX)))
		{
			//error_log(__METHOD__."('$email') dns_get_record('$domain', DNS_MX) returned ".array2string($dns));
			$hosts[$dns[0]['target']] = true;
			$hosts[preg_replace('/^[^.]+/', 'imap', $dns[0]['target'])] = true;
			$hosts[preg_replace('/^[^.]+/', 'mail', $dns[0]['target'])] = true;
		}

		// verify hosts in dns
		foreach($hosts as $host => $data)
		{
			if (!dns_get_record($host, DNS_A)) unset($hosts[$host]);
		}
		//error_log(__METHOD__."('$email') returning ".array2string($hosts));
		return $hosts;
	}
}