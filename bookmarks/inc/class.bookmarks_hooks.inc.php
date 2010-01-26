<?php
/**
 * Bookmarks - Admin-, Preferences- and SideboxMenu-Hooks
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package bookmarks
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id: $
 */

/**
 * Class containing admin, preferences and sidebox-menus (used as hooks)
 */
class bookmarks_hooks
{
	/**
	 * Hook called by link-class to include bookmarks in link system
	 *
	 * @return array with method-names
	 */
	static function search_link() {
		return array(
			'query'      => 'bookmarks.bookmarks_bo.link_query',
			'title'      => 'bookmarks.bookmarks_bo.link_title',
			//'titles'     => 'infolog.infolog_bo.link_titles',
			'view'       => array(
				'menuaction' => 'bookmarks.bookmarks_ui.view',
			),
			'view_id'    => 'bm_id',
			'view_list'	=>	'bookmarks.bookmarks_ui.list',
			'add' => array(
				'menuaction' => 'bookmarks.bookmarks_ui.add',
			),
			'add_app'    => 'bookmarks',
			'add_id'     => 'bm_id',
			'add_popup'  => '750x550',
		);
	}

	/**
	 * hooks to build sidebox-menu plus the admin and preferences sections
	 *
	 * @param string/array $args hook args
	 */
	static function all_hooks($args)
	{
		$appname = 'bookmarks';
		$location = is_array($args) ? $args['location'] : $args;
		//echo "<p>admin_prefs_sidebox_hooks::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		if ($location == 'sidebox_menu')
		{
			$file = Array(
				'Tree view'        => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui.tree'),
				'List view'        => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui._list'),
				'New bookmark'     => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui.create'),
				'Import Bookmarks' => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui.import'),
				'Export Bookmarks' => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui.export')
			);
			display_sidebox($appname,$GLOBALS['egw_info']['apps']['bookmarks']['title'].' '.lang('Menu'),$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['preferences'] && $location != 'admin')
		{
			$file = array(
				'Preferences'	=> $GLOBALS['egw']->link('/index.php','menuaction=preferences.uisettings.index&appname='.$appname),
				'Grant Access'    => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
				'Edit Categories' => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=' . $appname . '&cats_level=True&global_cats=True')
			);
			if ($location == 'preferences')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Preferences'),$file);
			}
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{
			$file = Array(
				'Site Configuration' => egw::link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
				'Global Categories' => egw::link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname),
				'Custom fields' => egw::link('/index.php','menuaction=admin.customfields.edit&appname=' . $appname),
			);
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Admin'),$file);
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
		/* Settings array for this app */
		$settings = array(
			'defaultview' => array(
				'type'   => 'select',
				'label'  => 'Default view for bookmarks',
				'name'   => 'defaultview',
				'values' => array(
					'list'	=>	lang('List view'),
					'tree'	=>	lang('Tree view')
				),
				'help'   => 'This is the view Bookmarks uses when you enter the application. ',
				'xmlrpc' => True,
				'admin'  => False
			),
		);
		return $settings;
	}
}
?>
