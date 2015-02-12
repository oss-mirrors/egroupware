<?php
/**
 * Bookmarks - Admin-, Preferences- and SideboxMenu-Hooks
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package bookmarks
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
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
			'view_popup'  => '750x300',
			'add' => array(
				'menuaction' => 'bookmarks.bookmarks_ui.create',
			),
			'add_app'    => 'bookmarks',
			'add_id'     => 'bm_id',
			'add_popup'  => '750x300',
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
				'List view'        => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui._list&ajax=true'),
				'New bookmark'     => "javascript:egw_openWindowCentered2('".egw::link('/index.php',array(
						'menuaction' => 'bookmarks.bookmarks_ui.create'
					),false)."','_blank',750,300,'yes');",
				'Import Bookmarks' => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui.import'),
				'Export Bookmarks' => $GLOBALS['egw']->link('/index.php','menuaction=bookmarks.bookmarks_ui.export')
			);
			display_sidebox($appname,$GLOBALS['egw_info']['apps']['bookmarks']['title'].' '.lang('Menu'),$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{
			$file = Array(
				'Site Configuration' => egw::link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname, 'admin'),
				'Global Categories' => egw::link('/index.php','menuaction=admin.admin_categories.index&appname=' . $appname, 'admin'),
				'Custom fields' => egw::link('/index.php','menuaction=admin.customfields.edit&appname=' . $appname, 'admin'),
			);
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				$GLOBALS['egw']->framework->sidebox($appname,lang('Admin'),$file,'admin');
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
		// Import / Export for nextmatch
		if ($GLOBALS['egw_info']['user']['apps']['importexport'])
		{
			$definitions = new importexport_definitions_bo(array(
				'type' => 'export',
				'application' => 'bookmarks'
			));
			$options = array(
				'~nextmatch~'	=>	lang('Old fixed definition')
			);
			foreach ((array)$definitions->get_definitions() as $identifier)
			{
				try
				{
					$definition = new importexport_definition($identifier);
				}
				catch (Exception $e)
				{
					// permission error
					continue;
				}
				if ($title = $definition->get_title())
				{
					$options[$title] = $title;
				}
				unset($definition);
			}
			$default_def = 'export-bookmarks';
			$settings['nextmatch-export-definition'] = array(
				'type'   => 'select',
				'values' => $options,
				'label'  => 'Export definition to use for nextmatch export',
				'name'   => 'nextmatch-export-definition',
				'help'   => lang('If you specify an export definition, it will be used when you export'),
				'run_lang' => false,
				'xmlrpc' => True,
				'admin'  => False,
				'default'=> isset($options[$default_def]) ? $default_def : false,
			);
		}
		return $settings;
	}

	/**
	 * ACL rights and labels used by Calendar
	 *
	 * @param string|array string with location or array with parameters incl. "location", specially "owner" for selected acl owner
	 */
	public static function acl_rights($params)
	{
		return array(
			// ACL works differently in bookmarks, we change the label to ease confusion
			acl::READ    => 'private',
			acl::EDIT    => 'edit',
			acl::DELETE  => 'delete',
		);
	}

	/**
	 * Hook to tell framework we use standard categories method
	 *
	 * @param string|array $data hook-data or location
	 * @return boolean
	 */
	public static function categories($data)
	{
		return true;
	}
}
