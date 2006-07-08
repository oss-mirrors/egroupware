<?php
/**
 * eGroupWare Gallery2 integration
 * 
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * eGroupWare Gallery2 integration: User syncronisation hooks
 * 
 * This class gets either called via a hook if a user gets created, changed or deleted in eGroupWare
 * or can be called manualy, eg. from index to create a new user.
 * At the moment we only sync eGW Admins and the membership to g2's site-admins group. No other group memberships!
 * 
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class g2_integration
{
	var $error;

	/**
	 * Constructor, setting up the GalleryEmbed object
	 *
	 * @param boolean $hooked=true called as hook, pass fullinit=true to GalleryEmbed::init(), default yes
	 * @return g2_integration
	 */
	function g2_integration($hooked=true)
	{
		global $g2_data;
		if (is_array($g2_data)) return;	// sideboxMenu hook

		if (!file_exists(EGW_SERVER_ROOT.'/gallery/gallery2/config.php'))
		{
			$this->error = lang('Gallery2 is NOT installed, you need to %1do that%2 first!!!',
				'<a href="gallery2/install/" target="_blank">','</a>');
			return;
		}
		require_once(EGW_INCLUDE_ROOT.'/gallery/gallery2' . '/modules/core/classes/GalleryDataCache.class');
		GalleryDataCache::put('G2_EMBED', 1, true);
		require(EGW_INCLUDE_ROOT.'/gallery/gallery2' . '/main.php');
		require(EGW_INCLUDE_ROOT.'/gallery/gallery2' . '/modules/core/classes/GalleryEmbed.class');
		
		if (($ret = GalleryEmbed::init(array(
	    	'embedUri' => $GLOBALS['egw']->link('/gallery/index.php'),
	    	'g2Uri'    => $GLOBALS['egw_info']['server']['webserver_url'].'/gallery/gallery2/',
	    	'loginRedirect' => $GLOBALS['egw_info']['server']['webserver_url'].'/login.php',
//				'embedSessionString' => 'sessionid=',	// gets added by egw::link() to embedUri if necessary
	     	'gallerySessionId' => $_REQUEST['sessionid'],
			'activeUserId' => $GLOBALS['egw_info']['user']['account_id'],
			'activeLanguage' => g2_integration::g2_lang($GLOBALS['egw_info']['user']['preferences']['common']['lang']),
//				'apiVersion' => (optional) array int major, int minor (check if your integration is compatible)
			'fullInit' => $hooked,
		))))
		{
			// If we are NOT on fullinit (called by hook) and the error is because the user does not exist
			// we create the user now, saved us the migration
			if (!$hooked && ($ret2 = GalleryEmbed::isExternalIdMapped($GLOBALS['egw_info']['user']['account_id'], 'GalleryUser')) &&
				$ret2->getErrorCode() & ERROR_MISSING_OBJECT)
			{
				$this->error = $this->addAccount($GLOBALS['egw_info']['user'],$hooked);
			} 
			else 
			{
				// The error we got wasn't due to a missing user, it was a real error
				$this->error = '<p>'.lang('An error occurred while trying to initialize G2.')."</p>\n";
				$$this->error .= $GLOBALS['egw']->translation->convert($ret2 ? $ret2->getAsHtml() : $ret->getAsHtml(),'utf-8');
			}
		}
		elseif (!$hooked)
		{
			$this->error = $this->checkSetSiteAdmin($GLOBALS['egw_info']['user']['account_id']);
		}
	}
	
	/**
	 * g2 lang-code (de_DE) from eGW lang-code (de, pt-br)
	 *
	 * @static 
	 * @param string $egw_lang
	 * @return string
	 */
	function g2_lang($egw_lang)
	{
		list($g2_lang,$contry) = explode('-',$egw_lang);
		if (!$contry) $contry = $g2_lang;
		$g2_lang .= '_'.strtoupper($contry);

		return $g2_lang;
	}
	
	/**
	 * create new account
	 *
	 * @param array $data
	 * @param boolean $hooked=true call GalleryEmbed::done()
	 * @return string error-message or null on success
	 */
	function addAccount($data,$hooked=true)
	{
		if ($this->error) return $this->error;	// eg. g2 not installed

		if ($data['account_id'] == $GLOBALS['egw_info']['user']['account_id'])
		{
			$lang = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
		}
		else
		{
			$prefs = new preferences($data['account_id']);
			$prefs = $prefs->read_repository();
			$lang = $prefs['common']['lang'];
			unset($prefs);
		}
		if (($ret = GalleryEmbed::createUser($data['account_id'],array(
			'username' => $data['account_lid'],
			'email'    => $data['account_email'], 
			'fullname' => $data['account_fullname'] ? $data['account_fullname'] : $data['account_fristname'].' '.$data['account_lastname'],
			'language' => $this->g2_lang($lang),
		))))
		{
			// check if user already exists and eGW user is admin => just create the mapping (eg. first admin user)
			if ($ret->getErrorCode() & ERROR_COLLISION && $GLOBALS['egw_info']['user']['apps']['admin'])
			{
				list($ret,$user) = GalleryCoreApi::fetchUserByUserName($GLOBALS['egw_info']['user']['account_lid']);
				if (!$ret) 
				{
					$ret = GalleryEmbed::addExternalIdMapEntry($GLOBALS['egw_info']['user']['account_id'],$user->getId(),'GalleryUser');
				}
			}
			if ($ret)
			{
				$this->error = $GLOBALS['egw']->translation->convert($ret->getAsHtml(),'utf-8');
				$content = '<p>'.lang('An error occurred during user creation in G2.')."</p>\n" .
					$this->error;
			}
		}
		if (!$this->error)
		{
			$content = $this->checkSetSiteAdmin($data['account_id']);
			
			if ($hooked) GalleryEmbed::done();
		}
		return $content;
	}
	
	/**
	 * Check if user is an eGW admin and make him an g2 site-admin or not
	 *
	 * @static 
	 * @param int $account_id
	 * @return string error-message or null
	 */
	function checkSetSiteAdmin($account_id)
	{
		$apps = $account_id == $GLOBALS['egw_info']['user']['account_id'] ? $GLOBALS['egw_info']['user']['apps'] :
			$GLOBALS['egw']->acl->get_user_applications($account_id);
			
		$isAdmin = isset($apps['admin']) && $apps['admin'];
		list($ret,$g2_user) = GalleryCoreApi::loadEntityByExternalId($account_id, 'GalleryUser');
		if (!$ret) 
		{
			$g2_user_id = $g2_user->getId();
		    if ($isAdmin !== GalleryCoreApi::isUserInSiteAdminGroup($g2_user_id))
		    {
		    	// (re)set site-admin rights in g2
				list ($ret, $adminGroupId) = GalleryCoreApi::getPluginParameter('module', 'core', 'id.adminGroup');
				if (!$ret) 
				{
					$ret = $isAdmin ? GalleryCoreApi::addUserToGroup($g2_user_id, $adminGroupId) :
						GalleryCoreApi::removeUserFromGroup($g2_user_id, $adminGroupId);
				}
		    }
		}
		if ($ret)
		{
			return $GLOBALS['egw']->translation->convert($ret->getAsHtml(),'utf-8');
		}
		return null;
	}
	
	/**
	 * change an account
	 * 
	 * No error-handling as the hook cant transport it back anyway!
	 *
	 * @param array $data account_id, account_lid, account_firstname, account_lastname, account_email
	 */
	function editAccount($data)
	{
		if (!$this->error)
		{
			$ret = GalleryEmbed::updateUser($data['account_id'],array(
				'username' => $data['account_lid'],
				'email'    => $data['account_email'], 
				'fullname' => $data['account_fullname'] ? $data['account_fullname'] : $data['account_fristname'].' '.$data['account_lastname'],
			));
			if ($ret)
			{
				if ($ret->getErrorCode() & ERROR_MISSING_OBJECT)	// not a g2 user yet
				{
					$error = $this->addAccount($data);
					if ($error) die($error);
				}
				else
				{
					die($ret->getAsHtml());
				}
			}
			else
			{
				$this->checkSetSiteAdmin($data['account_id']);
				GalleryEmbed::done();
			}
		}
	}
	
	/**
	 * delete an account
	 *
	 * No error-handling as the hook cant transport it back anyway!
	 *
	 * @param array $data keys: account_id, account_lid, new_owner
	 */
	function deleteAccount($data)
	{
		if (!$this->error)
		{
			$ret = GalleryEmbed::deleteUser($data['account_id']);
			if ($ret && !$ret->getErrorCode() && ERROR_MISSING_OBJECT) die($ret->getAsHtml());
			GalleryEmbed::done();
		}
	}
	
	/**
	 * Terminate the g2 session on eGW logout
	 *
	 */
	function logout()
	{
		if (($ret = GalleryEmbed::logout(array(
	    	'embedUri' => $GLOBALS['egw']->link('/gallery/index.php'),
//			'embedSessionString' => 'sessionid=',	// gets added by egw::link() to embedUri if necessary
	     	'gallerySessionId' => $_REQUEST['sessionid'],
		))))
		{
			die ($GLOBALS['egw']->translation->convert($ret->getAsHtml()));
		}
	}
	
	/**
	 * Sidebox menu hook: displays g2 menu's as sidebox
	 *
	 */
	function sideboxMenu()
	{
		global $g2_data;

		foreach($g2_data['sidebarBlocksHtml'] as $n => $block)
		{
			if (strlen($block) > 2)
			{
				if (preg_match('/(<h3[^>]*> ?)(.*)( ?<\\/h3>)/',$block,$matches))
				{
					$title = $matches[2];
					unset($matches[0]);
					$block = str_replace(implode('',$matches),'',$block);
					$file = array(array(
						'text' => $block,
						'link' => false,
						'no_lang' => true,
						'icon' => false,
					));
				}
				else
				{
					$title = lang('Gallery menu');
					$file = array();
					foreach(explode('<a href',str_replace('</div>','',$block)) as $i => $link)
					{
						if (!$i) continue;	// <div>
						
						$file[] = array(
							'text'    => '<a href'.trim($link),
							'link' => false,
							'no_lang' => true,
						);
					}
					
				}
				display_sidebox('gallery',$title,$file);
			}
		}
	}
}
