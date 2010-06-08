<?php
/**
 * Stylite: jdots template
 *
 * @link http://www.stylite.de
 * @package jdots
 * @author Andreas Stöckel <as@stylite.de>
 * @author Ralf Becker <rb@stylite.de>
 * @author Nathan Gray <ng@stylite.de>
 * @version $Id$
 */

/**
* Stylite jdots template
*/
class jdots_framework extends egw_framework
{
	/**
	 * Whether javascript:egw_link_handler calls (including given app) should be returned by the "link" function
	 * or just the link
	 * 
	 * @var string
	 */
	private static $link_app;

	/**
	* Constructor
	*
	* @param string $template='idots' name of the template
	* @return idots_framework
	*/
	function __construct($template='jdots')
	{
		parent::__construct($template);		// call the constructor of the extended class
		
		$this->template_dir = '/jdots';		// we are packaged as an application
	}
	
	/**
	 * Extract applicaton name from given url (incl. GET parameters)
	 * 
	 * @param string $url
	 * @return string appname or NULL if it could not be detected (eg. constructing javascript urls)
	 */
	public static function app_from_url($url)
	{
		if (preg_match('/menuaction=([a-z0-9_-]+)\./i',$url,$matches))
		{
			return $matches[1];
		}
		if (preg_match('/\/([^\/]+)\/([^\/]+\.php)?(\?|\/|$)/',$url,$matches))
		{
			return $matches[1];
		}
		//error_log(__METHOD__."('$url') could NOT detect application!");
		return null;
	}

	/**
	 * Link url generator
	 *
	 * @param string	$string	The url the link is for
	 * @param string|array	$extravars	Extra params to be passed to the url
	 * @param string $link_app=null if appname or true, some templates generate a special link-handler url
	 * @return string	The full url after processing
	 */
	static function link($url = '', $extravars = '', $link_app=null)
	{
		if (is_null($link_app)) $link_app = self::$link_app;
		$link = parent::link($url,$extravars);
		// $link_app === true --> detect application, otherwise use given application
		if ($link_app && (is_string($link_app) || ($link_app = self::app_from_url($link))))
		{
			$link = "javascript:egw_link_handler('$link','$link_app');";
		}
		return $link;		
	}

	/**
	 * Redirects direct to a generated link
	 *
	 * If a session could not be verified or during login time, jDots is NOT used!
	 * It's only used if user preferences are loaded AND user select it in his prefs
	 * 
	 * @param string	$string	The url the link is for
	 * @param string|array	$extravars	Extra params to be passed to the url
	 */
	static function redirect_link($url = '',$extravars='')
	{
		return parent::redirect_link($url, $extravars);
	}

	/**
	 * Returns the html-header incl. the opening body tag
	 *
	 * @return string with html
	 */
	function header()
	{
		// make sure header is output only once
		if (self::$header_done) return '';
		self::$header_done = true;

		// add a content-type header to overwrite an existing default charset in apache (AddDefaultCharset directiv)
		header('Content-type: text/html; charset='.translation::charset());

		// catch error echo'ed before the header, ob_start'ed in the header.inc.php
		$content = ob_get_contents();
		ob_end_clean();

		// the instanciation of the template has to be here and not in the constructor,
		// as the old Template class has problems if restored from the session (php-restore)
		// todo: check if this is still true
		$this->tpl = new Template(common::get_tpl_dir('jdots'));
		$this->tpl->set_file(array('_head' => 'head.tpl'));
		$this->tpl->set_block('_head','head');
		$this->tpl->set_block('_head','framework');
		
		// include needed javascript files
		$js = $GLOBALS['egw']->js;
		$js->validate_file('.','egw_fw','jdots');
		$js->validate_file('.','egw_fw_ui','jdots');
		$js->validate_file('.','egw_fw_classes','jdots');
		$js->validate_file('jquery','jquery');
		$js->validate_file('jquery','jquery-ui');
		$js->validate_file('.','egw_json');

		// load jscalendar for calendar users
		if ($GLOBALS['egw_info']['user']['apps']['calendar'])
		{
			$GLOBALS['egw']->jscalendar;
		}
		// load dhtmlxtree for pm or email users
		if ($GLOBALS['egw_info']['user']['apps']['projectmanager'] || $GLOBALS['egw_info']['user']['apps']['felamimail'])
		{
			$GLOBALS['egw_info']['flags']['java_script'] .= html::tree(null,null);
		}
		// load framework if not yet loaded for an url WITHOUT cd=yes
		if (!isset($_GET['cd']) || $_GET['cd'] != 'yes')
		{
			$GLOBALS['egw_info']['flags']['java_script'] .= '<script type="text/javascript">
	if (typeof top.framework == "undefined" && !opener)
	{
		window.location.search += window.location.search ? "&cd=yes" : "?cd=yes";
	}
</script>';
		}
		$this->tpl->set_var($vars = $this->_get_header());
		$content .= $this->tpl->fp('out','head').$content;
		
		if (!isset($_GET['cd']) || $_GET['cd'] != 'yes')
		{
			//Set the sidebox content
			$app = $GLOBALS['egw_info']['flags']['currentapp'];
			$sidebox = json_encode($this->get_sidebox($app));
			$md5 = md5($sidebox);
			$content .= '<script type="text/javascript">
	if (typeof window.parent.framework != "undefined")
	{
		var app = window.parent.framework.getApplicationByName("'.$app.'");
		window.parent.framework.setSidebox(app,'.$sidebox.',"'.$md5.'");
		window.parent.framework.setWebsiteTitle(app,"'.htmlspecialchars($vars['website_title']).'");
	}';
			// if manual is enabled, assamble manual url and define global callManual() function
			if ($GLOBALS['egw_info']['user']['apps']['manual'])
			{
				$manual_url = egw::link('/index.php',array(
						'menuaction' => 'manual.uimanual.view',
					)+($GLOBALS['egw_info']['flags']['params']['manual'] ?
					$GLOBALS['egw_info']['flags']['params']['manual'] : array(
						'referer' => ($_SERVER['HTTPS'] ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
					)));
				$content .= '
	function callManual(_url)
	{
		var _framework = window.opener ? window.opener.parent.framework :  window.parent.framework;
		if (typeof _url == "undefined" || !_url) _url = "'.$manual_url.'";
		_framework.linkHandler(_url,"manual");
	}';
			}
			$content .= "\n</script>";
			return $content;
		}
		// from here on, only framework
		$content .= '<script type="text/javascript">
	function callManual()
	{
		framework.activeApp.iframe.contentWindow.callManual();		
	}
</script>';

		// topmenu
		$vars = $this->_get_navbar($apps = $this->_get_navbar_apps());
		$this->tpl->set_var($this->topmenu($vars,$apps));
		
		// hook after_navbar (eg. notifications)
		$this->tpl->set_var('hook_after_navbar',$this->_get_after_navbar());

		// add framework div's
		$this->tpl->set_var($this->_get_footer());
		$content .= $this->tpl->fp('out','framework');
		$content .= self::footer();
		
		echo $content;
		common::egw_exit();
	}
	
	private $topmenu_items;
	private $topmenu_info_items;

	/**
	 * Compile entries for topmenu:
	 * - regular items: links
	 * - info items
	 *
	 * @param array $vars
	 * @param array $apps
	 * @return array
	 */
	function topmenu(array $vars,array $apps)
	{
		$this->topmenu_items = $this->topmenu_info_items = array();

		parent::topmenu($vars,$apps);
		$vars['topmenu_items'] = "<ul>\n<li>".implode("</li>\n<li>",$this->topmenu_items)."</li>\n</ul>";
		$vars['topmenu_info_items'] = '<div class="topmenu_info_item">'.
			implode("</div>\n".'<div class="topmenu_info_item">',$this->topmenu_info_items)."</div>\n";
		
		$this->topmenu_items = $this->topmenu_info_items = null;

		return $vars;
	}


	/**
	* Add menu items to the topmenu template class to be displayed
	*
	* @param array $app application data
	* @param mixed $alt_label string with alternative menu item label default value = null
	* @param string $urlextra string with alternate additional code inside <a>-tag
	* @access protected
	* @return void
	*/
	function _add_topmenu_item(array $app_data,$alt_label=null)
	{
		if ($app_data['name'] == 'manual')
		{
			$app_data['url'] = "javascript:callManual();";
		}
		elseif (strpos($app_data['url'],'logout.php') === false)
		{
			$app_data['url'] = "javascript:egw_link_handler('".$app_data['url']."','".
				(isset($GLOBALS['egw_info']['user']['apps'][$app_data['name']]) ? 
					$app_data['name'] : 'about')."')";
		}
		$this->topmenu_items[] = '<a href="'.htmlspecialchars($app_data['url']).'">'.
			htmlspecialchars($alt_label ? $alt_label : $app_data['title']).'</a>';
	}

	/**
	 * Add info items to the topmenu template class to be displayed
	 *
	 * @param string $content html of item
	 * @access protected
	 * @return void
	 */
	function _add_topmenu_info_item($content)
	{
		$this->topmenu_info_items[] = $content;
	}

	/**
	 * Returns the html from the body-tag til the main application area (incl. opening div tag)
	 * 
	 * jDots does NOT use a navbar!
	 *
	 * @return string with html
	 */
	function navbar()
	{
		return '';
	}

	/**
	 * displays a login screen
	 * 
	 * Currently not used for jDots, as it's no login template set!
	 *
	 * @param string $extra_vars for login url
	 */
	function login_screen($extra_vars)
	{

	}

	/**
	 * displays a login denied message
	 * 
	 * Currently not used for jDots, as it's no login template set!
	 */
	function denylogin_screen()
	{

	}

	/**
	 * Array containing sidebox menus by applications and menu-name
	 * 
	 * @var array
	 */
	private $sideboxes;

	/**
	 * Should calls the first call to self::sidebox create an opened menu
	 * 
	 * @var boolean
	 */
	private $sidebox_menu_opened = true;

	/**
	 * Callback for sideboxes hooks, collects the data in a private var
	 *
	 * @param string $appname
	 * @param string $menu_title
	 * @param array $file
	 */
	public function sidebox($appname,$menu_title,$file)
	{
		if (!isset($file['menuOpened'])) $file['menuOpened'] = (boolean)$this->sidebox_menu_opened;
		$this->sidebox_menu_opened = false;

		$this->sideboxes[$appname][$menu_title] = $file;
	}

	/**
	 * Return sidebox data for an application
	 * 
	 * @param $appname
	 * @return array of array(
	 * 		'menu_name' => (string),	// menu name, currently md5(title)
	 * 		'title'     => (string),	// translated title to display
	 * 		'opened'    => (boolean),	// menu opend or closed
	 *  	'entries'   => array(
	 *			array(
	 *				'lang_item' => translated menu item or html, i item_link === false
	 * 				'icon_or_star' => url of bullet images, or false for none
	 *  			'item_link' => url or false (lang_item contains complete html)
	 *  			'target' => target attribute fragment, ' target="..."' 
	 *			),
	 *			// more entries
	 *		),
	 * 	),
	 *	array (
	 *		// next menu
	 *	)
	 */
	public function get_sidebox($appname)
	{
		if (!isset($this->sideboxes[$appname]))
		{
			self::$link_app = $appname;
			// allow other apps to hook into sidebox menu of an app, hook-name: sidebox_$appname
			$this->sidebox_menu_opened = true;
			$GLOBALS['egw']->hooks->process('sidebox_'.$appname,array($appname),true);	// true = call independent of app-permissions
			
			// calling the old hook
			$this->sidebox_menu_opened = true;
			$GLOBALS['egw']->hooks->single('sidebox_menu',$appname);
			self::$link_app = null;
		}
		//If there still is no sidebox content, return null here
		if (!isset($this->sideboxes[$appname]))
		{
			return null;
		}

		$data = array();
		foreach($this->sideboxes[$appname] as $menu_name => &$file)
		{
			$current_menu = array(
				'menu_name' => md5($menu_name),	// can contain html tags and javascript!
				'title' => $menu_name,
				'entries' => array(),
				'opened' => (boolean)$file['menuOpened'],
			);
			foreach($file as $item_text => $item_link)
			{
				if ($item_text === 'menuOpened' ||	// flag, not menu entry
					$item_text === '_NewLine_' || $item_link === '_NewLine_')
				{
					continue;
				}
				if (strtolower($item_text) == 'grant access' && $GLOBALS['egw_info']['server']['deny_user_grants_access'])
				{
					continue;
				}
		
				$var = array();
				$var['icon_or_star'] = $GLOBALS['egw_info']['server']['webserver_url'] . $this->template_dir.'/images/bullet.png';
				$var['target'] = '';
				if(is_array($item_link))
				{
					if(isset($item_link['icon']))
					{
						$app = isset($item_link['app']) ? $item_link['app'] : $appname;
						$var['icon_or_star'] = $item_link['icon'] ? common::image($app,$item_link['icon']) : False;
					}
					$var['lang_item'] = isset($item_link['no_lang']) && $item_link['no_lang'] ? $item_link['text'] : lang($item_link['text']);
					$var['item_link'] = $item_link['link'];
					if ($item_link['target'])
					{
						// we only support real targets not html markup with target in it
						if (strpos($item_link['target'], 'target=') === false &&
							strpos($item_link['target'], '"') === false)
						{
							$var['target'] = $item_link['target'];
						}
					}
				}
				else
				{
					$var['lang_item'] = lang($item_text);
					$var['item_link'] = $item_link;
				}
				$current_menu['entries'][] = $var;
			}
			$data[] = $current_menu;
		}
		return $data;
	}
	
	/**
	 * Ajax callback to store opened/closed status of menu's within one apps sidebox
	 * 
	 * @param string $app
	 * @param string $menu_name
	 * @param boolean $opened true = user opened menu, false = user closed it
	 * @todo implement storing and using stored values in get_sidebox
	 */
	public function ajax_sidebox_menu_opened($app,$menu_name,$opened)
	{
		error_log(__METHOD__."('$app','$menu_name',$opened)");
	}
	
	/**
	 * Return sidebox data for an application
	 * 
	 * Format see get_sidebox()
	 * 
	 * @param $appname
	 */
	public function ajax_sidebox($appname)
	{
		$response = egw_json_response::get();
		$response->data($this->get_sidebox($appname));
	}

	/**
	 * Prepare an array with apps used to render the navbar
	 * 
	 * @return array of array(
	 *  'name'  => app / directory name
	 * 	'title' => translated application title
	 *  'url'   => url to call for index
	 *  'icon'  => icon name
	 *  'icon_app' => application of icon
	 *  'icon_hover' => hover-icon, if used by template
	 *  'target'=> ' target="..."' attribute fragment to open url in target, popup or ''
	 * )
	 */
	public function ajax_navbar_apps()
	{
		$apps = parent::_get_navbar_apps();

		unset($apps['logout']);	// never display it
		if (isset($apps['about'])) $apps['about']['noNavbar'] = true;
		if (isset($apps['preferences'])) $apps['preferences']['noNavbar'] = true;
		if (isset($apps['manual'])) $apps['manual']['noNavbar'] = true;
		if (isset($apps['home'])) $apps['home']['noNavbar'] = true;

		// no need for website icon, if we have sitemgr
		if (isset($apps['sitemgr']) && isset($apps['sitemgr-link']))
		{
			unset($apps['sitemgr-link']);
		}
		if (!($default_app = $GLOBALS['egw_info']['user']['preferences']['common']['default_app']))
		{
			$default_app = 'home';
		}
		if (isset($apps[$default_app]))
		{
			$apps[$default_app]['isDefault'] = true;
		}
		$response = egw_json_response::get();
		$response->data(array_values($apps));
	}
	
	/**
	 * Returns the html from the closing div of the main application area to the closing html-tag
	 *
	 * @return string
	 */
	function footer()
	{
		return "\n</body>\n</html>\n";
	}
	
	/**
	 * Return javascript (eg. for onClick) to open manual with given url
	 * 
	 * @param string $url
	 * @return string
	 */
	function open_manual_js($url)
	{
		return "callManual('$url')";
	}
}
