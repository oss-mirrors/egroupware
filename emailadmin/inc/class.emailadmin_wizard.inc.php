<?php
/**
 * EGroupware EMailAdmin: Wizard to create mail accounts
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Wizard to create mail accounts
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
	 * Wizard to add email account
	 *
	 * @param array $content
	 * @param type $msg
	 */
	public function add(array $content=null, $msg='')
	{
		_debug_array($this->mozilla_ispdb('ralfbeckerkl@gmail.com'));
		die('Stop');
		$tpl = new etemplate_new('emailadmin.wizard');
		$content = array(
			'ident_realname' => $GLOBALS['egw_info']['user']['account_fullname'],
			'ident_email' => $GLOBALS['egw_info']['user']['account_email'],
		);
		$tpl->exec('emailadmin.emailadmin_wizard.autoconfig', $content);
	}

	/**
	 * Try to autoconfig an account
	 *
	 * @param array $content
	 */
	public function autoconfig(array $content)
	{
		$content['output'] = '';

		$connected = false;
		$content['acc_imap_username'] = $content['ident_email'];

		foreach(empty($content['acc_imap_host']) ? $this->get_imap_hosts($content['ident_email']) :
			array($content['acc_imap_host']) as $host => $data)
		{
			$content['acc_imap_host'] = $host;

			foreach(array('ssl' => 'SSL', 'tls' => 'STARTTLS', '' => 'insecure') as $secure => $label)
			{
				try {
					$content['output'] .= "\n".egw_time::to('now', 'H:i:s').": Trying $label connection to $host ...\n";

					$imap = new Horde_Imap_Client_Socket(array(
						'username' => $content['acc_imap_username'],
						'password' => $content['acc_imap_password'],
						'hostspec' => $content['acc_imap_host'],
						//'port' => $content['acc_imap_port'],
						'secure' => $secure,
						'timeout' => 1,
						'debug' => '/tmp/imap.log',
					));
					//$content['output'] .= array2string($imap->capability());
					$imap->login();
					$content['output'] .= "\n".lang('Successful connected to server and loged in :-)')."\n";
					if (!$imap->isSecureConnection())
					{
						$content['output'] .= lang('Connection is NOT secure! Everyone can read eg. your credentials.')."\n";
					}
					$content['output'] .= "\n\n".array2string($imap->capability());
					$connected = true;
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
							if ($secure == 'tls') break 2;	// no need to try insecure connection on same port
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
					break;	// no need to try other SSL or non-SSL connections, if auth failed

				case Horde_Imap_Client_Exception::SERVER_CONNECT:
					etemplate_new::set_validation_error('acc_imap_host', lang($e->getMessage()));
					break;
			}
		}
		$tpl = new etemplate_new('emailadmin.wizard');
		$tpl->exec('emailadmin.emailadmin_wizard.autoconfig', $content);
	}

	/**
	 * Query mozilla ISPDB
	 *
	 * @param type $email
	 * @return array hostname => values for keys 'displayName', 'imap', 'smtp', 'pop3', which contain
	 *	array of arrays with values for keys 'hostname', 'port', 'socketType'=(SSL|STARTTLS), 'username'=%EMAILADDRESS%
	 */
	protected function mozilla_ispdb($email, $type='imap')
	{
		$hosts = array();
		list(,$domain) = explode('@', $email);
		$url = 'https://autoconfig.thunderbird.net/v1.1/'.$domain;
		try {
			$xml = simplexml_load_file($url);
			foreach($xml->emailProvider->children() as $name => $server)
			{
				if (!in_array($name, array('incomingServer', 'outgoingServer'))) continue;
				foreach($server->attributes() as $name => $value)
				{
					if ($name == 'type') $type = (string)$value;
				}
				$host = (string)$server->hostname;
				$data = array();
				foreach($server as $name => $value)
				{
					$data[$name] = (string)$value;
				}
				if (!isset($hosts[$host]))
				{
					$hosts[$host]['displayName'] = (string)$xml->emailProvider->displayName;
				}
				$hosts[$host][$type][] = $data;
			}
		}
		catch(Exception $e) {

		}
		return $hosts;
	}

	/**
	 * Guess possible imap server hostnames from email address
	 *
	 * @param type $email
	 * @return array of hostname => data pairs
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
		error_log(__METHOD__."('$email') returning ".array2string($hosts));
		return $hosts;
	}
}