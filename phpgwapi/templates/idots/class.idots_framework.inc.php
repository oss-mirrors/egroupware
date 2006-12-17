<?php
/**
 * eGW idots template
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

require_once(EGW_API_INC.'/class.egw_framework.inc.php');
require_once(EGW_API_INC.'/class.Template.inc.php');

/**
 * eGW idots template
 * 
 * The idots_framework class draws the default idots template. It's a phplib template based template-set.
 * 
 * Other phplib template based template-sets should extend (not copy!) this class and reimplement methods they which to change.
 */
class idots_framework extends egw_framework
{
	/**
	 * HTML of the sidebox menu, get's collected here by calls to $this->sidebox
	 *
	 * @var string
	 */
	var $sidebox_content = '';
	/**
	 * Instance of the phplib Template class for the API's template dir (EGW_TEMPLATE_DIR)
	 *
	 * @var Template
	 */
	var $tpl;

	/**
	 * Constructor
	 *
	 * @param string $template='idots' name of the template
	 * @return idots_framework
	 */
	function idots_framework($template='idots')
	{
		$this->egw_framework($template);		// call the constructor of the extended class
	}

	/**
	 * Returns the html-header incl. the opening body tag
	 *
	 * @return string with html
	 */
	function header()
	{
		// add a content-type header to overwrite an existing default charset in apache (AddDefaultCharset directiv)
		header('Content-type: text/html; charset='.$GLOBALS['egw']->translation->charset());
		
		// catch error echo'ed before the header, ob_start'ed in the header.inc.php
		$content = ob_get_contents();
		ob_end_clean();

		// the instanciation of the template has to be here and not in the constructor,
		// as the old Template class has problems if restored from the session (php-restore)
		$this->tpl =& new Template(EGW_TEMPLATE_DIR);
		$this->tpl->set_file(array('_head' => 'head.tpl'));
		$this->tpl->set_block('_head','head');
		
		$this->tpl->set_var($this->_get_header());

		$content .= $this->tpl->fp('out','head');
		
		$this->sidebox_content = '';	// need to be emptied here, as the object get's stored in the session 
		
		return $content;
	}
	
	/**
	 * Returns the html from the body-tag til the main application area (incl. opening div tag)
	 *
	 * @return string with html
	 */
	function navbar()
	{
		// the navbar
		$this->tpl->set_file(array('navbar' => 'navbar.tpl'));
			
		$this->tpl->set_block('navbar','extra_blocks_header','extra_block_header');
		$this->tpl->set_block('navbar','extra_block_row','extra_block_row');
		$this->tpl->set_block('navbar','extra_block_row_raw','extra_block_row_raw');
		$this->tpl->set_block('navbar','extra_block_row_no_link','extra_block_row_no_link');
		$this->tpl->set_block('navbar','extra_block_spacer','extra_block_spacer');
		$this->tpl->set_block('navbar','extra_blocks_footer','extra_blocks_footer');
		$this->tpl->set_block('navbar','sidebox_hide_header','sidebox_hide_header');
		$this->tpl->set_block('navbar','sidebox_hide_footer','sidebox_hide_footer');
		$this->tpl->set_block('navbar','appbox','appbox');
		$this->tpl->set_block('navbar','navbar_footer','navbar_footer');
		
		$this->tpl->set_block('navbar','upper_tab_block','upper_tabs');
		$this->tpl->set_block('navbar','app_icon_block','app_icons');
		$this->tpl->set_block('navbar','app_title_block','app_titles');
		$this->tpl->set_block('navbar','app_extra_block','app_extra_icons');
		$this->tpl->set_block('navbar','app_extra_icons_div');
		$this->tpl->set_block('navbar','app_extra_icons_icon');

		$this->tpl->set_block('navbar','navbar_header','navbar_header');

		$apps = $this->_get_navbar_apps();
		$vars = $this->_get_navbar($apps);
	
		$this->tpl->set_var($vars);
		$content = $this->tpl->fp('out','navbar_header');

		// general (app-unspecific) sidebox menu
		$menu_title = lang('General Menu');

		$file['Home'] = $apps['home']['url'];
		if($GLOBALS['egw_info']['user']['apps']['preferences'])
		{
			$file['Preferences'] = $apps['preferences']['url'];
		}
		$file += array(
			array(
				'text'    => lang('About %1',$GLOBALS['egw_info']['apps'][$GLOBALS['egw_info']['flags']['currentapp']]['title']),
				'no_lang' => True,
				'link'    => $apps['about']['url']
			),
			$GLOBALS['egw_info']['user']['userid'] != 'anonymous' ? 'Logout' : 'Login' =>$apps['logout']['url']
		);
		$this->sidebox('',$menu_title,$file);
		$GLOBALS['egw']->hooks->single('sidebox_menu',$GLOBALS['egw_info']['flags']['currentapp']);

		if($GLOBALS['egw_info']['user']['preferences']['common']['auto_hide_sidebox'])
		{
			$this->tpl->set_var('lang_show_menu',lang('show menu'));
			$content .= $this->tpl->parse('out','sidebox_hide_header');

			$content .= $this->sidebox_content;	// content from calls to $this->sidebox
			
			$content .= $this->tpl->parse('out','sidebox_hide_footer');

			$var['sideboxcolstart']='';

			$this->tpl->set_var($var);
			$content .= $this->tpl->parse('out','appbox');
			$var['remove_padding'] = 'style="padding-left:0px;"';
			$var['sideboxcolend'] = '';
		}
		else
		{
			$var['menu_link'] = '';
			$var['sideboxcolstart'] = '<td id="tdSidebox" valign="top">';
			$var['remove_padding'] = '';
			$this->tpl->set_var($var);
			$content .= $this->tpl->parse('out','appbox');

			$content .= $this->sidebox_content;
			
			$var['sideboxcolend'] = '</td>';
		}

		$this->tpl->set_var($var);
		$content .= $this->tpl->parse('out','navbar_footer');

		// depricated (!) application header, if not disabled
		// ToDo: check if it can be removed
		if(!@$GLOBALS['egw_info']['flags']['noappheader'] && @isset($_GET['menuaction']))
		{
			list($app,$class,$method) = explode('.',$_GET['menuaction']);
			if(is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions['header'])
			{
				ob_start();
				$GLOBALS[$class]->header();
				$content .= ob_get_contents();
				ob_end_clean();
			}
		}
		
		// hook after navbar
		// ToDo: change it to return the content!
		ob_start();
		$GLOBALS['egw']->hooks->process('after_navbar');
		$content .= ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	/**
	 * Get navbar as array to eg. set as vars for a template (from idots' navbar.inc.php)
	 * 
	 * Reimplemented so set the vars for the navbar itself (uses $this->tpl and the blocks a and b)
	 *
	 * @internal PHP5 protected
	 * @param array $apps navbar apps from _get_navbar_apps
	 * @return array
	 */
	function _get_navbar($apps)
	{
		$var = parent::_get_navbar($apps);

		if($GLOBALS['egw_info']['user']['preferences']['common']['click_or_onmouseover'] == 'onmouseover')
		{
			$var['show_menu_event'] = 'onMouseOver';
		}
		else
		{
			$var['show_menu_event'] = 'onClick';
		}

		if($GLOBALS['egw_info']['user']['userid'] == 'anonymous')
		{
			$cnf_reg =& CreateObject('phpgwapi.config','registration');
			$cnf_reg->read_repository();
			$config_reg = $cnf_reg->config_data;
			unset($cnf_reg);
		
			$this->tpl->set_var(array(
				'url'   => $GLOBALS['egw']->link('/logout.php'),
				'title' => lang('Login'),
			));
			$this->tpl->fp('upper_tabs','upper_tab_block');
			if ($config_reg[enable_registration]=='True' && $config_reg[register_link]=='True')
			{
				$this->tpl->set_var(array(
					'url'   => $GLOBALS['egw']->link('/registration/index.php'),
					'title' => lang('Register'),
				));
			}
		}

		if (!($max_icons=$GLOBALS['egw_info']['user']['preferences']['common']['max_icons']))
		{
			$max_icons = 30;
		}

		if($GLOBALS['egw_info']['user']['preferences']['common']['start_and_logout_icons'] == 'no')
		{
			$tdwidth = 100 / $max_icons;
		}
		else
		{
			$tdwidth = 100 / ($max_icons+1);	// +1 for logout
		}
		$this->tpl->set_var('tdwidth',round($tdwidth));

		// not shown in the navbar
		foreach($apps as $app => $app_data)
		{
			if ($app != 'preferences' && $app != 'about' && $app != 'logout' &&
				($app != 'home' || $GLOBALS['egw_info']['user']['preferences']['common']['start_and_logout_icons'] != 'no'))
			{
				$this->tpl->set_var($app_data);
				
				if($i < $max_icons)
				{
					$this->tpl->set_var($app_data);
					if($GLOBALS['egw_info']['user']['preferences']['common']['navbar_format'] != 'text')
					{
						$this->tpl->fp('app_icons','app_icon_block',true);
					}
					if($GLOBALS['egw_info']['user']['preferences']['common']['navbar_format'] != 'icons')
					{
						$this->tpl->fp('app_titles','app_title_block',true);
					}
				}
				else // generate extra icon layer shows icons and/or text
				{
					$this->tpl->fp('app_extra_icons','app_extra_block',true);
				}
				$i++;
			}
		}
		// settings for the extra icons dif
		if ($i < $max_icons)	// no extra icon div
		{
			$this->tpl->set_var('app_extra_icons_div','');
			$this->tpl->set_var('app_extra_icons_icon','');
		}
		else
		{
			$var['lang_close'] = lang('Close');
			$var['lang_show_more_apps'] = lang('show_more_apps');
		}
		if ($GLOBALS['egw_info']['user']['preferences']['common']['start_and_logout_icons'] != 'no' && 
			$GLOBALS['egw_info']['user']['userid'] != 'anonymous')
		{
			$this->tpl->set_var($apps['logout']);
			if($GLOBALS['egw_info']['user']['preferences']['common']['navbar_format'] != 'text')
			{
				$this->tpl->fp('app_icons','app_icon_block',true);
			}
			if($GLOBALS['egw_info']['user']['preferences']['common']['navbar_format'] != 'icons')
			{
				$this->tpl->fp('app_titles','app_title_block',true);
			}
		}

		if($GLOBALS['egw_info']['user']['preferences']['common']['navbar_format'] == 'icons')
		{
			$var['app_titles'] = '<td colspan="'.$max_icons.'">&nbsp;</td>'; 
		}
		return $var;
	}


	/**
	 * Returns the html from the closing div of the main application area to the closing html-tag
	 *
	 * @return string html or null if no footer needed/wanted
	 */
	function footer()
	{
		static $footer_done;
		if ($footer_done++) return;	// prevent multiple footers, not sure we still need this (RalfBecker)
		
		if (!isset($GLOBALS['egw_info']['flags']['nofooter']) || !$GLOBALS['egw_info']['flags']['nofooter'])
		{
			// get the (depricated) application footer
			$content = $this->_get_app_footer();
			
			// run the hook navbar_end
			// ToDo: change to return the content
			ob_start();
			$GLOBALS['egw']->hooks->process('navbar_end');
			$content .= ob_get_contents();
			ob_end_clean();

			// eg. javascript, which need to be at the end of the page
			if ($GLOBALS['egw_info']['flags']['need_footer'])
			{
				$content .= $GLOBALS['egw_info']['flags']['need_footer'];
			}

			// do the template sets footer, former parse_navbar_end function
			// this closes the application area AND renders the closing body- and html-tag
			if (!isset($GLOBALS['egw_info']['flags']['nonavbar']) || !$GLOBALS['egw_info']['flags']['nonavbar'])
			{
				$this->tpl->set_file(array('footer' => 'footer.tpl'));
				$this->tpl->set_var($this->_get_footer());
				$content .= $this->tpl->fp('out','footer');
			}
			if (DEBUG_TIMER)
			{
				$totaltime = sprintf('%4.2lf',perfgetmicrotime() - $GLOBALS['egw_info']['flags']['page_start_time']); 
		
				$content .= lang('Page was generated in %1 seconds',$totaltime);
			}
			return $content;
		}
	}
	
	/**
	 * Parses one sidebox menu and add's the html to $this->sidebox_content for later use by $this->navbar
	 *
	 * @param string $appname
	 * @param string $menu_title
	 * @param array $file
	 */
	function sidebox($appname,$menu_title,$file)
	{
		if(!$appname || ($appname==$GLOBALS['egw_info']['flags']['currentapp'] && $file))
		{
			$this->tpl->set_var('lang_title',$menu_title);
			$this->sidebox_content .= $this->tpl->fp('out','extra_blocks_header');

			foreach($file as $text => $url)
			{
				$this->sidebox_content .= $this->_sidebox_menu_item($url,$text);
			}
			$this->sidebox_content .= $this->tpl->parse('out','extra_blocks_footer');
		}
	}

	/**
	 * Return a sidebox menu item
	 *
	 * @internal PHP5 protected
	 * @param string $item_link
	 * @param string $item_text
	 * @return string
	 */
	function _sidebox_menu_item($item_link='',$item_text='')
	{
		if($item_text === '_NewLine_' || $item_link === '_NewLine_')
		{
			return $this->tpl->parse('out','extra_block_spacer');
		}
		if (strtolower($item_text) == 'grant access' && $GLOBALS['egw_info']['server']['deny_user_grants_access']) 
		{
			return;
		}
		
		$var['icon_or_star']='<img src="'.$GLOBALS['egw_info']['server']['webserver_url'] . '/phpgwapi/templates/'.$this->template.'/images'.'/orange-ball.png" width="9" height="9" alt="ball"/>';
		$var['target'] = '';
		if(is_array($item_link))
		{
			if(isset($item_link['icon']))
			{
				$app = isset($item_link['app']) ? $item_link['app'] : $GLOBALS['egw_info']['flags']['currentapp'];
				$var['icon_or_star'] = $item_link['icon'] ? '<img style="margin:0px 2px 0px 2px" src="'.$GLOBALS['egw']->common->image($app,$item_link['icon']).'"/>' : False;
			}
			$var['lang_item'] = isset($item_link['no_lang']) && $item_link['no_lang'] ? $item_link['text'] : lang($item_link['text']);
			$var['item_link'] = $item_link['link'];
			if ($item_link['target'])
			{
				$var['target'] = ' target="' . $item_link['target'] . '"';
			}
		}
		else
		{
			$var['lang_item'] = lang($item_text);
			$var['item_link'] = $item_link;
		}
		$this->tpl->set_var($var);

		$block = 'extra_block_row';
		if ($var['item_link'] === False)
		{
			$block .= $var['icon_or_star'] === False ? '_raw' : '_no_link';
		}
		return $this->tpl->parse('out',$block);
	}
}
