<?php
/**
 * eGroupWare Gallery2 integration
 *
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006-9 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * eGroupWare Gallery2 integration: User syncronisation hooks
 *
 * This class gets either called via a hook if a user gets created, changed or deleted in eGroupWare
 * or can be called manualy, eg. from index to create a new user.
 * At the moment we only sync eGW Admins and the membership to g2's site-admins group. No other group memberships!
 */
class g2_integration
{
	/**
	 * Errors reported by G2
	 *
	 * @var string
	 */
	var $error;
	/**
	 * G2's prefix for _GET & _POST vars
	 *
	 * @var string
	 */
	var $prefix = 'g2_';
	/**
	 * returned data from G2's handleRequest
	 *
	 * @var array
	 */
	var $data;
	/**
	 * $_GET from before G2 was initialised
	 *
	 * @var array
	 */
	var $get_before_g2;

	/**
	 * Constructor, setting up the GalleryEmbed object
	 *
	 * @param boolean $hooked=true called as hook, pass fullinit=true to GalleryEmbed::init(), default yes
	 * @param string $url gallery url, default null = use /gallery (inside eGW)
	 * @return g2_integration
	 */
	function g2_integration($hooked=true,$url=null)
	{
		$this->get_before_g2 = $_GET;	// save $_GET from before G2 was initialised

		if (!file_exists(EGW_SERVER_ROOT.'/gallery/gallery2/config.php'))
		{
			$this->error = '<h3>'.lang('Gallery2 is NOT installed, you need to %1do that%2 first!!!',
				'<a href="gallery2/install/" target="_blank">','</a>')."</h3>\n".
				'<p><b>'.lang('Please note:')."</b></p>\n".
				'<p>'.lang('Use the same database settings as for your eGW installation (see your %1 file) and leave the table- and colum-prefix as suggested.',
					'<b>header.inc.php</b>')."</p>\n".
				'<p>'.lang('Use %1 as G2 data directory.','<b>'.$GLOBALS['egw_info']['server']['files_dir'].'/gallery'.'</b>')."</p>\n".
				'<p>'.lang('The administrator account has to be an existing eGroupWare account with exactly the same spelling!').'<br />'.
				lang('All other eGroupWare users will be created by the G2 integration including there admin rights.')."</p>\n".
				'<p>'.lang('For multiple eGroupWare instances, you have to remove %1, to be able to install the next instance.','<b>gallery/gallery2/config.php</b>').'<br />'.
				lang('After successful installation of all instances, you have to copy %1 to %2!','<b>gallery/gallery2_config.php</b>','<b>gallery/gallery2/config.php</b>')."</p>\n".
				'<p>'.lang('All filenames are relative to %1.','<b>'.EGW_SERVER_ROOT.'</b>')."</p>";

			return;
		}
		require_once(EGW_INCLUDE_ROOT.'/gallery/gallery2' . '/modules/core/classes/GalleryDataCache.class');
		GalleryDataCache::put('G2_EMBED', 1, true);
		require(EGW_INCLUDE_ROOT.'/gallery/gallery2' . '/main.php');
		require(EGW_INCLUDE_ROOT.'/gallery/gallery2' . '/modules/core/classes/GalleryEmbed.class');

		if (($ret = GalleryEmbed::init(array(
	    	'embedUri' => $url ? $url : $GLOBALS['egw']->link('/gallery/index.php'),
	    	'g2Uri'    => $GLOBALS['egw_info']['server']['webserver_url'].'/gallery/gallery2/',
	    	'loginRedirect' => $GLOBALS['egw_info']['server']['webserver_url'].'/login.php',
//				'embedSessionString' => 'sessionid=',	// gets added by egw::link() to embedUri if necessary
	     	'gallerySessionId' => egw_session::get_request('GALLERYSID'),
			'activeUserId' => $GLOBALS['egw_info']['user']['account_lid'] == 'anonymous' ? null : $GLOBALS['egw_info']['user']['account_id'],
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
				$this->error .= $GLOBALS['egw']->translation->convert($ret2 ? $ret2->getAsHtml() : $ret->getAsHtml(),'utf-8');
			}
		}
		elseif (!$hooked)
		{
			$this->error = $this->checkSetSiteAdmin($GLOBALS['egw_info']['user']['account_id']);
		}
		if ($hooked)
		{
			register_shutdown_function(array('GalleryEmbed','done'));
		}
		// all CreateObject or ExecMethod should use THIS instance!
		$GLOBALS['g2_integration'] =& $this;
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
				elseif ($ret->getErrorCode() & ERROR_OBSOLETE_DATA)
				{
					// not an error, just indicating no updated needed (nothing changed)
				}
				else
				{
					die($ret->getAsHtml());
				}
			}
			$this->checkSetSiteAdmin($data['account_id']);
			GalleryEmbed::done();
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
		if (class_exists('GalleryEmbed') && ($ret = GalleryEmbed::logout(array(
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
	 * @param string $location 'admin' || 'sidebox_menu'
	 */
	function menus($location)
	{
		if (is_array($location)) $location = $location['location'];

		$blocks = array();

		if (is_array($this->data['sidebarBlocksHtml']))
		{
			foreach($this->data['sidebarBlocksHtml'] as $n => $block)
			{
				//echo "block $n:<pre>".htmlspecialchars($block)."</pre>\n";
				if (strlen($block) > 2)
				{
					// block with own title, give him an own sidebox
					if (preg_match('/(<h3[^>]*> ?)(.*)( ?<\\/h3>)/',$block,$matches))
					{
						$title = $matches[2];
						unset($matches[0]);
						$block = str_replace(implode('',$matches),'',$block);

						$blocks[$title] = array(array(
							'text' => $block,
							'link' => false,
							'no_lang' => true,
							'icon' => false,
						));
					}
					else	// other blocks get collected in the "Gallery Menu"
					{
						if (!isset($file))
						{
							$file = array();
							$blocks[lang('Gallery Menu')] =& $file;
						}
						if (strpos($block,'block-core-ItemLinks') !== false)
						{
							foreach(explode("\n",$block) as $link)
							{
								$link = trim($link);
								if (!$link || substr($link,0,4) == '<div' || $link == '</div>') continue;

								$file[] = array(
									'text'    => $link,
									'link' => false,
									'no_lang' => true,
								);
							}
						}
						else
						{
							$file[] = array(
								'text' => $block,
								'link' => false,
								'no_lang' => true,
								'icon' => false,
							);
						}
					}
				}
			}
		}
		// G2's site-administration
		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$blocks[lang('Admin')] = array(
				'Site configuration' => $GLOBALS['egw']->link('/gallery/index.php',array('g2_view' => 'core.SiteAdmin')),
			);
		}
		$content = '';
		foreach($blocks as $title => $block)
		{
			switch ($location)
			{
				case 'admin':
					display_section('gallery',$block);
					break;

				case 'sidebox_menu':
					display_sidebox('gallery',$title,$block);
					break;

				case 'sitemgr':	// G2 sidebar for sitemgr
					if (!isset($block[0]['icon']) || $block[0]['icon'])
					{
						foreach($block as $text => $link)
						{
							if (!is_array($link)) $link = array('text' => '<a href="'.$link.'">'.$text.'</a>');

							if (isset($link['icon']) && !$link['icon'])
							{
								if ($ul_open) $content .= "</ul>\n";
								$ul_open = false;
								$content .= $link['text'];
							}
							else
							{
								if (!$ul_open) $content .= '<ul style="padding-left: 17px; margin: 0px">'."\n";
								$ul_open = true;
								$content .= '<li>'.$link['text']."</li>\n";
							}
						}
						if ($ul_open) $content .= "</ul>\n";
						$ul_open = false;
					}
					else
					{
						$content .= $block[0]['text'];
					}
					break;
			}
		}
		return $content;
	}

	/**
	 * Call GalleryEmbed::handleRequest(); and parse and convert the returned content
	 *
	 * @var string $type either 'complete', 'core' or 'sidebar'
	 * @var string &$title string returns G2's title
	 * @return string the content
	 */
	function handleRequest($type,&$title)
	{
		if (is_null($this->data))
		{
			// translate posted content for G2 to utf-8
			if ($_POST && ($charset = $GLOBALS['egw']->translation->charset()) != 'utf-8')
			{
				foreach($_POST as $name => $value)
				{
					if (substr($name,0,strlen($this->prefix)) == $this->prefix)
					{
						$_POST[$name] = $GLOBALS['egw']->translation->convert($value,$charset,'utf-8');
					}
				}
				$_REQUEST = array_merge($_REQUEST,$_POST);
			}
			GalleryCapabilities::set('showSidebarBlocks', $type == 'complete');

			$this->data = GalleryEmbed::handleRequest();

			if ($this->data['isDone'])	// redirect, download, ...
			{
				$GLOBALS['egw']->common->egw_exit();
			}
			$this->data = $GLOBALS['egw']->translation->convert($this->data,'utf-8');
		}
		if ($type != 'sidebar')
		{
			list($title,$css,$js) = GalleryEmbed::parseHead($this->data['headHtml']);
			$GLOBALS['egw_info']['flags']['java_script'] .= implode("\n",$js);

			return implode("\n",$css)."\n".$this->data['bodyHtml'];
		}
		// G2 sidebar for sitemgr
		return $this->menus('sitemgr');
	}

	/**
	 * get an image block from G2
	 *
	 * @param array $params see GalleryEmbed::getImageBlock
	 * @return string
	 */
	function imageBlock($params)
	{
		list($error,$block,$head) = GalleryEmbed::getImageBlock($params);

		$content = $error ? $error->getAsHtml() : $head.$block;

		return $GLOBALS['egw']->translation->convert($content,'utf-8');
	}

	/**
	 * fetch availible frames from G2
	 *
	 * @return array with 2 elements: 0 => array frameId => label pairs, 1 => string with url to sample page)
	 */
	function frameTypes()
	{
		list ($ret, $imageframe) = GalleryCoreApi::newFactoryInstance('ImageFrameInterface_1_1');
		if ($ret) die($ret->getAsHtml());
		list ($ret, $frames) = $imageframe->getImageFrameList();
		if ($ret) die($ret->getAsHtml());

		//list ($ret, $sample_url) = $imageframe->getSampleUrl();
		//if ($ret) die($ret->getAsHtml());
		// the url returned from G2 is wrong, duno ...
		$sample_url = $GLOBALS['egw']->link('/gallery/index.php',array('g2_view'=>'imageframe.Sample'));

		return array($GLOBALS['egw']->translation->convert($frames,'utf-8'),$sample_url);
	}

	/**
	 * Get translated list of image block types from G2
	 *
	 * unfortunally there's no function for that in G2's imageblock ...
	 *
	 * @return array
	 */
	function imageBlockTypes()
	{
		list ($ret, $module) = GalleryCoreApi::loadPlugin('module', 'imageblock', true);
		if ($ret) die($ret->getAsHtml());

		return $GLOBALS['egw']->translation->convert(array(
			'randomImage'  => $module->translate('Random Image'),
			'recentImage'  => $module->translate('Newest Image'),
			'viewedImage'  => $module->translate('Most Viewed Image'),
			'randomAlbum'  => $module->translate('Random Album'),
			'recentAlbum'  => $module->translate('Newest Album'),
			'viewedAlbum'  => $module->translate('Most Viewed Album'),
			'dailyImage'   => $module->translate('Picture of the Day'),
			'weeklyImage'  => $module->translate('Picture of the Week'),
			'monthlyImage' => $module->translate('Picture of the Month'),
			'dailyAlbum'   => $module->translate('Album of the Day'),
			'weeklyAlbum'  => $module->translate('Album of the Week'),
			'monthlyAlbum' => $module->translate('Album of the Month'),
		),'utf-8')+array('specificItem' => lang('specific item (requires itemId!)'));
	}
}