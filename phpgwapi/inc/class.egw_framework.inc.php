<?php
/**
 * EGroupware API - framework baseclass
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> rewrite in 12/2006
 * @author Pim Snel <pim@lingewoud.nl> author of the idots template set
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage framework
 * @access public
 * @version $Id$
 */

/**
 * eGW API - framework: virtual base class for all template sets
 *
 * This class creates / renders the eGW framework:
 *  a) html header
 *  b) navbar
 *  c) sidebox menu
 *  d) main application area
 *  e) footer
 * It replaces several methods in the common class and the diverse templates.
 *
 * Existing apps either set $GLOBALS['egw_info']['flags']['noheader'] and call common::egw_header() and
 * (if $GLOBALS['egw_info']['flags']['nonavbar'] is true) parse_navbar() or it's done by the header.inc.php include.
 * The app's hook_sidebox then calls the public function display_sidebox().
 * And the app calls common::egw_footer().
 *
 * This are the authors (and their copyrights) of the original egw_header, egw_footer methods of the common class:
 * This file written by Dan Kuykendall <seek3r@phpgroupware.org>
 * and Joseph Engo <jengo@phpgroupware.org>
 * and Mark Peters <skeeter@phpgroupware.org>
 * and Lars Kneschke <lkneschke@linux-at-work.de>
 * Copyright (C) 2000, 2001 Dan Kuykendall
 * Copyright (C) 2003 Lars Kneschke
 */
abstract class egw_framework
{
	/**
	 * Name of the template set, eg. 'idots'
	 *
	 * @var string
	 */
	var $template;

	/**
	 * Path relative to EGW_SERVER_ROOT for the template directory
	 *
	 * @var string
	 */
	var $template_dir;

	/**
	* true if $this->header() was called
	*
	* @var boolean
	*/
	static $header_done = false;
	/**
	* true if $this->navbar() was called
	*
	* @var boolean
	*/
	static $navbar_done = false;

	/**
	 * Constructor
	 *
	 * The constructor instanciates the class in $GLOBALS['egw']->framework, from where it should be used
	 *
	 * @return egw_framework
	 */
	function __construct($template)
	{
		$this->template = $template;

		if (!isset($GLOBALS['egw']->framework))
		{
			$GLOBALS['egw']->framework = $this;
		}
		$this->template_dir = '/phpgwapi/templates/'.$template;
	}

	/**
	 * Constructor for static variables
	 */
	public static function init_static()
	{
		self::$js_include_mgr = new egw_include_mgr(array(
			// We need LABjs, but putting it through egw_include_mgr causes it to re-load itself
			//'/phpgwapi/js/labjs/LAB.src.js',

			// allways load jquery (not -ui) and egw_json first
			'/phpgwapi/js/jquery/jquery.js',
			'/phpgwapi/js/./egw_json.js',
			// always include javascript helper functions
			'/phpgwapi/js/jsapi/jsapi.js',
			'/phpgwapi/js/jsapi/egw.js',
		));
	}

	/**
	 * PHP4-Constructor
	 *
	 * The constructor instanciates the class in $GLOBALS['egw']->framework, from where it should be used
	 *
	 * @deprecated use __construct()
	 */
	function egw_framework($template)
	{
		self::__construct($template);
	}

	/**
	 * Link url generator
	 *
	 * @param string $url	The url the link is for
	 * @param string/array	$extravars	Extra params to be passed to the url
	 * @return string	The full url after processing
	 */
	static function link($url, $extravars = '')
	{
		return $GLOBALS['egw']->session->link($url, $extravars);
	}

	/**
	 * Redirects direct to a generated link
	 *
	 * @param string $url	The url the link is for
	 * @param string/array	$extravars	Extra params to be passed to the url
	 * @return string	The full url after processing
	 */
	static function redirect_link($url, $extravars='')
	{
		egw::redirect(self::link($url, $extravars));
	}

	/**
	 * Renders an applicaton page with the complete eGW framework (header, navigation and menu)
	 *
	 * This is the (new) prefered way to render a page in eGW!
	 *
	 * @param string $content html of the main application area
	 * @param string $app_header=null application header, default what's set in $GLOBALS['egw_info']['flags']['app_header']
	 * @param string $navbar=null show the navigation, default !$GLOBALS['egw_info']['flags']['nonavbar'], false gives a typical popu
	 *
	 */
	function render($content,$app_header=null,$navbar=null)
	{
		if (!is_null($app_header)) $GLOBALS['egw_info']['flags']['app_header'] = $app_header;
		if (!is_null($navbar)) $GLOBALS['egw_info']['flags']['nonavbar'] = !$navbar;

		echo $this->header();

		if (!isset($GLOBALS['egw_info']['flags']['nonavbar']) || !$GLOBALS['egw_info']['flags']['nonavbar'])
		{
		   echo $this->navbar();
		}
		echo $content;

		echo $this->footer();
	 }

	/**
	 * Returns the html-header incl. the opening body tag
	 *
	 * @return string with html
	 */
	abstract function header(array $extra=array());

	/**
	 * Returns the html from the body-tag til the main application area (incl. opening div tag)
	 *
	 * If header has NOT been called, also return header content!
	 * No need to manually call header, this allows to postpone header so navbar / sidebox can include JS or CSS.
	 *
	 * @return string with html
	 */
	abstract function navbar();

	/**
	 * Return true if we are rendering the top-level EGroupware window
	 *
	 * A top-level EGroupware window has a navbar: eg. no popup and for a framed template (jdots) only frameset itself
	 *
	 * @return boolean $consider_navbar_not_yet_called_as_true=true
	 * @return boolean
	 */
	abstract function isTop($consider_navbar_not_yet_called_as_true=true);

	/**
	 * Returns the content of one sidebox
	 *
	 * @param string $appname
	 * @param string $menu_title
	 * @param array $file
	 */
	abstract function sidebox($appname,$menu_title,$file);

	/**
	 * Returns the html from the closing div of the main application area to the closing html-tag
	 *
	 * @return string
	 */
	abstract function footer();

	/**
	 * displays a login screen
	 *
	 * @string $extra_vars for login url
	 * @return string
	 */
	abstract function login_screen($extra_vars);

	/**
	 * displays a login denied message
	 *
	 * @return string
	 */
	abstract function denylogin_screen();

	/**
	 * Get footer as array to eg. set as vars for a template (from idots' head.inc.php)
	 *
	 * @return array
	 */
	protected function _get_footer()
	{
		$var = Array(
			'img_root'       => $GLOBALS['egw_info']['server']['webserver_url'] . $this->template_dir.'/images',
			'version'        => $GLOBALS['egw_info']['server']['versions']['phpgwapi']
		);
		$var['page_generation_time'] = '';
		if($GLOBALS['egw_info']['user']['preferences']['common']['show_generation_time'])
		{
			$totaltime = sprintf('%4.2lf',microtime(true) - $GLOBALS['egw_info']['flags']['page_start_time']);

			$var['page_generation_time'] = '<div id="divGenTime"><br/><span>'.lang('Page was generated in %1 seconds',$totaltime);
			if ($GLOBALS['egw_info']['flags']['session_restore_time'])
			{
				$var['page_generation_time'] .= ' '.lang('(session restored in %1 seconds)',
					sprintf('%4.2lf',$GLOBALS['egw_info']['flags']['session_restore_time']));
			}
			$var['page_generation_time'] .= '</span></div>';
		}
		$var['powered_by'] = lang('Powered by').' <a href="http://www.stylite.de/" target="_blank">Stylite\'s</a>'.
                        ' <a href="'.egw::link('/about.php','','about').'">EGroupware</a>'.
			' Community Version '.$GLOBALS['egw_info']['server']['versions']['phpgwapi'];

		return $var;
	}

	/**
	 * Get the (depricated) application footer
	 *
	 * @return string html
	 */
	protected static function _get_app_footer()
	{
		ob_start();
		// Include the apps footer files if it exists
		if (EGW_APP_INC != EGW_API_INC &&	// this prevents an endless inclusion on the homepage
			                                // (some apps set currentapp in hook_home => it's not releyable)
			(file_exists (EGW_APP_INC . '/footer.inc.php') || isset($_GET['menuaction'])) &&
			$GLOBALS['egw_info']['flags']['currentapp'] != 'home' &&
			$GLOBALS['egw_info']['flags']['currentapp'] != 'login' &&
			$GLOBALS['egw_info']['flags']['currentapp'] != 'logout' &&
			!@$GLOBALS['egw_info']['flags']['noappfooter'])
		{
			list($app,$class,$method) = explode('.',(string)$_GET['menuaction']);
			if ($class && is_object($GLOBALS[$class]) && is_array($GLOBALS[$class]->public_functions) &&
				isset($GLOBALS[$class]->public_functions['footer']))
			{
				$GLOBALS[$class]->footer();
			}
			elseif(file_exists(EGW_APP_INC.'/footer.inc.php'))
			{
				include(EGW_APP_INC . '/footer.inc.php');
			}
		}
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Get header as array to eg. set as vars for a template (from idots' head.inc.php)
	 *
	 * @param array $extra=array() extra attributes passed as data-attribute to egw.js
	 * @return array
	 */
	protected function _get_header(array $extra=array())
	{
		// get used language code (with a little xss check, if someone tries to sneak something in)
		if (preg_match('/^[a-z]{2}(-[a-z]{2})?$/',$GLOBALS['egw_info']['user']['preferences']['common']['lang']))
		{
			$lang_code = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
		}
		// IE specific fixes
		if (html::$user_agent == 'msie')
		{
			// tell IE to use it's own mode, not old compatibility modes (set eg. via group policy for all intranet sites)
			// has to be before any other header tags, but meta and title!!!
			$pngfix = '<meta http-equiv="X-UA-Compatible" content="IE=edge" />'."\n";

			// pngfix for IE6 defaults to yes
			if(!$GLOBALS['egw_info']['user']['preferences']['common']['disable_pngfix'] && html::$ua_version < 7)
			{
				$pngfix_src = $GLOBALS['egw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/js/pngfix.js';
				$pngfix .= '<!-- This solves the Internet Explorer PNG-transparency bug, but only for IE 5.5 - 6.0 and higher -->
				<!--[if lt IE 7.0]>
				<script src="'.$pngfix_src.'" type="text/javascript">
				</script>
				<![endif]-->';
			}
		}

		if(!$GLOBALS['egw_info']['user']['preferences']['common']['disable_slider_effects'])
		{
			$slider_effects_src = $GLOBALS['egw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/js/slidereffects.js';
			$slider_effects = '<script src="'.$slider_effects_src.'" type="text/javascript">
			</script>';
		}
		else
		{
			$simple_show_hide_src = $GLOBALS['egw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/js/simple_show_hide.js';
			$simple_show_hide = '<script src="'.$simple_show_hide_src.'" type="text/javascript">
			</script>';
		}

		if ($GLOBALS['egw_info']['flags']['app_header'])
		{
			$app = $GLOBALS['egw_info']['flags']['app_header'];
		}
		else
		{
			$app = $GLOBALS['egw_info']['flags']['currentapp'];
			$app = isset($GLOBALS['egw_info']['apps'][$app]) ? $GLOBALS['egw_info']['apps'][$app]['title'] : lang($app);
		}
		$var = array();
		if($app!='wiki') $robots ='<meta name="robots" content="none" />';
		if (substr($GLOBALS['egw_info']['server']['favicon_file'],0,4) == 'http')
		{
			$var['favicon_file'] = $GLOBALS['egw_info']['server']['favicon_file'];
		}
		else
		{
			$var['favicon_file'] = common::image('phpgwapi',$GLOBALS['egw_info']['server']['favicon_file']?$GLOBALS['egw_info']['server']['favicon_file']:'favicon.ico');
		}

		if ($GLOBALS['egw_info']['flags']['include_wz_tooltip'] &&
			file_exists(EGW_SERVER_ROOT.($wz_tooltip = '/phpgwapi/js/wz_tooltip/wz_tooltip.js')))
		{
			$include_wz_tooltip = '<script src="'.$GLOBALS['egw_info']['server']['webserver_url'].
				$wz_tooltip.'?'.filemtime(EGW_SERVER_ROOT.$wz_tooltip).'" type="text/javascript"></script>';
		}
		return $this->_get_css()+array(
			'img_icon'			=> $var['favicon_file'],
			'img_shortcut'		=> $var['favicon_file'],
			'pngfix'        	=> $pngfix,
			'slider_effects'	=> $slider_effects,
			'simple_show_hide'	=> $simple_show_hide,
			'lang_code'			=> $lang_code,
			'charset'       	=> translation::charset(),
			'website_title' 	=> strip_tags($GLOBALS['egw_info']['server']['site_title']. ($app ? " [$app]" : '')),
			'body_tags'     	=> self::_get_body_attribs(),
			'java_script'   	=> self::_get_js($extra),
			'meta_robots'		=> $robots,
			'dir_code'			=> lang('language_direction_rtl') != 'rtl' ? '' : ' dir="rtl"',
			'include_wz_tooltip'=> $include_wz_tooltip,
			'webserver_url'     => $GLOBALS['egw_info']['server']['webserver_url'],
		);
	}

	/**
	 * Get navbar as array to eg. set as vars for a template (from idots' navbar.inc.php)
	 *
	 * @param array $apps navbar apps from _get_navbar_apps
	 * @return array
	 */
	protected function _get_navbar($apps)
	{
		$var['img_root'] = $GLOBALS['egw_info']['server']['webserver_url'] . '/phpgwapi/templates/'.$this->template.'/images';

		if(isset($GLOBALS['egw_info']['flags']['app_header']))
		{
			$var['current_app_title'] = $GLOBALS['egw_info']['flags']['app_header'];
		}
		else
		{
			$var['current_app_title']=$apps[$GLOBALS['egw_info']['flags']['currentapp']]['title'];
		}
		$var['currentapp'] = $GLOBALS['egw_info']['flags']['currentapp'];

		// current users for admins
		$var['current_users'] = $this->_current_users();

		// quick add selectbox
		$var['quick_add'] = $this->_get_quick_add();

		$var['user_info'] = $this->_user_time_info();

		if($GLOBALS['egw_info']['user']['account_lastpwd_change'] == 0)
		{
			$api_messages = lang('You are required to change your password during your first login').'<br />'.
				lang('Click this image on the navbar: %1','<img src="'.common::image('preferences','navbar.gif').'">');
		}
		elseif($GLOBALS['egw_info']['server']['change_pwd_every_x_days'] && $GLOBALS['egw_info']['user']['account_lastpwd_change'] < time() - (86400*$GLOBALS['egw_info']['server']['change_pwd_every_x_days']))
		{
			$api_messages = lang('it has been more then %1 days since you changed your password',$GLOBALS['egw_info']['server']['change_pwd_every_x_days']);
		}

		// This is gonna change
		if(isset($cd))
		{
			$var['messages'] = $api_messages . '<br />' . checkcode($cd);
		}

		if (substr($GLOBALS['egw_info']['server']['login_logo_file'],0,4) == 'http' ||
			$GLOBALS['egw_info']['server']['login_logo_file'][0] == '/')
		{
			$var['logo_file'] = $GLOBALS['egw_info']['server']['login_logo_file'];
		}
		else
		{
			$var['logo_file'] = common::image('phpgwapi',$GLOBALS['egw_info']['server']['login_logo_file']?$GLOBALS['egw_info']['server']['login_logo_file']:'logo');
		}
		$var['logo_url'] = $GLOBALS['egw_info']['server']['login_logo_url']?$GLOBALS['egw_info']['server']['login_logo_url']:'http://www.eGroupWare.org';

		if (substr($var['logo_url'],0,4) != 'http')
		{
			$var['logo_url'] = 'http://'.$var['logo_url'];
		}
		$var['logo_title'] = $GLOBALS['egw_info']['server']['login_logo_title']?$GLOBALS['egw_info']['server']['login_logo_title']:'www.eGroupWare.org';

		return $var;
	}

	/**
	 * Returns html with user and time
	 *
	 * @return void
	 */
	protected static function _user_time_info()
	{
		$now = new egw_time();
		$user_info = '<b>'.common::display_fullname() .'</b>'. ' - ' . lang($now->format('l')) . ' ' . $now->format(true);

		$user_tzs = egw_time::getUserTimezones();
		if (count($user_tzs) > 1)
		{
			$tz = $GLOBALS['egw_info']['user']['preferences']['common']['tz'];
			$user_info .= html::form(html::select('tz',$tz,$user_tzs,true,' onchange="this.form.submit();"'),array(),
				'/index.php','','tz_selection',' style="display: inline;"','GET');
		}
		return $user_info;
	}

	/**
	 * Prepare the current users
	 *
	 * @return string
	 */
	protected static function _current_users()
	{
	   if( $GLOBALS['egw_info']['user']['apps']['admin'] && $GLOBALS['egw_info']['user']['preferences']['common']['show_currentusers'])
	   {
		  $current_users = '<a href="' . egw::link('/index.php','menuaction=admin.admin_accesslog.sessions') . '">' .
		  	lang('Current users') . ': <span id="currentusers">' . $GLOBALS['egw']->session->session_count() . '</span></a>';
		  return $current_users;
	   }
	}

	/**
	 * Prepare the quick add selectbox
	 *
	 * @return string
	 */
	protected static function _get_quick_add()
	{
		$apps = egw_link::app_list('add');
		asort($apps);	// sort them alphabetic

		$options = array(lang('Add').' ...');
		foreach($apps as $app => $label)
		{
			$link = egw::link('/index.php',egw_link::add($app,$GLOBALS['egw_info']['flags']['currentapp'],$GLOBALS['egw_info']['flags']['currentid'])+
				(is_array($GLOBALS['egw_info']['flags']['quick_add']) ? $GLOBALS['egw_info']['flags']['quick_add'] : array()));
			if (($popup = egw_link::is_popup($app,'add')))
			{
				list($w,$h) = explode('x',$popup);
				$action = "egw_openWindowCentered2('$link','_blank',$w,$h,'yes','$app');";
			}
			else
			{
				$action = "egw_link_handler('$link','$app');";
			}
			$options[$action] = $label;
		}
		return html::select('quick_add','',$options,true,$options=' onchange="eval(this.value); this.value=0; return false;"');
	}

	/**
	 * Prepare notification signal (blinking bell)
	 *
	 * @return string
	 */
	protected static function _get_notification_bell()
	{
		return html::div(
			html::a_href(
				html::image('notifications','notificationbell',lang('notifications')),
				'javascript: egwpopup_display();'
			),
			'id="notificationbell"', // options
			'', // class
			'display: none' //style
		);
	}

	/**
	 * Get the link to an application's index page
	 *
	 * @param string $app
	 * @return string
	 */
	public static function index($app)
	{
		$data =& $GLOBALS['egw_info']['user']['apps'][$app];
		if (!isset($data))
		{
			throw new egw_exception_wrong_parameter("'$app' not a valid app for this user!");
		}
		$index = '/'.$app.'/index.php';
		if (isset($data['index']))
		{
			if ($data['index'][0] == '/')
			{
				$index = $data['index'];
			}
			else
			{
				$index = '/index.php?menuaction='.$data['index'];
			}
		}
		return egw::link($index,$GLOBALS['egw_info']['flags']['params'][$app]);
	}

	/**
	 * Used internally to store unserialized value of $GLOBALS['egw_info']['user']['preferences']['common']['user_apporder']
	 */
	private static $user_apporder = array();

	/**
	 * Internal usort callback function used to sort an array according to the
	 * user sort order
	 */
	private static function _sort_apparray($a, $b)
	{
		//Unserialize the user_apporder array
		$arr = self::$user_apporder;

		$ind_a = isset($arr[$a['name']]) ? $arr[$a['name']] : null;
		$ind_b = isset($arr[$b['name']]) ? $arr[$b['name']] : null;

		if ($ind_a == $ind_b)
			return 0;

		if ($ind_a == null)
			return -1;

		if ($ind_b == null)
			return 1;

		return $ind_a > $ind_b ? 1 : -1;
	}

	/**
	 * Prepare an array with apps used to render the navbar
	 *
	 * This is similar to the former common::navbar() method - though it returns the vars and does not place them in global scope.
	 *
	 * @return array
	 */
	protected static function _get_navbar_apps()
	{
		list($first) = each($GLOBALS['egw_info']['user']['apps']);
		if(is_array($GLOBALS['egw_info']['user']['apps']['admin']) && $first != 'admin')
		{
			$newarray['admin'] = $GLOBALS['egw_info']['user']['apps']['admin'];
			foreach($GLOBALS['egw_info']['user']['apps'] as $index => $value)
			{
				if($index != 'admin')
				{
					$newarray[$index] = $value;
				}
			}
			$GLOBALS['egw_info']['user']['apps'] = $newarray;
			reset($GLOBALS['egw_info']['user']['apps']);
		}
		unset($index);
		unset($value);
		unset($newarray);

		$apps = array();
		foreach($GLOBALS['egw_info']['user']['apps'] as $app => $data)
		{
			if (is_long($app))
			{
				continue;
			}

			if ($app == 'preferences' || $GLOBALS['egw_info']['apps'][$app]['status'] != 2 && $GLOBALS['egw_info']['apps'][$app]['status'] != 3)
			{
				$apps[$app]['title'] = $GLOBALS['egw_info']['apps'][$app]['title'];
				$apps[$app]['url']   = self::index($app);
				$apps[$app]['name']  = $app;

				// create popup target
				if ($data['status'] == 4)
				{
					$apps[$app]['target'] = ' target="'.$app.'" onClick="'."if (this != '') { window.open(this+'".
						(strpos($apps[$app]['url'],'?') !== false ? '&' : '?').
						"referer='+encodeURIComponent(location),this.target,'width=800,height=600,scrollbars=yes,resizable=yes'); return false; } else { return true; }".'"';
				}
				elseif(isset($GLOBALS['egw_info']['flags']['navbar_target']) && $GLOBALS['egw_info']['flags']['navbar_target'])
				{
					$apps[$app]['target'] = 'target="' . $GLOBALS['egw_info']['flags']['navbar_target'] . '"';
				}
				else
				{
					$apps[$app]['target'] = '';
				}

				$icon = isset($data['icon']) ?  $data['icon'] : 'navbar';
				$icon_app = isset($data['icon_app']) ? $data['icon_app'] : $app;
				if ($app != $GLOBALS['egw_info']['flags']['currentapp'])
				{
					$apps[$app]['icon']  = common::image($icon_app,Array($icon,'nonav'));
					$apps[$app]['icon_hover']  = common::image_on($icon_app,Array($icon,'nonav'),'-over');
				}
				else
				{
					$apps[$app]['icon']  = common::image_on($icon_app,Array($icon,'nonav'),'-over');
					$apps[$app]['icon_hover']  = common::image($icon_app,Array($icon,'nonav'));
				}
			}
		}

		//Sort the applications accordingly to their user sort setting
		if ($GLOBALS['egw_info']['user']['preferences']['common']['user_apporder'])
		{
			//Sort the application array using the user_apporder array as sort index
			self::$user_apporder =
				unserialize($GLOBALS['egw_info']['user']['preferences']['common']['user_apporder']);
			uasort($apps, 'egw_framework::_sort_apparray');
		}

		if ($GLOBALS['egw_info']['flags']['currentapp'] == 'preferences' || $GLOBALS['egw_info']['flags']['currentapp'] == 'about')
		{
			$app = $app_title = 'EGroupware';
		}
		else
		{
			$app = $GLOBALS['egw_info']['flags']['currentapp'];
			$app_title = $GLOBALS['egw_info']['apps'][$app]['title'];
		}

		if ($GLOBALS['egw_info']['user']['apps']['preferences'])	// preferences last
		{
			$prefs = $apps['preferences'];
			unset($apps['preferences']);
			$apps['preferences'] = $prefs;
		}

		// We handle this here becuase its special
		$apps['about']['title'] = 'EGroupware';

		$apps['about']['url']   = egw::link('/about.php');
		$apps['about']['icon']  = common::image('phpgwapi',Array('about','nonav'));
		$apps['about']['icon_hover']  = common::image_on('phpgwapi',Array('about','nonav'),'-over');
		$apps['about']['name'] = 'about';

		$apps['logout']['title'] = lang('Logout');
		$apps['logout']['url']   = egw::link('/logout.php');
		$apps['logout']['icon']  = common::image('phpgwapi',Array('logout','nonav'));
		$apps['logout']['icon_hover']  = common::image_on('phpgwapi',Array('logout','nonav'),'-over');

		return $apps;
	}

	/**
	 * Used by template headers for including CSS in the header
	 *
	 * 'app_css'   - css styles from a) the menuaction's css-method and b) the $GLOBALS['egw_info']['flags']['css']
	 * 'file_css'  - link tag of the app.css file of the current app
	 * 'theme_css' - url of the theme css file
	 * 'print_css' - url of the print css file
	 *
	 * @author Dave Hall (*based* on verdilak? css inclusion code)
	 * @return array with keys 'app_css' from the css method of the menuaction-class and 'file_css' (app.css file of the application)
	 */
	public function _get_css()
	{
		$app_css = '';
		if(isset($_GET['menuaction']))
		{
			list($app,$class,$method) = explode('.',$_GET['menuaction']);
			if(is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions['css'])
			{
				error_log("Deprecated functionality in $app class $class: using of public_function css, use \$GLOBALS['egw_info']['flags']['css'] or an app.css file!");
				$app_css .= $GLOBALS[$class]->css();
			}
		}
		if (isset($GLOBALS['egw_info']['flags']['css']))
		{
			$app_css .= $GLOBALS['egw_info']['flags']['css'];
		}

		$theme_css = $this->template_dir.'/css/'.$GLOBALS['egw_info']['user']['preferences']['common']['theme'].'.css';
		if(!file_exists(EGW_SERVER_ROOT.$theme_css))
		{
			$theme_css = $this->template_dir.'/css/'.$this->template.'.css';
		}
		$print_css = $this->template_dir.'/print.css';
		if(!file_exists(EGW_SERVER_ROOT.$print_css))
		{
			$print_css = '/phpgwapi/templates/idots/print.css';
		}
		self::includeCSS($print_css, null, false);	// false = prepend (add as first) file
		self::includeCSS($theme_css, null, false);

		// Enhanced selectboxes (et1)
		self::includeCSS('/phpgwapi/js/jquery/chosen/chosen.css');

		// eTemplate2 - load in top so sidebox has styles too
		self::includeCSS('/etemplate/templates/default/etemplate2.css');

		// search for app specific css file
		self::includeCSS($GLOBALS['egw_info']['flags']['currentapp'], 'app');

		// add all css files from self::includeCSS
		$max_modified = 0;
		$debug_minify = (bool)$GLOBALS['egw_info']['server']['debug_minify'];
		$base_path = $GLOBALS['egw_info']['server']['webserver_url'];
		if ($base_path[0] != '/') $base_path = parse_url($base_path, PHP_URL_PATH);
		$css_file = '';
		foreach(self::$css_include_files as $n => $path)
		{
			foreach(self::resolve_css_includes($path) as $path)
			{
				if (($mod = filemtime(EGW_SERVER_ROOT.$path)) > $max_modified) $max_modified = $mod;

				if ($debug_minify)
				{
					$css_file .= '<link href="'.$GLOBALS['egw_info']['server']['webserver_url'].$path.'?'.$mod.'" type="text/css" rel="StyleSheet" />'."\n";
				}
				else
				{
					$css_file .= ($css_file ? ',' : '').substr($path, 1);
				}
			}
		}
		if (!$debug_minify)
		{
			$css = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/inc/min/?';
			if ($base_path && $base_path != '/') $css .= 'b='.substr($base_path, 1).'&';
			$css .= 'f='.$css_file . '&'.$max_modified;
			$css_file = '<link href="'.$css.'" type="text/css" rel="StyleSheet" />'."\n";
		}
		return array(
			'app_css'   => $app_css,
			'css_file'  => $css_file,
		);
	}

	/**
	 * Parse beginning of given CSS file for /*@import url("...") statements
	 *
	 * @param string $path EGroupware relative path eg. /phpgwapi/templates/default/some.css
	 * @return array parsed pathes (EGroupware relative) including $path itself
	 */
	protected static function resolve_css_includes($path, &$pathes=array())
	{
		if (($to_check = file_get_contents (EGW_SERVER_ROOT.$path, false, null, -1, 1024)) &&
			stripos($to_check, '/*@import') !== false && preg_match_all('|/\*@import url\("([^"]+)"|i', $to_check, $matches))
		{
			foreach($matches[1] as $import_path)
			{
				if ($import_path[0] != '/')
				{
					$dir = dirname($path);
					while(substr($import_path,0,3) == '../')
					{
						$dir = dirname($dir);
						$import_path = substr($import_path, 3);
					}
					$import_path = ($dir != '/' ? $dir : '').'/'.$import_path;
				}
				self::resolve_css_includes($import_path, $pathes);
			}
		}
		$pathes[] = $path;

		return $pathes;
	}

	/**
	 * Used by the template headers for including javascript in the header
	 *
	 * The method is included here to make it easier to change the js support
	 * in eGW.  One change then all templates will support it (as long as they
	 * include a call to this method).
	 *
	 * @param array $extra=array() extra data to pass to egw.js as data-parameter
	 * @return string the javascript to be included
	 */
	public static function _get_js(array $extra=array())
	{
		$java_script = '';

		// GLOBAL var to tell egroupware wether or not to enable the IE selectBox resize hack
		if($GLOBALS['egw_info']['user']['preferences']['common']['enable_ie_dropdownmenuhack'] && html::$user_agent == 'msie' && html::$ua_version < 9)
		{
			$java_script .= "<script type=\"text/javascript\">\nvar enable_ie_dropdownmenuhack=1;\n</script>\n";
		}

		/* this flag is for all javascript code that has to be put before other jscode.
		Think of conf vars etc...  (pim@lingewoud.nl) */
		if (isset($GLOBALS['egw_info']['flags']['java_script_thirst']))
		{
			$java_script .= $GLOBALS['egw_info']['flags']['java_script_thirst'] . "\n";
		}
		// add configuration, link-registry, images, user-data and -perferences for non-popup windows
		if ($GLOBALS['egw_info']['flags']['js_link_registry'])
		{
			self::validate_file('/phpgwapi/config.php');
			self::validate_file('/phpgwapi/images.php',array('template' => $GLOBALS['egw_info']['user']['preferences']['common']['template_set']));
			self::validate_file('/phpgwapi/user.php',array('user' => $GLOBALS['egw_info']['user']['account_lid']));
		}

		$extra['url'] = $GLOBALS['egw_info']['server']['webserver_url'];
		$extra['include'] = array_map(function($str){return substr($str,1);}, self::get_script_links(true), array(1));
		$extra['app'] = $GLOBALS['egw_info']['flags']['currentapp'];

		// Load LABjs ONCE here
		$java_script .= '<script type="text/javascript" src="'. $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/labjs/LAB.src.js"'." ></script>\n".
			'<script type="text/javascript" src="'. $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/jsapi/egw.js" id="egw_script_id"';
		foreach($extra as $name => $value)
		{
			if (is_array($value)) $value = json_encode($value);
			$java_script .= ' data-'.$name."='".str_replace("'", '\\\'', $value)."'";
		}
		$java_script .= "></script>\n";

		if(@isset($_GET['menuaction']))
		{
			list($app,$class,$method) = explode('.',$_GET['menuaction']);
			if(is_array($GLOBALS[$class]->public_functions) &&
				$GLOBALS[$class]->public_functions['java_script'])
			{
				$java_script .= $GLOBALS[$class]->java_script();
			}
		}
		if (isset($GLOBALS['egw_info']['flags']['java_script']))
		{
			// Strip out any script tags, this needs to be executed as anonymous function
			$GLOBALS['egw_info']['flags']['java_script'] = preg_replace(array('/(<script[^>]*>)([^<]*)/is','/<\/script>/'),array('$2',''),$GLOBALS['egw_info']['flags']['java_script']);
			if(trim($GLOBALS['egw_info']['flags']['java_script']) != '')
			{
				$java_script .= '<script type="text/javascript">window.egw_LAB.wait(function() {'.$GLOBALS['egw_info']['flags']['java_script'] . "});</script>\n";
			}
		}

		return $java_script;
	}

	/**
	 * List available themes
	 *
	 * Themes are css file in the template directory
	 *
	 * @param string $themes_dir='css'
	 */
	function list_themes()
	{
		$list = array();
		if (($dh = @opendir(EGW_SERVER_ROOT.$this->template_dir . SEP . 'css')))
		{
			while (($file = readdir($dh)))
			{
				if (preg_match('/'."\.css$".'/i', $file))
				{
					list($name) = explode('.',$file);
					$list[$name] = $name;
				}
			}
			closedir($dh);
		}
		return $list;
	}

	/**
	 * List available templates
	 *
	 * @param boolean $full_data=false true: value is array with values for keys 'name', 'title', ...
	 * @returns array alphabetically sorted list of templates
	 */
	static function list_templates($full_data=false)
	{
		$list = array();
		// templates packaged in the api
		$d = dir(EGW_SERVER_ROOT . '/phpgwapi/templates');
		while (($entry=$d->read()))
		{
			if ($entry != '..' && file_exists(EGW_SERVER_ROOT . '/phpgwapi/templates/' . $entry .'/class.'.$entry.'_framework.inc.php'))
			{
				if (file_exists ($f = EGW_SERVER_ROOT . '/phpgwapi/templates/' . $entry . '/setup/setup.inc.php'))
				{
					include($f);
					$list[$entry] = $full_data ? $GLOBALS['egw_info']['template'][$entry] :
						$GLOBALS['egw_info']['template'][$entry]['title'];
				}
				else
				{
					$list[$entry] = $full_data ? array(
						'name'  => $entry,
						'title' => $entry,
					) : $entry;
				}
			}
		}
		$d->close();
		// templates packaged like apps in own directories (containing as setup/setup.inc.php file!)
		$d = dir(EGW_SERVER_ROOT);
		while (($entry=$d->read()))
		{
			if ($entry != '..' && !isset($GLOBALS['egw_info']['apps'][$entry]) && is_dir(EGW_SERVER_ROOT.'/'.$entry) &&
				file_exists($f = EGW_SERVER_ROOT . '/' . $entry .'/setup/setup.inc.php'))
			{
				include($f);
				if (isset($GLOBALS['egw_info']['template'][$entry]))
				{
					$list[$entry] = $full_data ? $GLOBALS['egw_info']['template'][$entry] :
						$GLOBALS['egw_info']['template'][$entry]['title'];
				}
			}
		}
		$d->close();
		ksort($list);

		return $list;
	}

	/**
	* Compile entries for topmenu:
	* - regular items: links
	* - info items
	*
	* @param array $vars
	* @param array $apps
	*/
	function topmenu(array $vars,array $apps)
	{
		if($GLOBALS['egw_info']['user']['apps']['home'] && isset($apps['home']))
		{
			$this->_add_topmenu_item($apps['home']);
		}
		if($GLOBALS['egw_info']['user']['apps']['preferences'])
		{
			$this->_add_topmenu_item($apps['preferences']);
		}
		// allways display password in topmenu, if user has rights to change it
		if((($pw_app = $GLOBALS['egw_info']['user']['apps']['preferences']) ||
			($pw_app = $GLOBALS['egw_info']['user']['apps']['password'])) &&
			!$GLOBALS['egw']->acl->check('nopasswordchange', 1, 'preferences'))
		{
			$this->_add_topmenu_item(array(
				'name'  => $pw_app['name'] == 'password' ? 'about' : $pw_app['name'],
				'title' => lang('Password'),
				'url'   => egw::link($pw_app['name'] == 'password' ? $pw_app['index'] : '/index.php?menuaction=preferences.uipassword.change'),
				'icon'  => common::image($pw_app['icon'],$pw_app['icon_app']),
			));
		}

		if($GLOBALS['egw_info']['user']['apps']['manual'] && isset($apps['manual']))
		{
			$this->_add_topmenu_item(array_merge($apps['manual'],array('title' => lang('Help'))));
		}

		$GLOBALS['egw']->hooks->process('topmenu_info',array(),true);
		// Add extra items added by hooks
		foreach(self::$top_menu_extra as $extra_item) {
			$this->_add_topmenu_item($extra_item);
		}

		$this->_add_topmenu_item($apps['logout']);

		if($GLOBALS['egw_info']['user']['apps']['notifications'])
		{
			$this->_add_topmenu_info_item(self::_get_notification_bell());
		}
		$this->_add_topmenu_info_item($vars['user_info']);
		$this->_add_topmenu_info_item($vars['current_users']);
		$this->_add_topmenu_info_item($vars['quick_add']);
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
	abstract function _add_topmenu_item(array $app_data,$alt_label=null);

	/**
	* Add info items to the topmenu template class to be displayed
	*
	* @param string $content html of item
	* @access protected
	* @return void
	*/
	abstract function _add_topmenu_info_item($content);

	static $top_menu_extra = array();

	/**
	* Called by hooks to add an entry in the topmenu location.
	* Extra entries will be added just before Logout.
	*
	* @param string $id unique element id
	* @param string $url Address for the entry to link to
	* @param string $title Text displayed for the entry
	* @param string $target Optional, so the entry can open in a new page or popup
	* @access public
	* @return void
	*/
	public static function add_topmenu_item($id,$url,$title,$target = '')
	{
		$entry['name'] = $id;
		$entry['url'] = $url;
		$entry['title'] = $title;
		$entry['target'] = $target;

		self::$top_menu_extra[$id] = $entry;
	}

	/**
	* called by hooks to add an icon in the topmenu info location
	*
	* @param string $id unique element id
	* @param string $icon_src src of the icon image. Make sure this nog height then 18pixels
	* @param string $iconlink where the icon links to
	* @param booleon $blink set true to make the icon blink
	* @param mixed $tooltip string containing the tooltip html, or null of no tooltip
	* @access public
	* @return void
	*/
	abstract function topmenu_info_icon($id,$icon_src,$iconlink,$blink=false,$tooltip=null);

	/**
	 * Call and return content of 'after_navbar' hook
	 *
	 * @return string
	 */
	protected function _get_after_navbar()
	{
		ob_start();
		$GLOBALS['egw']->hooks->process('after_navbar',null,true);
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Return javascript (eg. for onClick) to open manual with given url
	 *
	 * @param string $url
	 */
	abstract function open_manual_js($url);

	/**
	 * Methods to add javascript to framework
	 */

	/**
	 * Body tags for onLoad, onUnload and onResize
	 *
	 * @var array
	 */
	protected static $body_tags = array();

	/**
	 * Sets an onLoad action for a page
	 *
	 * @param string $code='' javascript to be used
	 * @param boolean $replace=false false: append to existing, true: replace existing tag
	 * @return string content of onXXX tag after adding code
	 */
	static function set_onload($code='',$replace=false)
	{
		if ($replace || empty(self::$body_tags['onLoad']))
		{
			self::$body_tags['onLoad'] = $code;
		}
		else
		{
			self::$body_tags['onLoad'] .= $code;
		}
		return self::$body_tags['onLoad'];
	}

	/**
	 * Sets an onUnload action for a page
	 *
	 * @param string $code='' javascript to be used
	 * @param boolean $replace=false false: append to existing, true: replace existing tag
	 * @return string content of onXXX tag after adding code
	 */
	static function set_onunload($code='',$replace=false)
	{
		if ($replace || empty(self::$body_tags['onUnload']))
		{
			self::$body_tags['onUnload'] = $code;
		}
		else
		{
			self::$body_tags['onUnload'] .= $code;
		}
		return self::$body_tags['onUnload'];
	}

	/**
	 * Sets an onBeforeUnload action for a page
	 *
	 * @param string $code='' javascript to be used
	 * @param boolean $replace=false false: append to existing, true: replace existing tag
	 * @return string content of onXXX tag after adding code
	 */
	static function set_onbeforeunload($code='',$replace=false)
	{
		if ($replace || empty(self::$body_tags['onBeforeUnload']))
		{
			self::$body_tags['onBeforeUnload'] = $code;
		}
		else
		{
			self::$body_tags['onBeforeUnload'] .= $code;
		}
		return self::$body_tags['onBeforeUnload'];
	}

	/**
	* Sets an onResize action for a page
	*
	* @param string $code='' javascript to be used
	* @param boolean $replace=false false: append to existing, true: replace existing tag
	* @return string content of onXXX tag after adding code
	*/
	static function set_onresize($code='',$replace=false)
	{
		if ($replace || empty(self::$body_tags['onResize']))
		{
			self::$body_tags['onResize'] = $code;
		}
		else
		{
			self::$body_tags['onResize'] .= $code;
		}
		return self::$body_tags['onResize'];
	}

	/**
	 * Adds on(Un)Load= attributes to the body tag of a page
	 *
	 * @returns string the attributes to be used
	 */
	static public function _get_body_attribs()
	{
		$js = '';
		foreach(self::$body_tags as $what => $data)
		{
			if (!empty($data))
			{
				if($what == 'onLoad')
				{
					$js .= 'onLoad="egw_LAB.wait(function() {'. htmlspecialchars($data).'})"';
					continue;
				}
				$js .= ' '.$what.'="' . htmlspecialchars($data) . '"';
			}
		}
		return $js;
	}

	/**
	 * The include manager manages including js files and their dependencies
	 */
	protected static $js_include_mgr;

	/**
	* Checks to make sure a valid package and file name is provided
	*
	* Example call syntax:
	* a) egw_framework::validate_file('jscalendar','calendar')
	*    --> /phpgwapi/js/jscalendar/calendar.js
	* b) egw_framework::validate_file('/phpgwapi/inc/calendar-setup.js',array('lang'=>'de'))
	*    --> /phpgwapi/inc/calendar-setup.js?lang=de
	*
	* @param string $package package or complete path (relative to EGW_SERVER_ROOT) to be included
	* @param string|array $file=null file to be included - no ".js" on the end or array with get params
	* @param string $app='phpgwapi' application directory to search - default = phpgwapi
	* @param boolean $append=true should the file be added
	*
	* @discuss The browser specific option loads the file which is in the correct
	*          browser folder. Supported folder are those supported by class.browser.inc.php
	*
	* @returns bool was the file found?
	*/
	static function validate_file($package, $file=null, $app='phpgwapi')
	{
		self::$js_include_mgr->include_js_file($package, $file, $app);
	}

	/**
	 * Set or return all javascript files set via validate_file, optionally clear all files
	 *
	 * @param array $files=null array with pathes relative to EGW_SERVER_ROOT, eg. /phpgwapi/js/jquery/jquery.js
	 * @param boolean $clear_files=false true clear files after returning them
	 * @return array with pathes relative to EGW_SERVER_ROOT
	 */
	static function js_files(array $files=null, $clear_files=false)
	{
		if (isset($files) && is_array($files))
		{
			self::$js_include_mgr->include_files($files);
		}
		return self::$js_include_mgr->get_included_files($clear_files);
	}

	/**
	 * Used for generating the list of external js files to be included in the head of a page
	 *
	 * NOTE: This method should only be called by the template class.
	 * The validation is done when the file is added so we don't have to worry now
	 *
	 * @param boolean $return_pathes=false false: return html script tags, true: return array of file pathes relative to webserver_url
	 * @param boolean $clear_files=false true clear files after returning them
	 * @return string|array see $return_pathes parameter
	 */
	static public function get_script_links($return_pathes=false, $clear_files=false)
	{
		$debug_minify = (bool)$GLOBALS['egw_info']['server']['debug_minify'];
		$files = '';
		$to_include = $to_minify = array();
		$max_modified = 0;
		foreach(self::$js_include_mgr->get_included_files($clear_files) as $path)
		{
			$query = '';
			list($path,$query) = explode('?',$path,2);
			if (($mod = filemtime(EGW_SERVER_ROOT.$path)) > $max_modified) $max_modified = $mod;

			// for now minify does NOT support query parameters, nor php files generating javascript
			if ($debug_minify || $query || substr($path, -3) != '.js' || strpos($path,'ckeditor') !== false)
			{
				$path .= '?'. $mod.($query ? '&'.$query : '');
				$to_include[] = $path;
			}
			else
			{
				$to_minify[] = substr($path,1);
			}
		}
		if (!$debug_minify && $to_minify)
		{
			$base_path = $GLOBALS['egw_info']['server']['webserver_url'];
			if ($base_path[0] != '/') $base_path = parse_url($base_path, PHP_URL_PATH);
			$path = '/phpgwapi/inc/min/?'.($base_path && $base_path != '/' ? 'b='.substr($base_path, 1).'&' : '').
				'f='.implode(',', $to_minify) . '&'.$max_modified;
			// need to include minified javascript before not minified stuff like jscalendar-setup, as it might depend on it
			array_unshift($to_include, $path);
		}
		if ($return_pathes)
		{
			return $to_include;
		}
		$start = '<script type="text/javascript" src="'. $GLOBALS['egw_info']['server']['webserver_url'];
		$end = '">'."</script>\n";
		return "\n".$start.implode($end.$start, $to_include).$end;

		// using LABjs to load all javascript would require all other script-tags to run in wait() of queue!
		/*
		return "\n".$start.'/phpgwapi/js/labjs/LAB.src.js'.$end."\n".
			'<script type="text/javascript">
$LAB.setOptions({AlwaysPreserveOrder:true,BasePath:"'.$GLOBALS['egw_info']['server']['webserver_url'].'/"}).script(
'.json_encode(array_map(function($str){return substr($str,1);}, $to_include, array(1))).').wait();
</script>
';
		*/
	}

	/**
	 * Content from includeCSS calls
	 *
	 * @var array
	 */
	protected static $css_include_files = array();

	/**
	 * Include a css file, either speicified by it's path (relative to EGW_SERVER_ROOT) or appname and css file name
	 *
	 * @param string $app path (relative to EGW_SERVER_ROOT) or appname (if !is_null($name))
	 * @param string $name=null name of css file in $app/templates/{default|$this->template}/$name.css
	 * @param boolean $append=true true append file, false prepend (add as first) file used eg. for template itself
	 * @return boolean false: css file not found, true: file found
	 */
	public static function includeCSS($app, $name=null, $append=true)
	{
		if (!is_null($name))
		{
			$path = '/'.$app.'/templates/'.$GLOBALS['egw_info']['server']['template_set'].'/'.$name.'.css';
			if (!file_exists(EGW_SERVER_ROOT.$path))
			{
				$path = '/'.$app.'/templates/default/'.$name.'.css';
			}
		}
		else
		{
			$path = $app;
		}
		if (!file_exists(EGW_SERVER_ROOT.$path))
		{
			//error_log(__METHOD__."($app,$name) $path NOT found!");
			return false;
		}
		if (!in_array($path,self::$css_include_files))
		{
			if ($append)
			{
				self::$css_include_files[] = $path;
			}
			else
			{
				self::$css_include_files = array_merge(array($path), self::$css_include_files);
			}
		}
		return true;
	}

	/**
	 * Add registered CSS and javascript to ajax response
	 */
	public static function include_css_js_response()
	{
		$response = egw_json_response::get();
		$app = $GLOBALS['egw_info']['flags']['currentapp'];

		// try to add app specific css file
		self::includeCSS($app,'app');

		// add all css files from egw_framework::includeCSS()
		foreach(self::$css_include_files as $path)
		{
			$query = '';
			list($path,$query) = explode('?',$path,2);
			$path .= '?'. filemtime(EGW_SERVER_ROOT.$path).($query ? '&'.$query : '');
			$response->includeCSS($GLOBALS['egw_info']['server']['webserver_url'].$path);
		}

		// try to add app specific js file
		self::validate_file('.', 'app', $app);

		// add all js files from egw_framework::validate_file()
		$files = self::$js_include_mgr->get_included_files();
		foreach($files as $path)
		{
			$query = '';
			list($path,$query) = explode('?',$path,2);
			$path .= '?'. filemtime(EGW_SERVER_ROOT.$path).($query ? '&'.$query : '');
			$response->includeScript($GLOBALS['egw_info']['server']['webserver_url'].$path);
		}
	}

	/**
	 * Set a preference via ajax
	 *
	 * User either need run rights for preference app, or setting of preference will be silently ignored!
	 *
	 * @param string $app
	 * @param string $name
	 * @param string $value
	 */
	public static function ajax_set_preference($app, $name, $value)
	{
		//error_log(__METHOD__."('$app', '$name', '$value')");
		if ($GLOBALS['egw_info']['user']['apps']['preferences'])
		{
			$GLOBALS['egw']->preferences->read_repository();
			$GLOBALS['egw']->preferences->change($app, $name, $value);
			$GLOBALS['egw']->preferences->save_repository(True);
		}
	}

	/**
	 * Get preferences of a certain application via ajax
	 *
	 * @param string $app
	 */
	public static function ajax_get_preference($app)
	{
		if (preg_match('/^[a-z0-9_]+$/i', $app))
		{
			$response = egw_json_response::get();
			$pref = $GLOBALS['egw_info']['user']['preferences'][$app];
			if(!$pref) $pref = Array();
			$response->script('window.egw.set_preferences('.json_encode($pref).', "'.$app.'");');
		}
	}
}

// Init all static variables
egw_framework::init_static();

/**
 * Public functions to be compatible with the exiting eGW framework
 */
if (!function_exists('parse_navbar'))
{
	/**
	 * echo's out the navbar
	 *
	 * @deprecated use $GLOBALS['egw']->framework->navbar() or $GLOBALS['egw']->framework::render()
	 */
	function parse_navbar()
	{
		echo $GLOBALS['egw']->framework->navbar();
	}
}

if (!function_exists('display_sidebox'))
{
	/**
	 * echo's out a sidebox menu
	 *
	 * @deprecated use $GLOBALS['egw']->framework->sidebox()
	 */
	function display_sidebox($appname,$menu_title,$file)
	{
		$file = str_replace('preferences.uisettings.index', 'preferences.preferences_settings.index', $file);
		$GLOBALS['egw']->framework->sidebox($appname,$menu_title,$file);
	}
 }
