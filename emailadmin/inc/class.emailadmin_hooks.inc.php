<?php
/**
 * eGroupWare - eMailAdmin hooks
 *
 * @link http://www.egroupware.org
 * @package emailadmin
 * @author Klaus Leithoff <leithoff-AT-stylite.de>
 * @copyright (c) 2008-8 by leithoff-At-stylite.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * diverse static emailadmin hooks
 */
class emailadmin_hooks
{
	// hook to plug in into admin (managable) applications list
	static function admin()
	{
		// Only Modify the $file and $title variables.....
		$title = $appname = 'emailadmin';
		$file = Array(
			'Site Configuration'	=> $GLOBALS['egw']->link('/index.php','menuaction=emailadmin.emailadmin_ui.index')
		);

		//Do not modify below this line
		display_section($appname,$title,$file);
	}

    /**
     * Hook called to add action to user
     *
     * @param array $data
     * @param int $data['account_id'] numerical id
     */
	static function edit_user($data)
	{
		unset($data);	// not used
		if ($GLOBALS['egw_info']['user']['apps']['emailadmin'])
		{
			$GLOBALS['menuData'][] = array(
				'description' => 'mail account',
				'url'         => '/index.php',
				'extradata'   => 'menuaction=emailadmin.emailadmin_wizard.edit',
				'options'     => "onclick=\"egw_openWindowCentered2(this,'_blank',720,530,'yes'); return false;\"",
			);
		}
	}

    /**
     * Hook called after group emailadim settings has to be modified
     *
     * @param array $data
     * @param int $data['account_id'] numerical id
     */
    static function edit_group($data)
    {
		#echo "called hook and function<br>";
		#_debug_array($data);
		unset($data);	// not used
		# somehow the $data var seems to be quite sparsely populated, so we dont check any further
        if (#!empty($data['account_id']) && $data['account_id'] < 0 && // can't set it on add
            $GLOBALS['egw_info']['user']['apps']['emailadmin'])
        {
            $GLOBALS['menuData'][] = array(
                'description' => 'eMailAdmin: Group assigned Profile',
                'url' => '/index.php',
                'extradata' => 'menuaction=emailadmin.emailadmin_ui.index'
            );
        }
    }

    /**
     * Hook called before an account get deleted
     *
     * @param array $data
     * @param int $data['account_id'] numerical id
     * @param string $data['account_lid'] account-name
     * @param int $data['new_owner'] account-id of new owner, or false if data should get deleted
     */
	static function deleteaccount(array $data)
	{
		// as mail accounts contain credentials, we do NOT assign them to user users
		emailadmin_account::delete(0, $data['account_id']);

		if((int)$data['account_id'] > 0 &&
			$GLOBALS['egw_info']['user']['apps']['emailadmin'])
		{
			$boemailadmin = new emailadmin_bo();
			$profileList = $boemailadmin->getProfileList(null, null, null,(int) $data['account_id']);
			if (is_array($profileList)) {
				foreach ($profileList as $value) {
					$boemailadmin->delete($value['profileID']);
				}
			}
		}

		self::accountHooks($data);
	}

	/**
	 * Several hooks calling an instanciated emailadmin_imapbase
	 *
	 * @param string|array $hookData
	 */
	static public function accountHooks($hookData)
	{
		if (($default_profile_id = emailadmin_bo::getDefaultAccID()))
		{
			$imapbase = emailadmin_imapbase::forceEAProfileLoad($default_profile_id);

			switch(is_array($hookData) ? $hookData['location'] : $hookData)
			{
				case 'addaccount':
					$imapbase->addAccount($hookData);
					break;
				case 'deleteaccount':
					$imapbase->deleteAccount($hookData);
					break;
				case 'editaccount':
					$imapbase->updateAccount($hookData);
					break;
			}
			emailadmin_bo::unsetCachedObjects($default_profile_id);
		}
	}

    /**
     * Hook called before a group get deleted
     *
     * @param array $data
     * @param int $data['account_id'] numerical id
     * @param string $data['account_name'] account-name
     */
	static function deletegroup(array $data)
	{
		emailadmin_account::delete(0, $data['account_id']);

		if ((int)$data['account_id'] < 0 &&
			$GLOBALS['egw_info']['user']['apps']['emailadmin'])
		{
			$boemailadmin = new emailadmin_bo();
			$profileList = $boemailadmin->getProfileList(null, null,(int) $data['account_id']);
			if (is_array($profileList)) {
				foreach ($profileList as $value) {
					$boemailadmin->soemailadmin->deleteProfile($value['profileID']);
				}
			}
		}
	}

	/**
	 * Detect imap and smtp server plugins from EMailAdmin's inc directory
	 *
	 * @param string|array $data location string or array with key 'location' and other params
	 * @return array
	 */
	public static function server_types($data)
	{
		$location = is_array($data) ? $data['location'] : $data;
		$extended = is_array($data) ? $data['extended'] : false;

		$types = array();
		foreach(scandir($dir=EGW_INCLUDE_ROOT.'/emailadmin/inc') as $file)
		{
			$matches = null;
			if (!preg_match('/^class\.([^.]*(smtp|imap|postfix|dovecot|dbmail)[^.*]*)\.inc\.php$/', $file, $matches)) continue;
			$class_name = $matches[1];
			include_once($dir.'/'.$file);
			if (!class_exists($class_name)) continue;

			$is_imap = $class_name == 'emailadmin_imap' || is_subclass_of($class_name, 'emailadmin_imap');
			$is_smtp = $class_name == 'emailadmin_smtp' || is_subclass_of($class_name, 'emailadmin_smtp') && $class_name != 'defaultsmtp';

			if ($is_smtp && $location == 'smtp_server_types' || $is_imap && $location == 'imap_server_types')
			{
				// only register new imap-class-names
				if ($is_imap && $class_name == emailadmin_account::getIcClass ($class_name, true)) continue;

				$type = array(
					'classname' => $class_name,
					'description' => is_callable($function=$class_name.'::description') ? call_user_func($function) : $class_name,
				);

				if ($is_imap) $type['protocol'] = 'imap';

				$types[$class_name] = $type;
			}
		}
		if (!$extended)
		{
			foreach($types as $class_name => &$type)
			{
				$type = $type['description'];
			}
		}
		//error_log(__METHOD__."(".array2string($data).") returning ".array2string($types));
		return $types;
	}
}
