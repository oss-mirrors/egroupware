<?php
/**
 * phpgfreechat - Admin-, Preferences- and SideboxMenu-Hooks
 *
 * @link http://www.egroupware.org
 * @author Klaus Leithoff <kl-AT-stylite.de>
 * @package phpfreechat
 * @copyright (c) 2010 by Klaus Leithoff <kl-AT-stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id: class.phpfreechat_hooks.inc.php 28994 2010-01-20 23:26:05Z ralfbecker $
 */

/**
 * Class containing admin, preferences and sidebox-menus (used as hooks)
 */
class phpfreechat_hooks
{
	/**
	 * Hook called by link-class to include phpfreechat in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return array with method-names
	 */
	static function search_link($location)
	{
		return array(
			'edit_popup'  => '750x580',
		);
	}

	/**
	 * site config
	 */
	static function admin()
	{
	}

	/**
	 * hooks to build sidebox-menu plus the admin and preferences sections
	 *
	 * @param string/array $args hook args
	 */
	static function all_hooks($args)
	{
		$appname = 'phpfreechat';
		$location = is_array($args) ? $args['location'] : $args;
		//echo "<p>admin_prefs_sidebox_hooks::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		if ($GLOBALS['egw_info']['user']['apps']['preferences'] && $location != 'admin')
		{
			// future possible prefs
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{
			$file = Array(
				'Site configuration' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uiconfig.index&appname='.$appname),
				'Global Categories'  => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'admin.admin_categories.index',
					'appname'    => $appname,
					'global_cats'=> false)),
			);
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				// no sidebox in popup
				//display_sidebox($appname,lang('Admin'),$file);
			}
		}
	}

	/**
	 * populates $settings for the preferences
	 *
	 * @return array
	 */
	static function settings()
	{
		return (array)$settings;
	}
}
