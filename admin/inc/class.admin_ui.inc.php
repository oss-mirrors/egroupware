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
	 * New index page
	 *
	 * @param array $content
	 * @param string $msg
	 */
	public function index(array $content=null, $msg='')
	{
		$tpl = new etemplate('admin.index');

		$content = array();
		$content['msg'] = 'Hi Ralf ;-)';
		$sel_options['tree'] = $this->tree_data();
		$tpl->exec('admin.admin_ui.index', $content, $sel_options);
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
						}
						$parent =& $parent[$path]['item'];
					}
					$data['text'] = lang($data['text']);
					if (!empty($data['title'])) $data['title'] = lang($data['title']);
					$parent[$data['id']] = $data;
				}
			}
		}
		elseif ($root == '/groups')
		{
			$tree['item'][] = array(
				'text' => 'Admins',
				'id' => '/groups/Admins',
			);
			$tree['item'][] = array(
				'text' => 'Default',
				'id' => '/groups/Default',
			);
		}
		self::strip_item_keys($tree['item']);
		//_debug_array($tree); exit;
		return $tree;
	}

	private static function strip_item_keys(&$items)
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
			error_log(__METHOD__."(".array2string(func_get_args()).")");
		}
		return array_merge($GLOBALS['egw']->hooks->process('admin', array('admin')), self::$hook_data);
	}
}
