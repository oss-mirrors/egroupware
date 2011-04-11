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
		$options = array(
			// Defines not defined here
			/*WIKI_ACL_ALL*/  '_0' => lang('everyone'),
			/*WIKI_ACL_USER*/ '_1' => lang('users'),
			/*WIKI_ACL_ADMIN*/'_2' => lang('admins'),
		);
		$accs = $GLOBALS['egw']->accounts->get_list('groups');
		foreach($accs as $acc)
		{
			if ($acc['account_type'] == 'u')
			{
				$options[$acc['account_id']] = common::grab_owner_name($acc['account_id']);
			}
		}
		foreach($accs as $acc)
		{
			if ($acc['account_type'] == 'g' && (!$owngroups || ($owngroups && in_array($acc['account_id'],(array)$mygroups))))
			{
				$options[$acc['account_id']] = common::grab_owner_name($acc['account_id']);
			}
		}
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
			'default_read' => array(
				'type'   => 'multiselect',
				'label'  => 'Default read permission',
				'name'   => 'default_read',
				'values' => $options,
				'help'   => 'Default read permissions for creating a new page',
				'xmlrpc' => True,
				'admin'  => False,
			),
			'default_write' => array(
				'type'   => 'multiselect',
				'label'  => 'Default write permission',
				'name'   => 'default_write',
				'values' => $options,
				'help'   => 'Default write permissions for creating a new page',
				'xmlrpc' => True,
				'admin'  => False,
			)
		);
		
		if ($GLOBALS['egw_info']['user']['apps']['notifications'])
		{
			$details = array(
				'Title'		=>	lang('Title'),
				'Summary'	=>	lang('Summary'),
				'Category'	=>	lang('Category'),
				'Editor'	=>	lang('Person who changed the page'),
				'Content'	=>	lang('Page content'),
			);
			$settings += array(
				'notification_section' => array(
					'type'   => 'subsection',
					'title'  => 'Change Notification',
				),
				'notification_read' => array(
					'type'	=> 'check',
					'label'	=> 'Pages I have read access',
					'name'	=> 'notification_read',
					'help'	=> 'If a page I have read access to is changed, send a notification',
					'default' => 0
				),
				'notification_write' => array(
					'type'	=> 'check',
					'label'	=> 'Pages I have write access',
					'name'	=> 'notification_write',
					'help'	=> 'If a page I have write access to is changed, send a notification',
					'default' => 0
				),
				'notification_regex' => array(
					'type'	=> 'text',
					'label'	=> 'Pages that match this regular expression',
					'name'	=> 'notification_regex',
					'help'	=> 'If a page title matches this regular expression, send a notification.  You can look at title, name, lang, text using name: regex',
					'default' => ''
				),
				'notification_message' => array(
					'type'	=> 'notify',
					'label'	=> 'Message',
					'name'	=> 'notification_message',
					'help'	=> 'Message to send',
					'rows'	=> 3,
					'cols'	=> 50,
					'values'	=> $details,
					'default'	=> 'On $$Date$$ $$Editor$$ changed $$Title$$
$$Summary$$
$$Content$$'
				)
			);
		}
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
			'Site Configuration' => egw::link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
		//	'Lock / Unlock Pages' => $GLOBALS['egw']->link('/wiki/index.php','action=admin&locking=1'),
			'Block / Unblock hosts' => egw::link('/wiki/index.php','action=admin&blocking=1'),
			'Rebuild Links' => egw::link('/wiki/index.php','menuaction=wiki.wiki_hooks.rebuildlinks'),
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

	/**
	 * rebuildlinks
	 *
	 */
	function rebuildlinks()
	{
		@set_time_limit(0);

		if (!$GLOBALS['egw_info']['user']['apps']['admin'])
		{
			// error_log( 'Rebuilding Links ... -> Access not allowed ');
			$GLOBALS['egw']->redirect_link('/index.php');
		}
		error_log(__METHOD__.__LINE__. ' Rebuilding EGW Link Table Entries.');
		$bo = new wiki_bo;
		global $pagestore, $page, $ParseEngine, $Entity, $ParseObject;
		if ($bo->debug) error_log(__METHOD__.__LINE__. ' Read all Artikles - ... ');
		$i=0;
		$l=0;
		foreach($bo->find(str_replace(array('*','?'),array('%','_'),'%')) as $p)
		{
			$i++;
			$Entity=array(); // this one grows like hell, and eats time as we loop, so we reset that one on each go
			$page = $p;
			if ($bo->debug) error_log(__METHOD__.__LINE__.'['.$i.']' .' Processing '.$p['name'].' - '.$p['title'].' ('.$p['lang'].') ...');
			// delete the links of the page
			if ($bo->debug) $starrt = microtime(true);
			$bo->clear_link($p);
			$start = microtime();
			$j = count($Entity);

			if ($bo->debug) $start = microtime(true);
			parseText($p['text'], $ParseEngine, $ParseObject);
			if ($bo->debug) 
			{
				$end = microtime(true);
				$time= $end - $start;
				error_log(__METHOD__.__LINE__.'['.$j.']' ." Action parseText took ->$time seconds");
			}

			if ($bo->debug) $start = microtime(true);
			for(; $j < count($Entity); $j++)
			{
				if($Entity[$j][0] == 'ref')
					{$l++;$pagestore->new_link($page, $Entity[$j][1]); }
			}
			if ($bo->debug)
			{
				$end = microtime(true);
				$time= $end - $start;
				error_log(__METHOD__.__LINE__.'['.$j.']' ." Action loop and link took ->$time seconds");
			
				$ennd = microtime(true);
				$time= $ennd - $starrt;
				error_log(__METHOD__.__LINE__.' ['.$i.']' ." Action for ".$p['name']." ".$p['title']." ( ".$p['lang']." ) took ->$time seconds");
			}

			//if ($i >100) break;
		}
		error_log(__METHOD__.__LINE__.' '.$i." Pages processed. $l Links inserted (or count updated).");
		error_log(__METHOD__.__LINE__. ' Redirect back to Admin Page ');
		$GLOBALS['egw']->redirect_link('/admin/index.php');
	}
}
