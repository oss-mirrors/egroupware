<?php
/**
 * EGroupware: Admin app UI
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb@stylite.de>
 * @package admin
 * @copyright (c) 2013 by Ralf Becker <rb@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once EGW_INCLUDE_ROOT.'/etemplate/inc/class.etemplate.inc.php';

/**
 * UI for admin
 */
class admin_ui
{
	/**
	 * Methods callable via menuaction
	 * @var array
	 */
	public $public_functions = array(
		'index' => true,
	);

	/**
	 * Reference to global accounts object
	 *
	 * @var accounts
	 */
	private static $accounts;

	/**
	 * New index page
	 *
	 * @param array $content
	 * @param string $msg
	 */
	public function index(array $content=null, $msg='')
	{
		$tpl = new etemplate_new('admin.index');

		$content = array();
		$content['nm'] = array(
			'get_rows' => 'admin_ui::get_users',
			'no_cat' => true,
			'no_filter2' => true,
			'filter_label' => 'Group',
			'filter_no_lang' => true,
			'lettersearch' => true,
			'order' => 'account_lid',
			'sort' => 'ASC',
			'row_id' => 'account_id',
			'default_cols' => '!account_id,account_created',
			'actions' => self::user_actions(),
		);
		//$content['msg'] = 'Hi Ralf ;-)';
		$sel_options['tree'] = $this->tree_data();
		$sel_options['filter'] = array('' => lang('All'));
		foreach(self::$accounts->search(array(
			'type' => 'groups',
			'start' => false,
			'order' => 'account_lid',
			'sort' => 'ASC',
		)) as $account_id => $data)
		{
			$sel_options['filter'][$data['account_id']] = empty($data['account_description']) ? $data['account_lid'] : array(
				'label' => $data['account_lid'],
				'title' => $data['account_description'],
			);
		}
		$sel_options['account_primary_group'] = $sel_options['filter'];
		unset($sel_options['account_primary_group']['']);

		$actions = array(
			'view' => array(
				'onExecute' => 'javaScript:app.admin.group',
				'caption' => 'Show members',
				'enableId' => '^/groups/-\\d+',
				'default' => true,
			),
			'edit' => array(
				'onExecute' => 'javaScript:app.admin.group',
				'caption' => 'Edit group',
				'enableId' => '^/groups/-\\d+',
			),
			'acl' => array(
				'onExecute' => 'javaScript:app.admin.group',
				'caption' => 'Access control',
				'enableId' => '^/groups/-\\d+',
				'icon' => 'lock',
			),
			'delete' => array(
				'onExecute' => 'javaScript:app.admin.group',
				'confirm' => 'Delete this group',
				'caption' => 'Delete group',
				'enableId' => '^/groups/-\\d+',
			),
		);
		$tpl->setElementAttribute('tree', 'actions', $actions);

		$tpl->exec('admin.admin_ui.index', $content, $sel_options);
	}

	/**
	 * Actions on users
	 *
	 * @return array
	 */
	public static function user_actions()
	{
		$actions = array(
			'edit' => array(
				'caption' => 'Open',
				'default' => true,
				'allowOnMultiple' => false,
				'url' => 'menuaction=admin.uiaccounts.edit_user&account_id=$id',
				'group' => $group=0,
				'onExecute' => 'javaScript:app.admin.iframe_location',
			),
			'view' => array(
				'caption' => 'View',
				'allowOnMultiple' => false,
				'url' => 'menuaction=admin.uiaccounts.view_user&account_id=$id',
				'group' => $group,
				'onExecute' => 'javaScript:app.admin.iframe_location',
			),
			'acl' => array(
				'caption' => 'Access control',
				'allowOnMultiple' => false,
				'url' => 'menuaction=admin.admin_acl.index&account_id=$id',
				'group' => $group,
				'onExecute' => 'javaScript:app.admin.iframe_location',
				'icon' => 'lock',
			),
		);
		++$group;
		// supporting both old way using $GLOBALS['menuData'] and new just returning data in hook
		$apps = array_unique(array_merge(array('admin'), $GLOBALS['egw']->hooks->hook_implemented('edit_user')));
		foreach($apps as $n => $app)
		{
if ($app == 'felamimail') continue;	// disabled fmail for now, as it break whole admin, dono why
			$GLOBALS['menuData'] = $data = array();
			$data = $GLOBALS['egw']->hooks->single('edit_user', $app, true);
			if (!is_array($data)) $data = $GLOBALS['menuData'];
			foreach($data as $item)
			{
				// allow hook to return "real" actions, but still support legacy: description, url, extradata, options
				if (empty($item['caption']))
				{
					$item['caption'] = $item['description'];
					unset($item[$description]);
				}
				if (isset($item['url']) && isset($item['extradata']))
				{
					$item['url'] = $item['extradata'].'&account_id=$id';
					$item['id'] = substr($item['extradata'], 11);
					unset($item['extradata']);
					if ($item['options'] && preg_match('/(egw_openWindowCentered2?|window.open)\([^)]+,(\d+),(\d+).*(title="([^"]+)")?/', $item['options'], $matches))
					{
						$item['popup'] = $matches[2].'x'.$matches[3];
						$item['onExecute'] = 'javaScript:nm_action';
						if (isset($matches[5])) $item['tooltip'] = $matches[5];
						unset($item['options']);
					}
				}
				if (empty($item['icon'])) $item['icon'] = $app.'/navbar';
				if (empty($item['group'])) $item['group'] = $group;
				if (empty($item['onExecute'])) $item['onExecute'] = 'javaScript:app.admin.iframe_location';

				$actions[$item['id']] = $item;
			}
		}
		$actions['delete'] = array(
			'caption' => 'Delete',
			'group' => ++$group,
			'url' => 'menuaction=admin.uiaccounts.delete_user&account_id=$id',
			'onExecute' => 'javaScript:app.admin.iframe_location',
		);
		//error_log(__METHOD__."() actions=".array2string($actions));
		return $actions;
	}

	/**
	 * Callback for nextmatch to fetch users
	 *
	 * @param array $query
	 * @param array &$rows=null
	 * @return int total number of rows available
	 */
	public static function get_users(array $query, array &$rows=null)
	{
		$params = array(
			'type' => (int)$query['filter'] ? (int)$query['filter'] : 'accounts',
			'start' => $query['start'],
			'offset' => $query['num_rows'],
			'order' => $query['order'],
			'sort' => $query['sort'],
			'active' => false,
		);
		if ($query['searchletter'])
		{
			$params['query'] = $query['searchletter'];
			$params['query_type'] = 'start';
		}
		elseif($query['search'])
		{
			$params['query'] = $query['search'];
			$params['query_type'] = 'all';
		}

		$rows = self::$accounts->search($params);
		//error_log(__METHOD__."() accounts->search(".array2string($params).") total=".self::$accounts->total);

		foreach($rows as &$row)
		{
			$row['status'] = self::$accounts->is_expired($row) ?
				lang('Expired').' '.egw_time::to($row['account_expires'], true) :
					(!self::$accounts->is_active($row) ? lang('Disabled') :
						($row['account_expires'] != -1 ? lang('Expires').' '.egw_time::to($row['account_expires'], true) :
							lang('Enabled')));

			if (!self::$accounts->is_active($row)) $row['status_class'] = 'adminAccountInactive';
		}

		return self::$accounts->total;
	}

	/**
	 * Autoload tree from $_GET['id'] on
	 */
	public static function ajax_tree()
	{
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(self::tree_data(!empty($_GET['id']) ? $_GET['id'] : '/'));
		common::egw_exit();
	}

	/**
	 * Get data for navigation tree
	 *
	 * Example:
	 * array(
	 *	'id' => 0, 'item' => array(
	 *		array('id' => '/INBOX', 'text' => 'INBOX', 'tooltip' => 'Your inbox', 'open' => 1, 'im1' => 'kfm_home.png', 'im2' => 'kfm_home.png', 'child' => '1', 'item' => array(
	 *			array('id' => '/INBOX/sub', 'text' => 'sub', 'im0' => 'folderClosed.gif'),
	 *			array('id' => '/INBOX/sub2', 'text' => 'sub2', 'im0' => 'folderClosed.gif'),
	 *		)),
	 *		array('id' => '/user', 'text' => 'user', 'child' => '1', 'item' => array(
	 *			array('id' => '/user/birgit', 'text' => 'birgit', 'im0' => 'folderClosed.gif'),
	 *		)),
	 * ));
	 *
	 * @param string $root='/'
	 * @return array
	 */
	public static function tree_data($root = '/')
	{
		$tree = array('id' => $root === '/' ? 0 : $root, 'item' => array(), 'child' => 1);

		if ($root == '/')
		{
			$hook_data = self::call_hook();
			foreach($hook_data as $app => $app_data)
			{
				foreach($app_data as $text => $data)
				{
					if (!is_array($data))
					{
						$data = array(
							'link' => $data,
						);
					}
					if (empty($data['text'])) $data['text'] = $text;
					if (empty($data['id']))
					{
						$data['id'] = $root.($app == 'admin' ? 'admin' : 'apps/'.$app).'/';
						$data['id'] .= preg_match('/menuaction=([^&]+)/', $data['link'], $matches) ? $matches[1] : md5($link);
					}
					if (!empty($data['icon']))
					{
						$icon = $data['icon'];
						list(,$icon) = explode($GLOBALS['egw_info']['server']['webserver_url'], $icon);
						$icon = '../../../../..'.$icon;
						if ($data['child'] || $data['item'])
						{
							$data['im1'] = $data['im2'] = $icon;
						}
						else
						{
							$data['im0'] = $icon;
						}
					}
					unset($data['icon']);
					$parent =& $tree['item'];
					$parts = explode('/', $data['id']);
					if ($data['id'][0] == '/') array_shift($parts);	// remove root
					$last_part = array_pop($parts);
					$path = '';
					foreach($parts as $part)
					{
						$path .= ($path == '/' ? '' : '/').$part;
						if (!isset($parent[$path]))
						{
							$icon = $part == 'apps' ? common::image('phpgwapi', 'home') : common::image($part, 'navbar');
							list(,$icon) = explode($GLOBALS['egw_info']['server']['webserver_url'], $icon);
							$icon = '../../../../..'.$icon;
							$parent[$path] = array(
								'id' => $path,
								'text' => $part == 'apps' ? lang('Applications') : lang($part),
								//'im0' => 'folderOpen.gif',
								'im1' => $icon,
								'im2' => $icon,
								'item' => array(),
								'child' => 1,
							);
							if ($path == '/admin') $parent[$path]['open'] = true;
						}
						$parent =& $parent[$path]['item'];
					}
					$data['text'] = lang($data['text']);
					if (!empty($data['tooltip'])) $data['tooltip'] = lang($data['tooltip']);

					$parent[$data['id']] = self::fix_userdata($data);
				}
			}
		}
		elseif ($root == '/groups')
		{
			foreach($GLOBALS['egw']->accounts->search(array(
				'type' => 'groups',
				'order' => 'account_lid',
				'sort' => 'ASC',
			)) as $group)
			{
				$tree['item'][] = self::fix_userdata(array(
					'text' => $group['account_lid'],
					'id' => $root.'/'.$group['account_id'],
					'link' => egw::link('/index.php', array(
						'menuaction' => 'admin.uiaccounts.edit_group',
						'account_id' => $group['account_id'],
					)),
				));
			}
		}
		self::strip_item_keys($tree['item']);
		//_debug_array($tree); exit;
		return $tree;
	}

	/**
	 * Fix userdata as understood by tree
	 *
	 * @param array $data
	 * @return array
	 */
	private static function fix_userdata(array $data)
	{
		// store link as userdata, maybe we should store everything not directly understood by tree this way ...
		foreach(array_diff_key($data, array_flip(array(
			'id','text','tooltip','im0','im1','im2','item','child','select','open','call',
		))) as $name => $content)
		{
			$data['userdata'][] = array(
				'name' => $name,
				'content' => $content,
			);
			unset($data[$name]);
		}
		return $data;
	}

	/**
	 * Attribute 'item' has to be an array
	 *
	 * @param array $items
	 */
	private static function strip_item_keys(array &$items)
	{
		$items = array_values($items);
		foreach($items as &$item)
		{
			if (is_array($item) && isset($item['item']))
			{
				self::strip_item_keys($item['item']);
			}
		}
	}

	public static $hook_data = array();
	/**
	 * Return data from regular admin hook calling display_section() instead of returning it
	 *
	 * @return array appname => array of label => link/data pairs
	 */
	protected static function call_hook()
	{
		self::$hook_data = array();
		function display_section($appname,$file,$file2=False)
		{
			admin_ui::$hook_data[$appname] = $file2 ? $file2 : $file;
			//error_log(__METHOD__."(".array2string(func_get_args()).")");
		}
		return array_merge($GLOBALS['egw']->hooks->process('admin', array('admin')), self::$hook_data);
	}

	/**
	 * Init static variables
	 */
	public static function init_static()
	{
		self::$accounts = $GLOBALS['egw']->accounts;
	}
}
admin_ui::init_static();
