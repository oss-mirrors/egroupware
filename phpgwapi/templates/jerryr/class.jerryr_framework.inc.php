<?php
/**
 * EGroupware jerryr template set
 *
 * @link http://www.egroupware.org
 * @author Jerry Ruhe <jerry.ruhe@dilawri-group.ca>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> rewrite in 12/2006
 * @author Pim Snel <pim@lingewoud.nl> author of the idots template set
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage framework
 * @access public
 * @version $Id$
 */

require_once(EGW_SERVER_ROOT.'/phpgwapi/templates/idots/class.idots_framework.inc.php');

/**
 * eGW jerryr template
 */
class jerryr_framework extends idots_framework
{
	/**
	 * Constructor, calls the contstructor of the extended class
	 *
	 * @param string $template='jerryr'
	 * @return jerryr_framework
	 */
	function __construct($template='jerryr')
	{
		parent::__construct($template);
	}

	/**
	 * Check if current user agent is supported
	 *
	 * Currently we do NOT support:
	 * - iPhone, iPad, Android, SymbianOS due to iframe scrolling problems of Webkit
	 *
	 * @return boolean
	 */
	public static function is_supported_user_agent()
	{
		if (html::$ua_mobile)
		{
			return false;
		}
		return true;
	}

	/**
	* Display the string with html of the topmenu if its enabled
	*
	* @param array $vars
	* @param array $apps
	* @return string
	*/
	function topmenu(array $vars,array $apps)
	{
		$this->tplsav2->menuitems = array();
		$this->tplsav2->menuinfoitems = array();

		parent::topmenu($vars,$apps);

		$this->tplsav2->assign('info_icons',$this->topmenu_icon_arr);

		return $this->tplsav2->fetch('topmenu.tpl.php');
	}

	/**
	 * Return slide-out-menu config for idots.js
	 *
	 * @param string $show_menu_event='click'
	 * @return array
	 */
	protected function slide_out_menus($show_menu_event='click')
	{
		return array(
			array(
				'id' => 'menu1',
				'dir' => 'down',
				'left' => 45,
				'top' => 5,
				'width' => 188,
				'height' => 300,
				'pos' => 'right',
				'bind' => array(
					'#extra_icons_show' => array('event' => $show_menu_event, 'method' => 'showMenu'),
					'#menu1close' => array('event' => $show_menu_event, 'method' => 'hide'),
				),
			),
			array(
				'id' => 'menu2',
				'dir' => 'right',
				'left' => 0,
				'top' => 28,
				'width' => 160,
				'height' => 200,
				'bind' => array(
					'#menu2show' => array('event' => $show_menu_event, 'method' => 'showMenu'),
					'#menu2close' => array('event' => 'click', 'method' => 'hide'),
				),
			),
		);
	}
}
