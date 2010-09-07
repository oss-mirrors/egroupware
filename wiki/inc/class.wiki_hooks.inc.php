<?php
/**
 * eGroupware Wiki - Hooks
 *
 * @link http://www.egroupware.org
 * @package wiki
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (C) 2004-9 by RalfBecker-AT-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Static hooks for wiki
 */
class wiki_hooks
{
	/**
	 * Settings hook
	 *
	 * @param array|string $hook_data
	 */
	static public function settings($hook_data)
	{
		$settings = array(
			'rtfEditorFeatures' => array(
				'type'   => 'select',
				'label'  => 'Features of the editor?',
				'name'   => 'rtfEditorFeatures',
				'values' => array(
					'simple'   => lang('Simple'),
					'extended' => lang('Regular'),
					'advanced' => lang('Everything'),
				),
				'help'   => 'You can customize how many icons and toolbars the editor shows.',
				'xmlrpc' => True,
				'admin'  => False,
				'default'=> 'extended',
			),
		);
		if ($GLOBALS['egw_info']['user']['apps']['filemanager'])
		{
			$settings['upload_dir'] = array(
				'type'  => 'input',
				'label' => 'VFS upload directory',
				'name'  => 'upload_dir',
				'size'  => 50,
				'help'  => 'Start directory for image browser of rich text editor in EGroupware VFS (filemanager).',
				'xmlrpc' => True,
				'admin'  => False,
			);
		}
		return $settings;
	}

	/**
	 * Hook for admin menu
	 *
	 * @param array|string $hook_data
	 */
	public static function admin($hook_data)
	{
		$title = $appname = 'wiki';
		$file = Array(
			'Site Configuration' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
		//	'Lock / Unlock Pages' => $GLOBALS['egw']->link('/wiki/index.php','action=admin&locking=1'),
			'Block / Unblock hosts' => $GLOBALS['egw']->link('/wiki/index.php','action=admin&blocking=1'),
		);
		//Do not modify below this line
		display_section($appname,$title,$file);
	}

	/**
	 * Hook for sidebox menu
	 *
	 * @param array|string $hook_data
	 */
	public static function sidebox_menu($hook_data)
	{
		$appname = 'wiki';
		$menu_title = lang('Wiki Menu');
		$file = Array(
			'Recent Changes' => $GLOBALS['egw']->link('/wiki/index.php','page=RecentChanges'),
			'Preferences' => $GLOBALS['egw']->link('/index.php',array('menuaction'=>'preferences.uisettings.index','appname'=>'wiki')),
		);
		display_sidebox($appname,$menu_title,$file);

		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$menu_title = lang('Wiki Administration');
			$file = Array(
				'Site Configuration' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			//	'Lock / Unlock Pages' => $GLOBALS['egw']->link('/wiki/index.php','action=admin&locking=1'),
				'Block / Unblock Hosts' => $GLOBALS['egw']->link('/wiki/index.php','action=admin&blocking=1')
			);
			display_sidebox($appname,$menu_title,$file);
		}
	}

	/**
	 * Hook called by link-class to include infolog in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return array with method-names
	 */
	static function search_link($location)
	{
		return array(
			'query'      => 'wiki.wiki_bo.link_query',
			'title'      => 'wiki.wiki_bo.link_title',
			'view'       => array(
				'menuaction' => 'wiki.wiki_ui.view',
			),
			'view_id'    => 'page',
		);
	}
}
