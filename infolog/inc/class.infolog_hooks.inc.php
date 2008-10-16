<?php
/**
 * InfoLog - Admin-, Preferences- and SideboxMenu-Hooks
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package infolog
 * @copyright (c) 2003-6 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Class containing admin, preferences and sidebox-menus (used as hooks)
 */
class infolog_hooks
{
	/**
	 * Hook called by link-class to include infolog in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return array with method-names
	 */
	static function search_link($location)
	{
		return array(
			'query'      => 'infolog.infolog_bo.link_query',
			'title'      => 'infolog.infolog_bo.link_title',
			'titles'     => 'infolog.infolog_bo.link_titles',
			'view'       => array(
				'menuaction' => 'infolog.infolog_ui.index',
				'action' => 'sp'
			),
			'view_id'    => 'action_id',
			'add' => array(
				'menuaction' => 'infolog.infolog_ui.edit',
				'type'   => 'task'
			),
			'add_app'    => 'action',
			'add_id'     => 'action_id',
			'add_popup'  => '750x550',
			'file_access'=> 'infolog.infolog_bo.file_access',
		);
	}

	/**
	 * hooks to build sidebox-menu plus the admin and preferences sections
	 *
	 * @param string/array $args hook args
	 */
	static function all_hooks($args)
	{
		$appname = 'infolog';
		$location = is_array($args) ? $args['location'] : $args;
		//echo "<p>admin_prefs_sidebox_hooks::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		if ($location == 'sidebox_menu')
		{
			$file = array(
				'infolog list' => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'infolog.infolog_ui.index' )),
				array(
					'text' => '<a class="textSidebox" href="'.htmlspecialchars($GLOBALS['egw']->link('/index.php',array(
							'menuaction' => 'infolog.infolog_ui.edit',
						))).'" target="_blank" onclick="window.open(this.href,this.target,\'dependent=yes,width=750,height=550,scrollbars=yes,status=yes\'); return false;">'.lang('Add').'</a>',
					'no_lang' => true,
				)
			);
			display_sidebox($appname,$GLOBALS['egw_info']['apps']['infolog']['title'].' '.lang('Menu'),$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['preferences'] && $location != 'admin')
		{
			$file = array(
				'Preferences'     => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uisettings.index&appname='.$appname),
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
				'Site configuration' => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'infolog.infolog_ui.admin' )),
				'Global Categories'  => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'admin.uicategories.index',
					'appname'    => $appname,
					'global_cats'=> True)),
				'Custom fields, typ and status' => $GLOBALS['egw']->link('/index.php',array(
					'menuaction' => 'infolog.infolog_customfields.edit')),
				'CSV-Import'         => $GLOBALS['egw']->link('/infolog/csv_import.php')
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
	 * populates $GLOBALS['settings'] for the preferences
	 */
	static function settings()
	{
		/* Setup some values to fill the array of this app's settings below */
		$ui = new infolog_ui();	// need some labels from
		$filters = $show_home = array();
		$show_home[] = lang("DON'T show InfoLog");
		foreach($ui->filters as $key => $label)
		{
			$show_home[$key] = $filters[$key] = lang($label);
		}

		// migrage old filter-pref 1,2 to the filter one 'own-open-today'
		if (isset($GLOBALS['type']) && in_array($GLOBALS['egw']->preferences->{$GLOBALS['type']}['homeShowEvents'],array('1','2')))
		{
			$GLOBALS['egw']->preferences->add('infolog','homeShowEvents','own-open-today',$GLOBALS['type']);
			$GLOBALS['egw']->preferences->save_repository();
		}
		$show_links = array(
			'all'    => lang('all links and attachments'),
			'links'  => lang('only the links'),
			'attach' => lang('only the attachments'),
			'none'   => lang('no links or attachments'),
				'no_describtion' => lang('no describtion, links or attachments'),
		);
		$show_details = array(
			0 => lang('No'),
			1 => lang('Yes'),
			2 => lang('Only for details'),
		);

		/* Settings array for this app */
		$GLOBALS['settings'] = array(
			'defaultFilter' => array(
				'type'   => 'select',
				'label'  => 'Default Filter for InfoLog',
				'name'   => 'defaultFilter',
				'values' => $filters,
				'help'   => 'This is the filter InfoLog uses when you enter the application. Filters limit the entries to show in the actual view. There are filters to show only finished, still open or futures entries of yourself or all users.',
				'xmlrpc' => True,
				'admin'  => False
			),
			'homeShowEvents' => array(
				'type'   => 'select',
				'label'  => 'InfoLog filter for the main screen',
				'name'   => 'homeShowEvents',
				'values' => $show_home,
				'help'   => 'Should InfoLog show up on the main screen and with which filter. Works only if you dont selected an application for the main screen (in your preferences).',
				'xmlrpc' => True,
				'admin'  => False
			),
			'listNoSubs' => array(
				'type'   => 'check',
				'label'  => 'List no Subs/Childs',
				'name'   => 'listNoSubs',
				'help'   => 'Should InfoLog show Subtasks, -calls or -notes in the normal view or not. You can always view the Subs via there parent.',
				'xmlrpc' => True,
				'admin'  => False
			),
			'show_links' => array(
				'type'   => 'select',
				'label'  => 'Show in the InfoLog list',
				'name'   => 'show_links',
				'values' => $show_links,
				'help'   => 'Should InfoLog show the links to other applications and/or the file-attachments in the InfoLog list (normal view when you enter InfoLog).',
				'xmlrpc' => True,
				'admin'  => False
			),
			'never_hide' => array(
				'type'   => 'check',
				'label'  => 'Never hide search and filters',
				'name'   => 'never_hide',
				'help'   => 'If not set, the line with search and filters is hidden for less entries then "max matches per page" (as defined in your common preferences).',
				'xmlrpc' => True,
				'admin'  => False
			),
			'show_percent' => array(
				'type'   => 'select',
				'label'  => 'Show status and percent done separate',
				'name'   => 'show_percent',
				'values' => $show_details,
				'help'   => 'Should the Infolog list show the percent done only for status ongoing or two separate icons.',
				'xmlrpc' => True,
				'admin'  => False
			),
			'show_id' => array(
				'type'   => 'select',
				'label'  => 'Show ticket Id',
				'name'   => 'show_id',
				'values' => $show_details,
				'help'   => 'Should the Infolog list show a unique numerical Id, which can be used eg. as ticket Id.',
				'xmlrpc' => True,
				'admin'  => False
			),
			'set_start' => array(
				'type'   => 'select',
				'label'  => 'Startdate for new entries',
				'name'   => 'set_start',
				'values' => array(
					'date'     => lang('todays date'),
					'datetime' => lang('actual date and time'),
					'empty'    => lang('leave it empty'),
				),
				'help'   => 'To what should the startdate of new entries be set.',
				'xmlrpc' => True,
				'admin'  => False
			),
			'cal_show' => array(
				'type'   => 'multiselect',
				'label'  => 'Which types should the calendar show',
				'name'   => 'cal_show',
				'values' => $ui->bo->enums['type'],
				'help'   => 'Can be used to show further InfoLog types in the calendar or limit it to show eg. only tasks.',
				'xmlrpc' => True,
				'admin'  => False
			),
			'cat_add_default' => array(
				'type'   => 'select',
				'label'  => 'Default categorie for new Infolog entries',
				'name'   => 'cat_add_default',
				'values' => self::all_cats(),
				'help'   => 'You can choose a categorie to be preselected, when you create a new Infolog entry',
				'xmlrpc' => True,
				'admin'  => False
			),

		);

		// notification preferences
		$GLOBALS['settings']['notify_creator'] = array(
			'type'   => 'check',
			'label'  => 'Receive notifications about own items',
			'name'   => 'notify_creator',
			'help'   => 'Do you want a notification, if items you created get updated?',
			'xmlrpc' => True,
			'admin'  => False,
		);
		$GLOBALS['settings']['notify_assigned'] = array(
			'type'   => 'select',
			'label'  => 'Receive notifications about items assigned to you',
			'name'   => 'notify_assigned',
			'help'   => 'Do you want a notification, if items get assigned to you or assigned items get updated?',
			'values' => array(
				'0' => lang('No'),
				'1' => lang('Yes'),
				'assignment' => lang('Only if I get assigned or removed'),
			),
			'xmlrpc' => True,
			'admin'  => False,
		);

		// to add options for more then 3 days back or in advance, you need to update soinfolog::users_with_open_entries()!
		$options = array(
			'0'   => lang('No'),
			'-1d' => lang('one day after'),
			'0d'  => lang('same day'),
			'1d'  => lang('one day in advance'),
			'2d'  => lang('%1 days in advance',2),
			'3d'  => lang('%1 days in advance',3),
		);
		$GLOBALS['settings']['notify_due_delegated'] = array(
			'type'   => 'select',
			'label'  => 'Receive notifications about due entries you delegated',
			'name'   => 'notify_due_delegated',
			'help'   => 'Do you want a notification, if items you delegated are due?',
			'values' => $options,
			'xmlrpc' => True,
			'admin'  => False,
		);
		$GLOBALS['settings']['notify_due_responsible'] = array(
			'type'   => 'select',
			'label'  => 'Receive notifications about due entries you are responsible for',
			'name'   => 'notify_due_responsible',
			'help'   => 'Do you want a notification, if items you are responsible for are due?',
			'values' => $options,
			'xmlrpc' => True,
			'admin'  => False,
		);
		$GLOBALS['settings']['notify_start_delegated'] = array(
			'type'   => 'select',
			'label'  => 'Receive notifications about starting entries you delegated',
			'name'   => 'notify_start_delegated',
			'help'   => 'Do you want a notification, if items you delegated are about to start?',
			'values' => $options,
			'xmlrpc' => True,
			'admin'  => False,
		);
		$GLOBALS['settings']['notify_start_responsible'] = array(
			'type'   => 'select',
			'label'  => 'Receive notifications about starting entries you are responsible for',
			'name'   => 'notify_start_responsible',
			'help'   => 'Do you want a notification, if items you are responsible for are about to start?',
			'values' => $options,
			'xmlrpc' => True,
			'admin'  => False,
		);

		return true;	// otherwise prefs say it cant find the file ;-)
	}

	/**
	 * Return InoLog Categories (used for setting )
	 *
	 * @return array
	 */
	private static function all_cats()
	{
		$categories = new categories('','infolog');

		foreach((array)$categories->return_sorted_array(0,False,'','','',true) as $cat)
		{
			$s = str_repeat('&nbsp;',$cat['level']) . stripslashes($cat['name']);

			if ($cat['app_name'] == 'phpgw' || $cat['owner'] == '-1')
			{
				$s .= ' &#9830;';
			}
			$sel_options[$cat['id']] = $s;	// 0.9.14 only
		}
		return $sel_options;
	}

	/**
	 * Verification hook called if settings / preferences get stored
	 *
	 * Installs a task to send async infolog notifications at 2h everyday
	 *
	 * @param array $data
	 */
	static function verify_settings($data)
	{
		if ($data['prefs']['notify_due_delegated'] || $data['prefs']['notify_due_responsible'] ||
			$data['prefs']['notify_start_delegated'] || $data['prefs']['notify_start_responsible'])
		{
			require_once(EGW_API_INC.'/class.asyncservice.inc.php');

			$async =& new asyncservice();
			//$async->cancel_timer('infolog-async-notification');

			if (!$async->read('infolog-async-notification'))
			{
				$async->set_timer(array('hour' => 2),'infolog-async-notification','infolog.infolog_bo.async_notification',null);
			}
		}
	}
}
